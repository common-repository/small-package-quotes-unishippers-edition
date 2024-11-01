<?php

/**
 *  Box sizes template 
 */
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists("Unishippers_UpdateProductInsuranceDetailOption")) {

    class Unishippers_UpdateProductInsuranceDetailOption {

        /**
         * Constructor.
         */
        public function __construct($action) {

            if ($action == 'hooks') {
                $this->en_add_simple_product_hooks();
                $this->en_add_variable_product_hooks();
            }
        }

        /**
         * Add simple product fields.
         */
        public function en_add_simple_product_hooks() {

            /* Add simple product fields */
            add_action(
                    'woocommerce_product_options_shipping', array($this, 'en_show_product_fields'), 102
            );
            add_action(
                    'woocommerce_process_product_meta', array($this, 'en_save_product_fields'), 10
            );
        }

        /**
         * Add variable product fields.
         */
        public function en_add_variable_product_hooks() {

            add_action(
                    'woocommerce_product_after_variable_attributes', array($this, 'en_show_product_fields'), 102, 3
            );
            add_action(
                    'woocommerce_save_product_variation', array($this, 'en_save_product_fields'), 10
            );
        }

        /**
         * Save the simple product fields.
         * @param int $post_id
         */
        function en_save_product_fields($post_id) {

            if (isset($post_id) && $post_id > 0) {
                $var_insurance = ( isset($_POST['_en_insurance_fee'][$post_id]) ) ? sanitize_text_field( wp_unslash($_POST['_en_insurance_fee'][$post_id])) : "";
                update_post_meta(
                        $post_id, '_en_insurance_fee', esc_attr($var_insurance)
                );
            }
        }

        /**
         * Show product fields in variation and simple product.
         * @param array $loop
         * @param object $variation_data
         * @param object $variation
         */
        function en_show_product_fields($loop, $variation_data = array(), $variation = array()) {

            if (!empty($variation) || isset($variation->ID)) {
                /* Variable products */
                $this->en_product_custom_fields($variation->ID);
            } else {
                /* Simple products */
                $post_id = get_the_ID();
                $this->en_product_custom_fields($post_id);
            }
        }

        /**
         * Add Insurance text field
         * @global $wpdb
         * @param $loop
         * @param $variation_data
         * @param $variation
         */
        function en_product_custom_fields($post_id) { 
            
            $description = "";
            $disable_insurance = "";
            
            $plan_notifi = apply_filters('en_woo_plans_notification_action' , array());

            if(!empty($plan_notifi) && (isset($plan_notifi['insurance_fee'])))
            {
                $enable_plugins = (isset($plan_notifi['insurance_fee']['enable_plugins'])) ? $plan_notifi['insurance_fee']['enable_plugins'] : "";
                $disable_plugins = (isset($plan_notifi['insurance_fee']['disable_plugins'])) ? $plan_notifi['insurance_fee']['disable_plugins'] : "";
                if(strlen($disable_plugins) > 0)
                {
                    if(strlen($enable_plugins) > 0)
                    {
                        $description =  apply_filters('en_woo_plans_notification_message_action' , $enable_plugins , $disable_plugins);
                    }
                    else
                    {
                        $description = apply_filters('unishippers_small_plans_notification_link' , array(2));
                        $disable_insurance = "disabled_me";
                    }
                }
            }
            
            $insurance = array(
                    'id'          => '_en_insurance_fee[' . $post_id . ']',
                    'label'       => __( 'Insure this item', 'eniture-unishippers-small-quotes' ),
                    'class'       =>  "$disable_insurance _en_insurance_fee",
                    'value'       => get_post_meta(
                            $post_id, '_en_insurance_fee', true
                    ),
                    'description' => $description
            );

            woocommerce_wp_checkbox($insurance);
        }

    }

    /* Initialize object */
    new Unishippers_UpdateProductInsuranceDetailOption('hooks');
}