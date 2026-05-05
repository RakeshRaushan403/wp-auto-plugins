<?php
/**
 * Plugin Name: Content Performance Heatmap
 * Plugin URI: https://example.com/content-performance-heatmap
 * Description: Generates visual heatmaps showing which sections and paragraphs of your posts get the most engagement based on scroll depth, time spent, and click tracking to help optimize content structure.
 * Version: 1.0.0
 * Author: AutoPush Bot
 * Author URI: https://example.com
 * License: GPL2
 * Text Domain: content-performance-heatmap
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

class Content_Performance_Heatmap {
    
    /**
     * Plugin version
     * @var string
     */
    private $version = '1.0.0';
    
    /**
     * Table name for storing engagement data
     * @var string
     */
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'cph_engagement';
        
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_uninstall_hook(__FILE__, array('Content_Performance_Heatmap', 'uninstall'));
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_cph_track_engagement', array($this, 'track_engagement'));
        add_action('wp_ajax_nopriv_cph_track_engagement', array($this, 'track_engagement'));
        add_action('wp_ajax_cph_get_heatmap_data', array($this, 'get_heatmap_data'));
        add_action('add_meta_boxes', array($this, 'add_heatmap_meta_box'));
        add_filter('the_content', array($this, 'wrap_content_paragraphs'), 999);
    }
    
    /**
     * Activate plugin and create database table
     */
    public function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            paragraph_index int(11) NOT NULL,
            scroll_depth int(11) DEFAULT 0,
            time_spent int(11) DEFAULT 0,
            clicks int(11) DEFAULT 0,
            session_id varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY paragraph_index (paragraph_index)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        add_option('cph_version', $this->version);
    }
    
    /**
     * Uninstall plugin and remove all data
     */
    public static function uninstall() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cph_engagement';
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        delete_option('cph_version');
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_scripts() {
        if (!is_singular('post')) {
            return;
        }
        
        wp_enqueue_script(
            'cph-tracking',
            plugin_dir_url(__FILE__) . 'js/tracking.js',
            array('jquery'),
            $this->version,
            true
        );
        
        wp_localize_script('cph-tracking', 'cphData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cph_track_nonce'),
            'postId' => get_the_ID()
        ));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        global $post;
        
        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }
        
        if (!$post || $post->post_type !== 'post') {
            return;
        }
        
        wp_enqueue_style(
            'cph-admin-style',
            plugin_dir_url(__FILE__) . 'css/admin-style.css',
            array(),
            $this->version
        );
        
        wp_enqueue_script(
            'cph-admin-script',
            plugin_dir_url(__FILE__) . 'js/admin-script.js',
            array('jquery'),
            $this->version,
            true
        );
        
        wp_localize_script('cph-admin-script', 'cphAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cph_admin_nonce'),
            'postId' => $post->ID
        ));
    }
    
    /**
     * Wrap content paragraphs with tracking divs
     * 
     * @param string $content Post content
     * @return string Modified content
     */
    public function wrap_content_paragraphs($content) {
        if (!is_singular('post') || !in_the_loop() || !is_main_query()) {
            return $content;
        }
        
        $paragraphs = preg_split('/(<\/?(?:p|h[1-6]|blockquote)[^>]*>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        $wrapped_content = '';
        $paragraph_index = 0;
        $in_tag = false;
        
        foreach ($paragraphs as $piece) {
            if (preg_match('/<(p|h[1-6]|blockquote)[^>]*>/i', $piece)) {
                $wrapped_content .= '<div class="cph-paragraph" data-paragraph="' . esc_attr($paragraph_index) . '">';
                $wrapped_content .= $piece;
                $in_tag = true;
            } elseif (preg_match('/<\/(p|h[1-6]|blockquote)>/i', $piece)) {
                $wrapped_content .= $piece;
                $wrapped_content .= '</div>';
                $in_tag = false;
                $paragraph_index++;
            } else {
                $wrapped_content .= $piece;
            }
        }
        
        return $wrapped_content;
    }
    
    /**
     * Track engagement data via AJAX
     */
    public function track_engagement() {
        check_ajax_referer('cph_track_nonce', 'nonce');
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $paragraph_index = isset($_POST['paragraph_index']) ? absint($_POST['paragraph_index']) : 0;
        $scroll_depth = isset($_POST['scroll_depth']) ? absint($_POST['scroll_depth']) : 0;
        $time_spent = isset($_POST['time_spent']) ? absint($_POST['time_spent']) : 0;
        $clicks = isset($_POST['clicks']) ? absint($_POST['clicks']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        if (!isset($_COOKIE['cph_session_id'])) {
            $session_id = wp_generate_password(32, false);
            setcookie('cph_session_id', $session_id, time() + (86400 * 30), COOKIEPATH, COOKIE_DOMAIN);
        } else {
            $session_id = sanitize_text_field($_COOKIE['cph_session_id']);
        }
        
        global $wpdb;
        
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE post_id = %d AND paragraph_index = %d AND session_id = %s",
            $post_id,
            $paragraph_index,
            $session_id
        ));
        
        if ($existing) {
            $wpdb->update(
                $this->table_name,
                array(
                    'scroll_depth' => $scroll_depth,
                    'time_spent' => $time_spent,
                    'clicks' => $clicks
                ),
                array('id' => $existing->id),
                array('%d', '%d', '%d'),
                array('%d')
            );
        } else {
            $wpdb->insert(
                $this->table_name,
                array(
                    'post_id' => $post_id,
                    'paragraph_index' => $paragraph_index,
                    'scroll_depth' => $scroll_depth,
                    'time_spent' => $time_spent,
                    'clicks' => $clicks,
                    'session_id' => $session_id
                ),
                array('%d', '%d', '%d', '%d', '%d', '%s')
            );
        }
        
        wp_send_json_success('Data tracked');
    }
    
    /**
     * Get heatmap data for a post
     */
    public function get_heatmap_data() {
        check_ajax_referer('cph_admin_nonce', 'nonce');
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                paragraph_index,
                AVG(scroll_depth) as avg_scroll,
                AVG(time_spent) as avg_time,
                SUM(clicks) as total_clicks,
                COUNT(DISTINCT session_id) as unique_views
            FROM {$this->table_name}
            WHERE post_id = %d
            GROUP BY paragraph_index
            ORDER BY paragraph_index ASC",
            $post_id
        ));
        
        $heatmap_data = array();
        $max_score = 0;
        
        foreach ($results as $row) {
            $engagement_score = ($row->avg_scroll * 0.3) + ($row->avg_time * 0.4) + ($row->total_clicks * 0.3);
            if ($engagement_score > $max_score) {
                $max_score = $engagement_score;
            }
            
            $heatmap_data[] = array(
                'index' => $row->paragraph_index,
                'score' => $engagement_score,
                'views' => $row->unique_views,
                'time' => round($row->avg_time, 2),
                'clicks' => $row->total_clicks
            );
        }
        
        foreach ($heatmap_data as &$data) {
            $data['intensity'] = $max_score > 0 ? ($data['score'] / $max_score) * 100 : 0;
        }
        
        wp_send_json_success($heatmap_data);
    }
    
    /**
     * Add heatmap meta box to post editor
     */
    public function add_heatmap_meta_box() {
        add_meta_box(
            'cph_heatmap_meta_box',
            'Content Performance Heatmap',
            array($this, 'render_heatmap_meta_box'),
            'post',
            'normal',
            'high'
        );
    }
    
    /**
     * Render heatmap meta box content
     * 
     * @param WP_Post $post Current post object
     */
    public function render_heatmap_meta_box($post) {
        global $wpdb;
        
        $total_sessions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT session_id) FROM {$this->table_name} WHERE post_id = %d",
            $post->ID
        ));
        
        echo '<div id="cph-heatmap-container">';
        echo '<p><strong>Total Unique Views:</strong> ' . esc_html($total_sessions) . '</p>';
        echo '<div id="cph-heatmap-visualization"></div>';
        echo '<button type="button" id="cph-load-heatmap" class="button button-primary">Load Heatmap</button>';
        echo '</div>';
    }
}

new Content_Performance_Heatmap();