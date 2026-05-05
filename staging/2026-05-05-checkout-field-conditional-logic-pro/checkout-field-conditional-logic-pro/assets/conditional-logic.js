/**
 * Checkout Field Conditional Logic Pro - Frontend Script
 * Version: 1.0.0
 */

(function($) {
    'use strict';
    
    var cfclp = {
        rules: {},
        cartData: {},
        
        /**
         * Initialize conditional logic
         */
        init: function() {
            if (typeof cfclpData === 'undefined') {
                return;
            }
            
            this.rules = cfclpData.rules || {};
            this.cartData = cfclpData.cartData || {};
            
            if (!this.rules.field_to_control || !this.rules.condition_type) {
                return;
            }
            
            this.bindEvents();
            this.applyRules();
        },
        
        /**
         * Bind events to checkout elements
         */
        bindEvents: function() {
            var self = this;
            
            // Listen for shipping method changes
            $(document.body).on('updated_checkout', function() {
                self.applyRules();
            });
            
            // Listen for payment method changes
            $('form.checkout').on('change', 'input[name="payment_method"]', function() {
                self.applyRules();
            });
            
            // Listen for shipping method changes
            $(document.body).on('change', 'input[name^="shipping_method"]', function() {
                $(document.body).trigger('update_checkout');
            });
        },
        
        /**
         * Apply conditional logic rules
         */
        applyRules: function() {
            var self = this;
            var fieldWrapper = $('#' + this.rules.field_to_control + '_field');
            
            if (fieldWrapper.length === 0) {
                return;
            }
            
            var shouldShow = this.evaluateCondition();
            
            if (this.rules.action_type === 'show') {
                if (shouldShow) {
                    this.showField(fieldWrapper);
                } else {
                    this.hideField(fieldWrapper);
                }
            } else {
                if (shouldShow) {
                    this.hideField(fieldWrapper);
                } else {
                    this.showField(fieldWrapper);
                }
            }
        },
        
        /**
         * Evaluate condition based on rule type
         *
         * @return {boolean}
         */
        evaluateCondition: function() {
            switch (this.rules.condition_type) {
                case 'shipping_method':
                    return this.checkShippingMethod();
                    
                case 'payment_gateway':
                    return this.checkPaymentGateway();
                    
                case 'cart_total':
                    return this.checkCartTotal();
                    
                case 'product_category':
                    return this.checkProductCategory();
                    
                default:
                    return false;
            }
        },
        
        /**
         * Check if shipping method matches
         *
         * @return {boolean}
         */
        checkShippingMethod: function() {
            var selectedMethod = $('input[name^="shipping_method"]:checked').val();
            
            if (!selectedMethod) {
                return false;
            }
            
            // Extract method ID (e.g., "flat_rate:1" -> "flat_rate")
            var methodId = selectedMethod.split(':')[0];
            
            return methodId === this.rules.condition_value || selectedMethod === this.rules.condition_value;
        },
        
        /**
         * Check if payment gateway matches
         *
         * @return {boolean}
         */
        checkPaymentGateway: function() {
            var selectedGateway = $('input[name="payment_method"]:checked').val();
            return selectedGateway === this.rules.condition_value;
        },
        
        /**
         * Check if cart total meets condition
         *
         * @return {boolean}
         */
        checkCartTotal: function() {
            var cartTotal = parseFloat(this.cartData.total) || 0;
            var conditionValue = parseFloat(this.rules.condition_value) || 0;
            var operator = this.rules.operator || 'equals';
            
            switch (operator) {
                case 'greater_than':
                    return cartTotal > conditionValue;
                    
                case 'less_than':
                    return cartTotal < conditionValue;
                    
                case 'equals':
                    return cartTotal === conditionValue;
                    
                default:
                    return false;
            }
        },
        
        /**
         * Check if product category matches
         *
         * @return {boolean}
         */
        checkProductCategory: function() {
            var categories = this.cartData.categories || [];
            return categories.indexOf(this.rules.condition_value) !== -1;
        },
        
        /**
         * Show field with animation
         *
         * @param {jQuery} fieldWrapper
         */
        showField: function(fieldWrapper) {
            fieldWrapper.slideDown(300).removeClass('cfclp-hidden');
            fieldWrapper.find('input, select, textarea').prop('disabled', false);
        },
        
        /**
         * Hide field with animation
         *
         * @param {jQuery} fieldWrapper
         */
        hideField: function(fieldWrapper) {
            fieldWrapper.slideUp(300).addClass('cfclp-hidden');
            fieldWrapper.find('input, select, textarea').prop('disabled', true).val('');
        }
    };
    
    // Initialize on document ready and after checkout update
    $(document).ready(function() {
        cfclp.init();
    });
    
    $(document.body).on('updated_checkout', function() {
        cfclp.init();
    });
    
})(jQuery);