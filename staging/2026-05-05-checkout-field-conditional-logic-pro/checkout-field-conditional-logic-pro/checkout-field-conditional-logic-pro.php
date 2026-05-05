<?php
/**
 * Plugin Name: Checkout Field Conditional Logic Pro
 * Plugin URI: https://rakesh-raushan.my-style.in/
 * Description: Dynamically shows or hides WooCommerce checkout fields based on real-time customer selections like shipping method, product categories, payment gateway, or cart total, reducing friction and form abandonment.
 * Version: 1.0.0
 * Author: AutoPush Bot
 * Author URI: https://rakesh-raushan.my-style.in/
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: checkout-field-conditional-logic-pro
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 8.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Main plugin class
 */
class Checkout_Field_Conditional_Logic_Pro {
    
    /**
     * Plugin version
     *
     * @var string
     */
    const VERSION = '1.0.0';
    
    /**
     * Option name for storing rules
     *
     * @var string
     */
    const OPTION_NAME = 'cfclp_conditional_rules';
    
    /**
     * Instance of this class
     *
     * @var object
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     *
     * @return Checkout_Field_Conditional_Logic_Pro
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
        register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
    }
    
    /**
     * Initialize plugin
     *
     * @return void
     */
    public function init() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
            return;
        }
        
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_post_cfclp_save_rules', array( $this, 'save_rules' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
        add_action( 'woocommerce_after_checkout_form', array( $this, 'output_conditional_logic_script' ) );
        
        // Declare HPOS compatibility
        add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
    }
    
    /**
     * Declare HPOS compatibility
     *
     * @return void
     */
    public function declare_hpos_compatibility() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                __FILE__,
                true
            );
        }
    }
    
    /**
     * Display WooCommerce missing notice
     *
     * @return void
     */
    public function woocommerce_missing_notice() {
        echo '<div class="error"><p><strong>' . esc_html__( 'Checkout Field Conditional Logic Pro', 'checkout-field-conditional-logic-pro' ) . '</strong> ' . esc_html__( 'requires WooCommerce to be installed and active.', 'checkout-field-conditional-logic-pro' ) . '</p></div>';
    }
    
    /**
     * Add admin menu
     *
     * @return void
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __( 'Checkout Field Logic', 'checkout-field-conditional-logic-pro' ),
            __( 'Checkout Field Logic', 'checkout-field-conditional-logic-pro' ),
            'manage_woocommerce',
            'checkout-field-logic',
            array( $this, 'render_admin_page' )
        );
    }
    
    /**
     * Register settings
     *
     * @return void
     */
    public function register_settings() {
        register_setting( 'cfclp_settings', self::OPTION_NAME );
    }
    
    /**
     * Render admin page
     *
     * @return void
     */
    public function render_admin_page() {
        $rules = get_option( self::OPTION_NAME, array() );
        $nonce = wp_create_nonce( 'cfclp_save_rules_nonce' );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__( 'Checkout Field Conditional Logic', 'checkout-field-conditional-logic-pro' ); ?></h1>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="cfclp_save_rules">
                <?php wp_nonce_field( 'cfclp_save_rules_nonce', 'cfclp_nonce' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="field_to_control"><?php echo esc_html__( 'Field to Control', 'checkout-field-conditional-logic-pro' ); ?></label>
                        </th>
                        <td>
                            <input type="text" name="cfclp_rules[field_to_control]" id="field_to_control" 
                                   value="<?php echo isset( $rules['field_to_control'] ) ? esc_attr( $rules['field_to_control'] ) : ''; ?>" 
                                   class="regular-text" placeholder="billing_company">
                            <p class="description"><?php echo esc_html__( 'Enter the field ID (e.g., billing_company, shipping_address_2)', 'checkout-field-conditional-logic-pro' ); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="condition_type"><?php echo esc_html__( 'Condition Type', 'checkout-field-conditional-logic-pro' ); ?></label>
                        </th>
                        <td>
                            <select name="cfclp_rules[condition_type]" id="condition_type">
                                <option value="shipping_method" <?php selected( isset( $rules['condition_type'] ) ? $rules['condition_type'] : '', 'shipping_method' ); ?>>
                                    <?php echo esc_html__( 'Shipping Method', 'checkout-field-conditional-logic-pro' ); ?>
                                </option>
                                <option value="payment_gateway" <?php selected( isset( $rules['condition_type'] ) ? $rules['condition_type'] : '', 'payment_gateway' ); ?>>
                                    <?php echo esc_html__( 'Payment Gateway', 'checkout-field-conditional-logic-pro' ); ?>
                                </option>
                                <option value="cart_total" <?php selected( isset( $rules['condition_type'] ) ? $rules['condition_type'] : '', 'cart_total' ); ?>>
                                    <?php echo esc_html__( 'Cart Total', 'checkout-field-conditional-logic-pro' ); ?>
                                </option>
                                <option value="product_category" <?php selected( isset( $rules['condition_type'] ) ? $rules['condition_type'] : '', 'product_category' ); ?>>
                                    <?php echo esc_html__( 'Product Category', 'checkout-field-conditional-logic-pro' ); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="condition_value"><?php echo esc_html__( 'Condition Value', 'checkout-field-conditional-logic-pro' ); ?></label>
                        </th>
                        <td>
                            <input type="text" name="cfclp_rules[condition_value]" id="condition_value" 
                                   value="<?php echo isset( $rules['condition_value'] ) ? esc_attr( $rules['condition_value'] ) : ''; ?>" 
                                   class="regular-text" placeholder="flat_rate">
                            <p class="description"><?php echo esc_html__( 'Enter the value to match (e.g., flat_rate, bacs, 100, category-slug)', 'checkout-field-conditional-logic-pro' ); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="action_type"><?php echo esc_html__( 'Action', 'checkout-field-conditional-logic-pro' ); ?></label>
                        </th>
                        <td>
                            <select name="cfclp_rules[action_type]" id="action_type">
                                <option value="show" <?php selected( isset( $rules['action_type'] ) ? $rules['action_type'] : '', 'show' ); ?>>
                                    <?php echo esc_html__( 'Show Field', 'checkout-field-conditional-logic-pro' ); ?>
                                </option>
                                <option value="hide" <?php selected( isset( $rules['action_type'] ) ? $rules['action_type'] : '', 'hide' ); ?>>
                                    <?php echo esc_html__( 'Hide Field', 'checkout-field-conditional-logic-pro' ); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="operator"><?php echo esc_html__( 'Operator (for cart total)', 'checkout-field-conditional-logic-pro' ); ?></label>
                        </th>
                        <td>
                            <select name="cfclp_rules[operator]" id="operator">
                                <option value="greater_than" <?php selected( isset( $rules['operator'] ) ? $rules['operator'] : '', 'greater_than' ); ?>>
                                    <?php echo esc_html__( 'Greater Than', 'checkout-field-conditional-logic-pro' ); ?>
                                </option>
                                <option value="less_than" <?php selected( isset( $rules['operator'] ) ? $rules['operator'] : '', 'less_than' ); ?>>
                                    <?php echo esc_html__( 'Less Than', 'checkout-field-conditional-logic-pro' ); ?>
                                </option>
                                <option value="equals" <?php selected( isset( $rules['operator'] ) ? $rules['operator'] : '', 'equals' ); ?>>
                                    <?php echo esc_html__( 'Equals', 'checkout-field-conditional-logic-pro' ); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button( __( 'Save Rules', 'checkout-field-conditional-logic-pro' ) ); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Save rules from admin form
     *
     * @return void
     */
    public function save_rules() {
        if ( ! isset( $_POST['cfclp_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cfclp_nonce'] ) ), 'cfclp_save_rules_nonce' ) ) {
            wp_die( esc_html__( 'Security check failed', 'checkout-field-conditional-logic-pro' ) );
        }
        
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'Unauthorized access', 'checkout-field-conditional-logic-pro' ) );
        }
        
        $rules = array();
        if ( isset( $_POST['cfclp_rules'] ) && is_array( $_POST['cfclp_rules'] ) ) {
            $rules = array(
                'field_to_control' => isset( $_POST['cfclp_rules']['field_to_control'] ) ? sanitize_text_field( wp_unslash( $_POST['cfclp_rules']['field_to_control'] ) ) : '',
                'condition_type' => isset( $_POST['cfclp_rules']['condition_type'] ) ? sanitize_text_field( wp_unslash( $_POST['cfclp_rules']['condition_type'] ) ) : '',
                'condition_value' => isset( $_POST['cfclp_rules']['condition_value'] ) ? sanitize_text_field( wp_unslash( $_POST['cfclp_rules']['condition_value'] ) ) : '',
                'action_type' => isset( $_POST['cfclp_rules']['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['cfclp_rules']['action_type'] ) ) : '',
                'operator' => isset( $_POST['cfclp_rules']['operator'] ) ? sanitize_text_field( wp_unslash( $_POST['cfclp_rules']['operator'] ) ) : 'equals',
            );
        }
        
        update_option( self::OPTION_NAME, $rules );
        
        wp_safe_redirect( add_query_arg( array(
            'page' => 'checkout-field-logic',
            'updated' => 'true'
        ), admin_url( 'admin.php' ) ) );
        exit;
    }
    
    /**
     * Enqueue frontend scripts
     *
     * @return void
     */
    public function enqueue_frontend_scripts() {
        if ( ! is_checkout() ) {
            return;
        }
        
        wp_enqueue_script(
            'cfclp-conditional-logic',
            plugin_dir_url( __FILE__ ) . 'assets/conditional-logic.js',
            array( 'jquery', 'wc-checkout' ),
            self::VERSION,
            true
        );
        
        $rules = get_option( self::OPTION_NAME, array() );
        $cart_data = $this->get_cart_data();
        
        wp_localize_script( 'cfclp-conditional-logic', 'cfclpData', array(
            'rules' => $rules,
            'cartData' => $cart_data,
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        ) );
    }
    
    /**
     * Get cart data for conditional logic
     *
     * @return array
     */
    private function get_cart_data() {
        $data = array(
            'total' => 0,
            'categories' => array(),
        );
        
        if ( ! WC()->cart ) {
            return $data;
        }
        
        $data['total'] = WC()->cart->get_total( 'edit' );
        
        foreach ( WC()->cart->get_cart() as $cart_item ) {
            $product = $cart_item['data'];
            if ( $product ) {
                $terms = get_the_terms( $product->get_id(), 'product_cat' );
                if ( $terms && ! is_wp_error( $terms ) ) {
                    foreach ( $terms as $term ) {
                        $data['categories'][] = $term->slug;
                    }
                }
            }
        }
        
        $data['categories'] = array_unique( $data['categories'] );
        
        return $data;
    }
    
    /**
     * Output conditional logic script inline
     *
     * @return void
     */
    public function output_conditional_logic_script() {
        ?>
        <script type="text/javascript">
        /* Inline script for immediate execution */
        </script>
        <?php
    }
    
    /**
     * Uninstall plugin - clean up options
     *
     * @return void
     */
    public static function uninstall() {
        delete_option( self::OPTION_NAME );
    }
}

// Initialize plugin
Checkout_Field_Conditional_Logic_Pro::get_instance();
