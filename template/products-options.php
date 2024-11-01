<?php

/**
 *  Box sizes template 
 */
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists("Unishippers_UpdateProductDetailOption")) {

    class Unishippers_UpdateProductDetailOption {

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
                    'woocommerce_product_options_shipping', array($this, 'en_show_product_fields'), 100
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
                    'woocommerce_product_after_variable_attributes', array($this, 'en_show_product_fields'), 100, 3
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
                $var_hazardous = ( isset($_POST['_hazardousmaterials'][$post_id]) ) ? sanitize_text_field( wp_unslash($_POST['_hazardousmaterials'][$post_id])) : "";

                update_post_meta(
                        $post_id, '_hazardousmaterials', esc_attr($var_hazardous)
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
         * Add vertival rotation checkbox.
         * @global $wpdb
         * @param $loop
         * @param $variation_data
         * @param $variation
         */
        function en_product_custom_fields($post_id) {
            
            $plan_notifi = apply_filters('en_woo_plans_notification_action' , array());

            $features = [
                'hazardous_material' => ['class' => '_hazardous_material', 
                                            'id' => '_hazardousmaterials', 
                                            'allowed_plan' => 2,
                                            'label' => 'Hazardous material'],
                'transit_days'       => ['class' => '_en_exempt_ground_transit_restriction', 
                                            'id' => '_en_exempt_ground_transit_restriction', 
                                            'allowed_plan' => 3,
                                            'label' => 'Exempt from Ground Transit Time Restriction']
            ];

            foreach ($features as $key => $feature) {
                $transit_restriction_enable_carrier_arr = apply_filters('en_check_ground_transit_restrict_status', []);
                if($key == 'transit_days' && count($transit_restriction_enable_carrier_arr) == 0){
                    continue;
                }
                $description = "";
                $disable_hazardous = "";
                if(!empty($plan_notifi) && (isset($plan_notifi[$key]))){
                    $enable_plugins = (isset($plan_notifi[$key]['enable_plugins'])) ? $plan_notifi['hazardous_material']['enable_plugins'] : "";
                    $disable_plugins = (isset($plan_notifi[$key]['disable_plugins'])) ? $plan_notifi['hazardous_material']['disable_plugins'] : "";
                    if(strlen($disable_plugins) > 0)
                    {
                        if(strlen($enable_plugins) > 0)
                        {
                            $description =  apply_filters('en_woo_plans_notification_message_action' , $enable_plugins , $disable_plugins);
                        }
                        else
                        {
                            $description = apply_filters('unishippers_small_plans_notification_link' , [$feature['allowed_plan']]);
                            $disable_hazardous = "disabled_me";
                        }
                    }
                }

                $field_array = array(
                    'id' => $feature['id']. '[' . $post_id . ']',
                    'label' => $feature['label'],
                    'class' =>  $disable_hazardous. ' '. $feature['id'],
                    'value' => get_post_meta(
                            $post_id, $feature['id'], true
                    ),

                    'description' => $description
                );
                woocommerce_wp_checkbox($field_array);
            }
        }

    }

    /* Initialize object */
    new Unishippers_UpdateProductDetailOption('hooks');
}
