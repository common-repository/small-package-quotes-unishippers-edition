<?php

/**
 * Includes Shipping Rules Ajax Request class
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("EnUnishippersSmallShippingRulesAjaxReq")) {

    class EnUnishippersSmallShippingRulesAjaxReq
    {
        /**
         * Get shipping rules ajax request
         */
        public function __construct()
        {
            add_action('wp_ajax_nopriv_en_unishippers_small_save_shipping_rule', array($this, 'save_shipping_rule_ajax'));
            add_action('wp_ajax_en_unishippers_small_save_shipping_rule', array($this, 'save_shipping_rule_ajax'));

            add_action('wp_ajax_nopriv_en_unishippers_small_edit_shipping_rule', array($this, 'edit_shipping_rule_ajax'));
            add_action('wp_ajax_en_unishippers_small_edit_shipping_rule', array($this, 'edit_shipping_rule_ajax'));

            add_action('wp_ajax_nopriv_en_unishippers_small_delete_shipping_rule', array($this, 'delete_shipping_rule_ajax'));
            add_action('wp_ajax_en_unishippers_small_delete_shipping_rule', array($this, 'delete_shipping_rule_ajax'));

            add_action('wp_ajax_nopriv_en_unishippers_small_update_shipping_rule_status', array($this, 'update_shipping_rule_status_ajax'));
            add_action('wp_ajax_en_unishippers_small_update_shipping_rule_status', array($this, 'update_shipping_rule_status_ajax'));
        }

        /**
         * Save Shipping Rule Function
         * @global $wpdb
         */
        function save_shipping_rule_ajax()
        {
            global $wpdb;

            $insert_qry = $update_qry = '';
            $error = false;
            $data = $_POST;
            $get_shipping_rule_id = (isset($data['rule_id']) && intval($data['rule_id'])) ? $data['rule_id'] : "";
            $last_id = $get_shipping_rule_id;
            $qry = "SELECT * FROM " . $wpdb->prefix . "eniture_unishippers_small_shipping_rules WHERE name = '" . $data['name'] . "'"; 
            $get_shipping_rule = $wpdb->get_results($qry);
            unset($data['action']);
            unset($data['rule_id']);
            
            if (!empty($get_shipping_rule_id)) {
                $data['settings'] = json_encode($data['settings']);
                $update_qry = $wpdb->update(
                    $wpdb->prefix . 'eniture_unishippers_small_shipping_rules', $data, array('id' => $get_shipping_rule_id)
                );

                $update_qry = (!empty($get_shipping_rule) && reset($get_shipping_rule)->id == $get_shipping_rule_id) ? 1 : $update_qry;
            } else {
                if (!empty($get_shipping_rule)) {
                    $error = true;
                } else {
                    $data['settings'] = json_encode($data['settings']);
                    $insert_qry = $wpdb->insert($wpdb->prefix . 'eniture_unishippers_small_shipping_rules', $data);
                    $last_id = $wpdb->insert_id;
                }
            }

            $shipping_rules_list = array('name' => $data["name"], 'type' => $data["type"], 'is_active' => $data["is_active"], 'insert_qry' => $insert_qry, 'update_qry' => $update_qry, 'id' => $last_id, 'error' => $error);

            echo json_encode($shipping_rules_list);
            exit;
        }

        /**
         * Edit Shipping Rule Function
         * @global $wpdb
         */
        function edit_shipping_rule_ajax()
        {
            global $wpdb;
            $get_shipping_rule_id = (isset($_POST['edit_id']) && intval($_POST['edit_id'])) ? $_POST['edit_id'] : "";
            $shipping_rules_list = $wpdb->get_results(
                "SELECT * FROM " . $wpdb->prefix . "eniture_unishippers_small_shipping_rules WHERE id=$get_shipping_rule_id"
            );
            $product_tags_markup = $this->get_product_tags_markup($shipping_rules_list);
            $data = ['rule_data' => reset($shipping_rules_list), 'product_tags_markup' => $product_tags_markup];

            echo json_encode($data);
            exit;
        }

        /**
         * Delete Shipping Rule Function
         * @global $wpdb
         */
        function delete_shipping_rule_ajax()
        {
            global $wpdb;
            $get_shipping_rule_id = (isset($_POST['delete_id']) && intval($_POST['delete_id'])) ? $_POST['delete_id'] : "";
            $qry = $wpdb->delete($wpdb->prefix . 'eniture_unishippers_small_shipping_rules', array('id' => $get_shipping_rule_id));

            echo json_encode(['query' => $qry]);
            exit;
        }

        /**
         * Update Shipping Rule Status Function
         * @global $wpdb
         */
        function update_shipping_rule_status_ajax()
        {
            global $wpdb;
            $get_shipping_rule_id = (isset($_POST['rule_id']) && intval($_POST['rule_id'])) ? $_POST['rule_id'] : "";
            $is_active = isset($_POST['is_active']) ? $_POST['is_active'] : "";
            $data = ['is_active' => $is_active];
            
            $update_qry = $wpdb->update(
                $wpdb->prefix . 'eniture_unishippers_small_shipping_rules', $data, array('id' => $get_shipping_rule_id)
            );

            echo json_encode(['id' => $get_shipping_rule_id, 'is_active' => $is_active, 'update_qry' => $update_qry]);
            exit;
        }

        function get_product_tags_markup($shipping_rules_list)
        {
            $tags_options = '';
            $shipping_rules_list = reset($shipping_rules_list);
            $tags_data = isset($shipping_rules_list->settings) ? json_decode($shipping_rules_list->settings, true) : [];
            $selected_tags_detials = $this->get_selected_tags_details($tags_data['filter_by_product_tag_value']);

            if (!empty($selected_tags_detials) && is_array($selected_tags_detials)) {
                foreach ($selected_tags_detials as $key => $tag) {
                    $tags_options .= "<option selected='selected' value='" . esc_attr($tag['term_taxonomy_id']) . "'>" . esc_html($tag['name']) . "</option>";
                }
            }

            if (empty($tags_data['filter_by_product_tag_value']) || !is_array($tags_data['filter_by_product_tag_value'])) {
                $tags_data['filter_by_product_tag_value'] = [];
            }

            $en_woo_product_tags = get_tags( array( 'taxonomy' => 'product_tag' ) );
            if (!empty($en_woo_product_tags) && is_array($tags_data['filter_by_product_tag_value'])) {
                foreach ($en_woo_product_tags as $key => $tag) {
                    if (!in_array($tag->term_id, $tags_data['filter_by_product_tag_value'])) {
                        $tags_options .= "<option value='" . esc_attr($tag->term_taxonomy_id) . "'>" . esc_html($tag->name) . "</option>";
                    }
                }
            }

            return $tags_options;
        }

        function get_selected_tags_details($products_tags_arr)
        {
            if (empty($products_tags_arr) || !is_array($products_tags_arr)) {
                return [];
            }

            $tags_detail = [];
            $count = 0;
            $en_woo_product_tags = get_tags( array( 'taxonomy' => 'product_tag' ) );

            if (isset($en_woo_product_tags) && !empty($en_woo_product_tags)) {
                foreach ($en_woo_product_tags as $key => $tag) {
                    if (in_array($tag->term_taxonomy_id, $products_tags_arr)) {
                        $tags_detail[$count]['term_id'] = $tag->term_id;
                        $tags_detail[$count]['name'] = $tag->name;
                        $tags_detail[$count]['slug'] = $tag->slug;
                        $tags_detail[$count]['term_taxonomy_id'] = $tag->term_taxonomy_id;
                        $tags_detail[$count]['description'] = $tag->description;
                        $count++;
                    }
                }
            }

            return $tags_detail;
        }

        function apply_shipping_rules($unishippers_small_package, $apply_on_rates = false, $rates = [])
        {
            if (empty($unishippers_small_package)) return $apply_on_rates ? $rates : false;

            global $wpdb;
            $qry = "SELECT * FROM " . $wpdb->prefix . "eniture_unishippers_small_shipping_rules"; 
            $rules = $wpdb->get_results($qry, ARRAY_A);

            if (empty($rules)) return $apply_on_rates ? $rates : false;
        
            $is_rule_applied = false;
            foreach ($rules as $rule) {
                if (!$rule['is_active']) continue;

                $settings = isset($rule['settings']) ? json_decode($rule['settings'], true) : [];
                if (empty($settings)) continue;

                $rule_type = isset($rule['type']) ? $rule['type'] : '';

                if ($rule_type == 'Hide Methods' && !$apply_on_rates) {
                    $is_rule_applied = $this->apply_rule($settings, $unishippers_small_package);
                    if ($is_rule_applied) break;
                } else if ($rule_type == 'Override Rates' && $apply_on_rates) {
                    $rates = $this->apply_override_rates_rule($unishippers_small_package, $settings, $rates);
                }
            }

            return $apply_on_rates ? $rates : $is_rule_applied;
        }

        function apply_rule($settings, $unishippers_small_package)
        {
            $is_rule_applied = false;

            if ($settings['apply_to'] == 'cart') {
                $formatted_values = $this->get_formatted_values($unishippers_small_package);
                $is_rule_applied = $this->apply_rule_filters($settings, $formatted_values);
            } else {
                foreach ($unishippers_small_package as $key => $pkg) {
                    $is_rule_applied = false;
                    $shipments = [];
                    $shipments[$key] = $pkg;

                    $formatted_values = $this->get_formatted_values($shipments);
                    $is_rule_applied = $this->apply_rule_filters($settings, $formatted_values);

                    if ($is_rule_applied) break;
                }
            }

            return $is_rule_applied;
        }

        function get_formatted_values($shipments)
        {
            $formatted_values = ['weight' => 0, 'price' => 0, 'quantity' => 0, 'tags' => []];

            foreach ($shipments as $pkg) {
                if (empty($pkg['origin']) || empty($pkg['items'])) continue;
                
                $formatted_values['weight'] += floatval($pkg['shipment_weight']);
                $formatted_values['price'] += floatval($pkg['product_prices']);
                $formatted_values['quantity'] += floatval($pkg['product_quantities']);
                $formatted_values['tags'] = array_merge($formatted_values['tags'], $pkg['product_tags']);
            }

            return $formatted_values;
        }

        function apply_rule_filters($settings, $formatted_values)
        {
            $is_filter_applied = false;
            $filters = ['weight', 'price', 'quantity'];

            // Check if any of the filter is checked
            $filters_checks = ['filter_by_weight', 'filter_by_price', 'filter_by_quantity', 'filter_by_product_tag'];
            $any_filter_checked = false;
            foreach ($filters_checks as $check) {
                if (isset($settings[$check]) && filter_var($settings[$check], FILTER_VALIDATE_BOOLEAN)) {
                    $any_filter_checked = true;
                    break;
                }
            }

            // If there is no filter check, then all rules will meet so rule will be treated as applied
            if (!$any_filter_checked) {
                return true;
            }

            foreach ($filters as $filter) {
                if (filter_var($settings['filter_by_' . $filter], FILTER_VALIDATE_BOOLEAN)) {
                    $is_filter_applied = $formatted_values[$filter] >= $settings['filter_by_' . $filter . '_from'];
                    if ($is_filter_applied && !empty($settings['filter_by_' . $filter . '_to'])) {
                        $is_filter_applied = $formatted_values[$filter] < $settings['filter_by_' . $filter . '_to'];
                    }
                }

                if ($is_filter_applied) break;
            }

            if (filter_var($settings['filter_by_product_tag'], FILTER_VALIDATE_BOOLEAN) && !$is_filter_applied) {
                $product_tags = $settings['filter_by_product_tag_value'];
                $tags_check = array_filter($product_tags, function ($tag) use ($formatted_values) {
                    return in_array($tag, $formatted_values['tags']);
                });
                $is_filter_applied = count($tags_check) > 0;
            }

            return $is_filter_applied;
        }

        function apply_override_rates_rule($unishippers_small_package, $settings, $rates)
        {
            $updated_rates = $rates;

            foreach ($unishippers_small_package as $key => $pkg) {
                $is_rule_applied = false;
                $shipments = [];
                $shipments[$key] = $pkg;

                $formatted_values = $this->get_formatted_values($shipments);
                $is_rule_applied = $this->apply_rule_filters($settings, $formatted_values);

                if ($is_rule_applied) {
                    $updated_rates = $this->get_updated_rates($updated_rates, $settings);
                };
            }

            return $updated_rates;
        }

        function get_updated_rates($rates, $settings)
        {
            $new_api_enabled = get_option('api_endpoint_unishippers_small') == 'unishippers_small_new_api';

            foreach ($rates as $key => $result) {
                $unishippers_quotes = (isset($result->q) && !empty($result->q)) ? $result->q : [];
                if (empty($unishippers_quotes)) continue;

                foreach ($unishippers_quotes as $quote_key => $quote) {
                    if ($new_api_enabled) {
                        $smallpkg_get_quotes = new Unishipper_Get_Shipping_Quotes;
                        $quote = $smallpkg_get_quotes->formatQuoteDetails($quote);
                    }

                    if (!isset($quote->serviceType)) continue;

                    $service_code = isset($settings['service']) ? $settings['service'] : '';
                    if ($quote->serviceType == $service_code && (isset($quote->totalNetCharge->Amount) || isset($quote->totalOfferPrice->value))) {
                        // Legacy API
                        !$new_api_enabled && isset($quote->totalNetCharge->Amount) && $unishippers_quotes->$quote_key->totalNetCharge->Amount = $settings['service_rate'];
                        // New API
                        $new_api_enabled && isset($quote->totalOfferPrice->value) && $unishippers_quotes[$quote_key]->totalOfferPrice->value = $settings['service_rate'];
                    }
                }

                $rates[$key]->q = $unishippers_quotes;
            }

            return $rates;
        }

        /**
         * Get the large cart settings array.
         * @return array Returns the max items and max weight per package in an array format or empty array if not found.
         */
        function get_large_cart_settings()
        {
            global $wpdb;
            $qry = "SELECT * FROM " . $wpdb->prefix . "eniture_unishippers_small_shipping_rules"; 
            $rules = $wpdb->get_results($qry, ARRAY_A);
            if (empty($rules)) return [];

            $response = [];
            foreach ($rules as $rule) {
                if (!$rule['is_active']) continue;

                $settings = isset($rule['settings']) ? json_decode($rule['settings'], true) : [];
                if (empty($settings)) continue;

                $rule_type = isset($rule['type']) ? $rule['type'] : '';
                if ($rule_type == 'Large Cart Settings' && !empty($settings['max_items']) && !empty($settings['max_weight_per_package'])) {
                    $response['largeCartSettingFlag'] = '1';
                    $response['largeCartMaxItems'] = $settings['max_items'];
                    $response['largeCartWeightPerPackage'] = $settings['max_weight_per_package'];
                    break;
                }
            }

            return $response;
        }
    }
}

new EnUnishippersSmallShippingRulesAjaxReq();
