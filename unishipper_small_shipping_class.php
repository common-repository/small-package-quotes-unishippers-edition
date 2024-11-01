<?php

/**
 * Unishipper Small Shipping Class
 *
 * @package     Unishipper Small Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialization Function
 */
function unishippers_small_init()
{
    if (!class_exists('WC_unishipper_small')) {

        /**
         * Unishipper Small Shipping Calculation Class
         */
        class WC_unishipper_small extends WC_Shipping_Method
        {

            /** $smpkgFoundErr */
            public $smpkgFoundErr = array();

            /** $smpkgQuoteErr */
            public $smpkgQuoteErr = array();
            public $order_detail;
            public $is_autoresid;
            public $accessorials;
            public $helper_obj;
            public $unishepper_small_res_inst;
            public $api_response_unishepper_bins;
            public $instore_pickup_and_local_delivery;
            public $group_small_shipments;
            public $web_service_inst;
            public $package_plugin;
            public $InstorPickupLocalDelivery;
            public $woocommerce_package_rates;
            public $quote_settings;
            public $shipment_type;
            public $eniture_rates;
            public $VersionCompat;
            public $en_not_returned_the_quotes = FALSE;
            public $minPrices = [];
            // Virtual Products
            public $en_fdo_meta_data_third_party = [];
            // FDO
            public $en_fdo_meta_data = [];

            /**
             * Woocommerce Shipping Field Attributes
             * @param $instance_id
             */
            public function __construct($instance_id = 0)
            {
                $title = get_option('wc_settings_unishepper_small_label_as');
                (!$title) ? $title = "Unishippers" : '';
                $this->id = 'unishepper_small';
                $this->helper_obj = new Unishippers_En_Fed_Sml_Helper_Class();
                $this->instance_id = absint($instance_id);
                $this->method_title = __('Unishippers', 'eniture-unishippers-small-quotes');
                $this->method_description = __('Shipping rates from Unishippers.', 'eniture-unishippers-small-quotes');
                $this->supports = array(
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal',
                );
                $this->enabled = "yes";
                $this->title = "Small Package Quotes - Unishippers Edition";
                $this->init();
            }

            /**
             * Update Unishippers Small Woocommerce Shipping Settings
             */
            function init()
            {
                $this->init_form_fields();
                $this->init_settings();
                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            }

            /**
             * Enable Woocommerce Shipping For Unishippers Small
             */
            function init_form_fields()
            {
                $this->instance_form_fields = array(
                    'enabled' => array(
                        'title' => __('Enable / Disable', 'eniture-unishippers-small-quotes'),
                        'type' => 'checkbox',
                        'label' => __('Enable This Shipping Service', 'eniture-unishippers-small-quotes'),
                        'default' => 'no',
                        'id' => 'unishepper_small_enable_disable_shipping'
                    )
                );
            }

            /**
             * Multi shipment query
             * @param array $en_rates
             * @param string $accessorial
             */
            public function en_multi_shipment($en_rates, $accessorial, $origin)
            {
                $accessorial .= '_unishippers_small';
                $en_rates = (isset($en_rates) && (is_array($en_rates))) ? array_slice($en_rates, 0, 1) : [];
                $total_cost = array_sum($this->VersionCompat->enArrayColumn($en_rates, 'cost'));

                !$total_cost > 0 ? $this->en_not_returned_the_quotes = TRUE : '';

                $en_rates = !empty($en_rates) ? reset($en_rates) : [];
                $this->minPrices[$origin] = $en_rates;
                // FDO
                $this->en_fdo_meta_data[$origin] = (isset($en_rates['meta_data']['en_fdo_meta_data'])) ? $en_rates['meta_data']['en_fdo_meta_data'] : [];

                if (isset($this->eniture_rates[$accessorial])) {
                    $this->eniture_rates[$accessorial]['cost'] += $total_cost;
                } else {
                    $this->eniture_rates[$accessorial] = [
                        'id' => $accessorial,
                        'label' => 'Shipping',
                        'cost' => $total_cost,
                        'label_sufex' => str_split($accessorial),
                        'plugin_name' => 'unisheppers',
                        'plugin_type' => 'small',
                        'owned_by' => 'eniture'
                    ];
                }
            }

            /**
             * Single shipment query
             * @param array $en_rates
             * @param string $accessorial
             */
            public function en_single_shipment($en_rates, $accessorial, $origin)
            {
                if(is_array($this->eniture_rates) && is_array($en_rates)){
                    $this->eniture_rates = array_merge($this->eniture_rates, $en_rates);
                }else if(is_array($en_rates) && empty($this->eniture_rates)){
                    $this->eniture_rates = $en_rates;
                }
            }

            /**
             * Virtual Products
             */
            public function en_virtual_products()
            {
                global $woocommerce;
                $products = $woocommerce->cart->get_cart();
                $items = $product_name = [];
                foreach ($products as $key => $product_obj) {
                    $product = $product_obj['data'];
                    $is_virtual = $product->get_virtual();

                    if ($is_virtual == 'yes') {
                        $attributes = $product->get_attributes();
                        $product_qty = $product_obj['quantity'];
                        $product_title = str_replace(array("'", '"'), '', $product->get_title());
                        $product_name[] = $product_qty . " x " . $product_title;

                        $meta_data = [];
                        if (!empty($attributes)) {
                            foreach ($attributes as $attr_key => $attr_value) {
                                $meta_data[] = [
                                    'key' => $attr_key,
                                    'value' => $attr_value,
                                ];
                            }
                        }

                        $items[] = [
                            'id' => $product_obj['product_id'],
                            'name' => $product_title,
                            'quantity' => $product_qty,
                            'price' => $product->get_price(),
                            'weight' => 0,
                            'length' => 0,
                            'width' => 0,
                            'height' => 0,
                            'type' => 'virtual',
                            'product' => 'virtual',
                            'sku' => $product->get_sku(),
                            'attributes' => $attributes,
                            'variant_id' => 0,
                            'meta_data' => $meta_data,
                        ];
                    }
                }

                $virtual_rate = [];

                if (!empty($items)) {
                    $virtual_rate = [
                        'id' => 'en_virtual_rate',
                        'label' => 'Virtual Quote',
                        'cost' => 0,
                    ];

                    $virtual_fdo = [
                        'plugin_type' => 'small',
                        'plugin_name' => 'unishippers_small',
                        'accessorials' => '',
                        'items' => $items,
                        'address' => '',
                        'handling_unit_details' => '',
                        'rate' => $virtual_rate,
                    ];

                    $meta_data = [
                        'sender_origin' => 'Virtual Product',
                        'product_name' => wp_json_encode($product_name),
                        'en_fdo_meta_data' => $virtual_fdo,
                    ];

                    $virtual_rate['meta_data'] = $meta_data;

                }

                return $virtual_rate;
            }

            /**
             * Calculate Shipping Rates For Unishippers Small
             * @param $package
             * @return array
             */
            public function calculate_shipping($package = [], $eniture_admin_order_action = false)
            {
                if (is_admin() && !wp_doing_ajax() && !$eniture_admin_order_action) {
                    return [];
                }

                $this->package_plugin = get_option('unishepper_small_package');

                $label_sufex_arr = array();
                $Unishipper_Small_Auto_Residential_Detection = new Unishippers_Small_Auto_Residential_Detection();

                $coupn = WC()->cart->get_coupons();
                if (isset($coupn) && !empty($coupn)) {
                    $freeShipping = $this->unishepperSmpkgFreeShipping($coupn);
                    if ($freeShipping == 'y')
                        return [];
                }
                $unishepper_small_woo_obj = new Unishippers_Small_Woo_Update_Changes();
                $dest_zipcode = (strlen(WC()->customer->get_shipping_postcode()) > 0) ? WC()->customer->get_shipping_postcode() : $unishepper_small_woo_obj->unishepper_small_postcode();

                $get_packg_obj = new Unishippers_Small_Shipping_Get_Package();
                $unishepper_small_res_inst = new Unishippers_Get_Shipping_Quotes();
                $this->VersionCompat = new Unishippers_VersionCompat();

                $this->unishepper_small_res_inst = $unishepper_small_res_inst;
                $this->web_service_inst = $unishepper_small_res_inst;

                $this->get_hazardous_fields();

                $this->instore_pickup_and_local_delivery = FALSE;

                $rates = array();
                $rateArray = array();

                $quotesArray = array();
                $quotes = array();
                $SmPkgWebServiceArr = array();
                $unishepper_small_package = "";
                $unishepper_small_package = $get_packg_obj->group_unishepper_small_shipment($package, $unishepper_small_res_inst, $dest_zipcode);
                
                // apply hide methods shipping rules
                $shipping_rules_obj = new EnUnishippersSmallShippingRulesAjaxReq();
                $shipping_rules_applied = $shipping_rules_obj->apply_shipping_rules($unishepper_small_package);
                if ($shipping_rules_applied) {
                    return [];
                }

                // Suppress small rates when weight threshold is met
                $supress_parcel_rates = apply_filters('en_suppress_parcel_rates_hook', '');
                if (!empty($unishepper_small_package) && is_array($unishepper_small_package) && $supress_parcel_rates) {
                    foreach ($unishepper_small_package as $org_id => $pckg) {
                        $total_shipment_weight = 0;

                        $shipment_items = !empty($pckg['items']) ? $pckg['items'] : []; 
                        foreach ($shipment_items as $item) {
                            $total_shipment_weight += (floatval($item['productWeight']) * $item['productQty']);
                        }

                        $unishepper_small_package[$org_id]['shipment_weight'] = $total_shipment_weight;
                        $weight_threshold = get_option('en_weight_threshold_lfq');
                        $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;
                        
                        if ($total_shipment_weight > $weight_threshold) {
                            $unishepper_small_package[$org_id]['is_shipment'] = 'ltl';
                            $unishepper_small_package[$org_id]['origin']['ptype'] = 'ltl';
                        }
                    }
                }

                $no_param_multi_ship = 0;
                $services = array();
                $services_list = array();

                $domestic_services = apply_filters('unishepper_small_domestic_services', array());

                $intrntal_services = apply_filters('unishepper_small_international_services', array());

                $service_flag = (!empty($domestic_services) || (!empty($intrntal_services))) ? TRUE : FALSE;

                if (isset($unishepper_small_package) && !empty($unishepper_small_package) && $service_flag) {

                    // Free shipping for -100% handling fee
                    $handling_fee = get_option('unishipper_small_hand_fee_mark_up');
                    if (!empty($handling_fee) && $handling_fee == '-100%') {
                        $rates = array(
                            'id' => 'unishepper_small:' . 'free',
                            'label' => 'Free Shipping',
                            'cost' => 0,
                            'plugin_name' => 'unisheppers',
                            'plugin_type' => 'small',
                            'owned_by' => 'eniture'
                        );
                        $this->add_rate($rates);
                        
                        return [];
                    }

                    $services_list['domestic_services'] = $domestic_services;
                    $services_list['intrntal_services'] = $intrntal_services;
                    
                    $SmPkgWebServiceArr = $unishepper_small_res_inst->unishippers_small_shipping_array($unishepper_small_package, $package, $services_list, $this->package_plugin);
                    $counter = 0;

                    foreach ($SmPkgWebServiceArr as $locId => $sPackage) {

                        if ($sPackage != 'ltl') {

                            $EnUnishipperSmallTransitDays = new Unishippers_EnUnishipperSmallTransitDays();

                            $senderZip = (isset($sPackage['senderZip'])) ? $sPackage['senderZip'] : '';
                            $package_bins = (isset($sPackage['bins'])) ? $sPackage['bins'] : [];
                            $en_box_fee = (isset($sPackage['en_box_fee'])) ? $sPackage['en_box_fee'] : [];
                            $en_multi_box_qty = (isset($sPackage['count'])) ? $sPackage['count'] : [];
                            $fedex_bins = (isset($sPackage['fedex_bins'])) ? $sPackage['fedex_bins'] : [];
                            $hazardous_status = (isset($sPackage['hazardous_status'])) ? $sPackage['hazardous_status'] : '';
                            // New API hazardous check
                            $hazardous_status = (isset($sPackage['hazardous_material'])) ? $sPackage['hazardous_material'] : $hazardous_status;
                            $package_bins = !empty($fedex_bins) ? $package_bins + $fedex_bins : $package_bins;
                            if (!strlen($senderZip) > 0) {
                                continue;
                            }

                            $this->unishepper_small_res_inst->product_detail[$senderZip]['product_name'] = wp_json_encode($sPackage['product_name']);
                            $this->unishepper_small_res_inst->product_detail[$senderZip]['products'] = $sPackage['products'];
                            $this->unishepper_small_res_inst->product_detail[$senderZip]['sender_origin'] = $sPackage['sender_origin'];
                            $this->unishepper_small_res_inst->product_detail[$senderZip]['package_bins'] = $package_bins;
                            $this->unishepper_small_res_inst->product_detail[$senderZip]['en_box_fee'] = $en_box_fee;
                            $this->unishepper_small_res_inst->product_detail[$senderZip]['en_multi_box_qty'] = $en_multi_box_qty;
                            $this->unishepper_small_res_inst->product_detail[$senderZip]['hazardous_status'] = $hazardous_status;
                            $this->unishepper_small_res_inst->product_detail[$senderZip]['origin_markup'] = $sPackage['origin_markup'];
                            $this->unishepper_small_res_inst->product_detail[$senderZip]['product_level_markup'] = $sPackage['product_level_markup'];
                            $this->unishepper_small_res_inst->product_detail[$senderZip]['exempt_ground_transit_restriction'] = (isset($sPackage['exempt_ground_transit_restriction'])) ? $sPackage['exempt_ground_transit_restriction'] : '';

                            // FDO
                            $en_fdo_meta_data = (isset($sPackage['en_fdo_meta_data'])) ? $sPackage['en_fdo_meta_data'] : '';
                            $this->unishepper_small_res_inst->product_detail[$senderZip]['en_fdo_meta_data'] = $en_fdo_meta_data;

                            if (isset($sPackage['receiverCountryCode']) && $sPackage['receiverCountryCode'] != $sPackage['senderCountryCode']) {
                                $services['international'] = $intrntal_services;
                            }
                            if (isset($sPackage['receiverCountryCode']) && $sPackage['receiverCountryCode'] == $sPackage['senderCountryCode']) {
                                $services['domestic'] = $domestic_services;
                            }

                            $quotes[$locId] = $unishepper_small_res_inst->unishepper_small_get_quotes($sPackage, $this->package_plugin);

                            (isset($sPackage['hazardous_material'])) ? $quotes[$locId]->hazardous_material = TRUE : "";


                            $Unishipper_Small_Auto_Residential_Detection = new Unishippers_Small_Auto_Residential_Detection();
                            $label_sfx_rtrn = $Unishipper_Small_Auto_Residential_Detection->filter_label_sufex_array_unishepper_small($quotes[$locId]);
                            $label_sufex_arr = array_merge($label_sufex_arr, $label_sfx_rtrn);
                        }

                        $counter++;
                    }

                    $this->InstorPickupLocalDelivery = (isset($quotes[$locId]->InstorPickupLocalDelivery)) ? $quotes[$locId]->InstorPickupLocalDelivery : array();
                }

                // Virtual products
                $virtual_rate = $this->en_virtual_products();
                $en_is_shipment = (count($quotes) > 1 || $no_param_multi_ship == 1) || $no_param_multi_ship == 1 || !empty($virtual_rate) ? 'en_multi_shipment' : 'en_single_shipment';
                $this->quote_settings['shipment'] = $en_is_shipment;
                $this->eniture_rates = [];

                $en_rates = $quotes;

                // apply override rates shipping rules
                $shipping_rule_obj = new EnUnishippersSmallShippingRulesAjaxReq;
                $en_rates = $shipping_rule_obj->apply_shipping_rules($unishepper_small_package, true, $en_rates);

                foreach ($en_rates as $origin => $step_for_rates) {
                    if (!isset($step_for_rates->error)) {
                        $product_detail = (isset($this->unishepper_small_res_inst->product_detail[$origin])) ? $this->unishepper_small_res_inst->product_detail[$origin] : array();
                        (isset($domestic_international[$origin])) ? $services = $domestic_international[$origin] : '';

                        $filterd_rates = $unishepper_small_res_inst->parse_unishipper_small_output($step_for_rates, $services, $product_detail, $this->quote_settings);

                        $en_sorting_rates = (isset($filterd_rates['en_sorting_rates'])) ? $filterd_rates['en_sorting_rates'] : [];

                        if (isset($filterd_rates['en_sorting_rates']))
                            unset($filterd_rates['en_sorting_rates']);

                        if (is_array($filterd_rates) && !empty($filterd_rates) && !isset($filterd_rates['error'])) {
                            foreach ($filterd_rates as $accessorial => $service) {
                                (!empty($filterd_rates[$accessorial])) ? array_multisort($en_sorting_rates[$accessorial], SORT_ASC, $filterd_rates[$accessorial]) : $en_sorting_rates[$accessorial] = [];
                                $this->$en_is_shipment($filterd_rates[$accessorial], $accessorial, $origin);
                            }
                        } else {
                            $this->en_not_returned_the_quotes = TRUE;
                        }

                        // Add backup rates
                        if (($this->en_not_returned_the_quotes && get_option('backup_rates_carrier_returns_error_unishippers_small') == 'yes') || (is_array($filterd_rates) && isset($filterd_rates['error']) && $filterd_rates['error'] == 'backup_rate' && get_option('backup_rates_carrier_fails_to_return_response_unishippers_small') == 'yes')) {
                            $this->unishipper_small_backup_rates();
                            return [];
                        }
                    } else {
                        $this->en_not_returned_the_quotes = TRUE;
                    }
                }


                if ($en_is_shipment == 'en_single_shipment') {

                    // In-store pickup and local delivery
                    $instore_pickup_local_devlivery_action = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'instore_pickup_local_devlivery');
                    if (isset($this->web_service_inst->en_wd_origin_array['suppress_local_delivery']) && $this->web_service_inst->en_wd_origin_array['suppress_local_delivery'] == "1" && (!is_array($instore_pickup_local_devlivery_action))) {
                        $this->eniture_rates = apply_filters('suppress_local_delivery', $this->eniture_rates, $this->web_service_inst->en_wd_origin_array, $this->package_plugin, $this->InstorPickupLocalDelivery);
                    }
                }
                $rad_status = true;
                $all_plugins = apply_filters('active_plugins', get_option('active_plugins'));
                if (stripos(implode($all_plugins), 'residential-address-detection.php') || is_plugin_active_for_network('residential-address-detection/residential-address-detection.php')) {
                    if(get_option('suspend_automatic_detection_of_residential_addresses') != 'yes') {
                        $rad_status = get_option('residential_delivery_options_disclosure_types_to') != 'not_show_r_checkout';
                    }
                }
                $accessorials = $rad_status == true ? ['R' => 'residential delivery'] : [];

                add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);

                // Images for FDO
                $image_urls = apply_filters('en_fdo_image_urls_merge', []);

                // Virtual products
                if (!empty($virtual_rate)) {
                    $en_virtual_fdo_meta_data[] = $virtual_rate['meta_data']['en_fdo_meta_data'];
                    $this->en_fdo_meta_data_third_party = !empty($this->en_fdo_meta_data_third_party) ? array_merge($this->en_fdo_meta_data_third_party, $en_virtual_fdo_meta_data) : $en_virtual_fdo_meta_data;
                }

                $en_rates = $this->eniture_rates;

                foreach ($en_rates as $accessorial => $rate) {

                    // show delivery estimates
                    if ($en_is_shipment == 'en_single_shipment') {

                        $unishippers_small_show_delivery_estimates_plan = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'unishippers_small_show_delivery_estimates');
                        $unishippers_small_delivey_estimate = get_option('unishippers_small_delivery_estimates');

                        if (isset($unishippers_small_delivey_estimate) && !empty($unishippers_small_delivey_estimate) && $unishippers_small_delivey_estimate != 'dont_show_estimates' && !is_array($unishippers_small_show_delivery_estimates_plan)) {
                            if ($unishippers_small_delivey_estimate == 'delivery_date' && !empty($rate['transit_time'])) {
                                $rate['label'] .= ' (Expected delivery by ' . date('m-d-Y', strtotime($rate['transit_time'])) . ')';
                            } else if ($unishippers_small_delivey_estimate == 'delivery_days' && !empty($rate['delivery_days'])) {
                                $correct_word = ($rate['delivery_days'] == 1) ? 'is' : 'are';
                                $rate['label'] .= ' (Intransit days: ' . $rate['delivery_days'] . ')';
                            }
                        }
                    }

                    if (isset($rate['label_sufex']) && !empty($rate['label_sufex'])) {
                        // Custom work mgs4u ref ticket #46864896
                        if (has_filter('en_update_rate_through_cart_enhancement')) {
                            $rate = apply_filters('en_update_rate_through_cart_enhancement', $rate);
                        } else {
                            $label_sufex = array_intersect_key($accessorials, array_flip($rate['label_sufex']));
                            $rate['label'] .= (!empty($label_sufex)) ? ' with ' . implode(' and ', $label_sufex) : '';
                        }

                        // Order widget detail set
                        // FDO
                        if (isset($this->minPrices) && !empty($this->minPrices)) {
                            $rate['minPrices'] = $this->minPrices;
                            $rate['meta_data']['min_prices'] = wp_json_encode($this->minPrices);
                            $rate['meta_data']['en_fdo_meta_data']['data'] = array_values($this->en_fdo_meta_data);
                            // Virtual Products
                            (!empty($this->en_fdo_meta_data_third_party)) ? $rate['meta_data']['en_fdo_meta_data']['data'] = array_merge($rate['meta_data']['en_fdo_meta_data']['data'], $this->en_fdo_meta_data_third_party) : '';
                            $rate['meta_data']['en_fdo_meta_data']['shipment'] = 'multiple';
                            $rate['meta_data']['en_fdo_meta_data'] = wp_json_encode($rate['meta_data']['en_fdo_meta_data']);
                        } else {
                            $en_set_fdo_meta_data['data'] = [$rate['meta_data']['en_fdo_meta_data']];
                            $en_set_fdo_meta_data['shipment'] = 'sinlge';
                            $rate['meta_data']['en_fdo_meta_data'] = wp_json_encode($en_set_fdo_meta_data);
                        }

                        // Images for FDO
                        $rate['meta_data']['en_fdo_image_urls'] = wp_json_encode($image_urls);
                    }

                    if (isset($rate['cost']) && $rate['cost'] > 0) {
                        $rate['id'] = isset($rate['id']) && is_string($rate['id']) ? 'unishepper_small:' . $rate['id'] : '';
                        $this->add_rate($rate);
                        $en_rates[$accessorial] = array_merge($en_rates[$accessorial], $rate);
                    }
                }

                // Origin terminal address
                if ($en_is_shipment == 'en_single_shipment') {
                    (isset($this->InstorPickupLocalDelivery->localDelivery) && ($this->InstorPickupLocalDelivery->localDelivery->status == 1)) ? $this->local_delivery($this->web_service_inst->en_wd_origin_array['fee_local_delivery'], $this->web_service_inst->en_wd_origin_array['checkout_desc_local_delivery'], $this->web_service_inst->en_wd_origin_array) : "";
                    (isset($this->InstorPickupLocalDelivery->inStorePickup) && ($this->InstorPickupLocalDelivery->inStorePickup->status == 1)) ? $this->pickup_delivery($this->web_service_inst->en_wd_origin_array['checkout_desc_store_pickup'], $this->web_service_inst->en_wd_origin_array, $this->InstorPickupLocalDelivery->totalDistance) : "";
                }
                return $en_rates;
            }

            /**
             * Add Hazardous Fee
             * @param string $service_code
             * @param array $quote_settings
             * @return string
             */
            function add_hazardous_material($service_code)
            {
                $hazardous_fee = get_option('en_unishippers_ground_hazardous_material_fee');
                $air_hazardous_fee = get_option('en_unishippers_air_hazardous_material_fee');
                return ($service_code == "SG") ? $hazardous_fee : $air_hazardous_fee;
            }

            /**
             * Hazardouds values quote settings
             */
            function get_hazardous_fields()
            {
                $this->quote_settings = array();
                $this->quote_settings['hazardous_materials_shipments'] = get_option('unishepper_small_hazardous_materials_shipments');
                $this->quote_settings['ground_hazardous_material_fee'] = get_option('en_unishippers_ground_hazardous_material_fee');
                $this->quote_settings['air_hazardous_material_fee'] = get_option('en_unishippers_air_hazardous_material_fee');
                $this->quote_settings['residential_delivery'] = get_option('unishepper_small_quote_as_residential_delivery');
                $this->quote_settings['handling_fee'] = get_option('unishipper_small_hand_fee_mark_up');
                $this->quote_settings['services'] = [
                    'domestic' => apply_filters('unishepper_small_domestic_services', []),
                    'international' => apply_filters('unishepper_small_international_services', [])
                ];
            }

            /**
             * final rates sorting
             * @param array type $rates
             * @param array type $package
             * @return array type
             */
            function en_sort_woocommerce_available_shipping_methods($rates, $package)
            {
                //  if there are no rates don't do anything

                if (!$rates) {
                    return [];
                }

                // Check the option to sort shipping methods by price on quote settings
                if (get_option('shipping_methods_do_not_sort_by_price') != 'yes') {

                    $local_delivery = isset($rates['local-delivery']) ? $rates['local-delivery'] : '';
                    $in_store_pick_up = isset($rates['in-store-pick-up']) ? $rates['in-store-pick-up'] : '';

                    // get an array of prices
                    $prices = array();
                    foreach ($rates as $rate) {
                        $prices[] = $rate->cost;
                    }

                    // use the prices to sort the rates
                    array_multisort($prices, $rates);

//              unset instore-pickup & local delivery and set at the end of quotes array
                    if (isset($in_store_pick_up) && !empty($in_store_pick_up)) {
                        unset($rates['in-store-pick-up']);
                        $rates['in-store-pick-up'] = $in_store_pick_up;
                    }
                    if (isset($local_delivery) && !empty($local_delivery)) {
                        unset($rates['local-delivery']);
                        $rates['local-delivery'] = $local_delivery;
                    }
                }

                // return the rates
                return $rates;
            }

            /**
             * Pickup delivery quote
             * @return array type
             */
            function pickup_delivery($label, $en_wd_origin_array, $total_distance)
            {
                $this->woocommerce_package_rates = 1;
                $this->instore_pickup_and_local_delivery = TRUE;

                $label = (isset($label) && (strlen($label) > 0)) ? $label : 'In-store pick up';
                // Origin terminal address
                $address = (isset($en_wd_origin_array['address'])) ? $en_wd_origin_array['address'] : '';
                $city = (isset($en_wd_origin_array['city'])) ? $en_wd_origin_array['city'] : '';
                $state = (isset($en_wd_origin_array['state'])) ? $en_wd_origin_array['state'] : '';
                $zip = (isset($en_wd_origin_array['zip'])) ? $en_wd_origin_array['zip'] : '';
                $phone_instore = (isset($en_wd_origin_array['phone_instore'])) ? $en_wd_origin_array['phone_instore'] : '';
                strlen($total_distance) > 0 ? $label .= ' | ' . str_replace("mi", "miles", $total_distance) . ' away' : '';
                strlen($address) > 0 ? $label .= ' | ' . $address : '';
                strlen($city) > 0 ? $label .= ', ' . $city : '';
                strlen($state) > 0 ? $label .= ' ' . $state : '';
                strlen($zip) > 0 ? $label .= ' ' . $zip : '';
                strlen($phone_instore) > 0 ? $label .= ' | ' . $phone_instore : '';

                $pickup_delivery = array(
                    'id' => 'unishepper_small:' . 'in-store-pick-up',
                    'cost' => !empty($en_wd_origin_array['fee_store_pickup']) ? $en_wd_origin_array['fee_store_pickup'] : 0,
                    'label' => $label,
                    'plugin_name' => 'unisheppers',
                    'plugin_type' => 'small',
                    'owned_by' => 'eniture'
                );

                add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                $this->add_rate($pickup_delivery);
            }

            /**
             * Local delivery quote
             * @param string type $cost
             * @return array type
             */
            function local_delivery($cost, $label, $en_wd_origin_array)
            {
                $this->woocommerce_package_rates = 1;
                $this->instore_pickup_and_local_delivery = TRUE;
                $label = (isset($label) && (strlen($label) > 0)) ? $label : 'Local Delivery';
                $local_delivery = array(
                    'id' => 'unishepper_small:' . 'local-delivery',
                    'cost' => !empty($cost) ? $cost : 0,
                    'label' => $label,
                    'plugin_name' => 'unisheppers',
                    'plugin_type' => 'small',
                    'owned_by' => 'eniture'
                );

                add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                $this->add_rate($local_delivery);
            }

            /**
             * Check is free shipping or not
             * @param $coupon
             * @return string
             */
            function unishepperSmpkgFreeShipping($coupon)
            {
                foreach ($coupon as $key => $value) {
                    if ($value->get_free_shipping() == 1) {
                        $rates = array(
                            'id' => 'free',
                            'label' => 'Free Shipping',
                            'cost' => 0,
                            'plugin_name' => 'unisheppers',
                            'plugin_type' => 'small',
                            'owned_by' => 'eniture'
                        );
                        $this->add_rate($rates);
                        return 'y';
                    }
                }
            }

            /**
            * Adds backup rates in the shipping rates
            * @return void
            * */
            function unishipper_small_backup_rates()
            {
                if (get_option('enable_backup_rates_unishippers_small') != 'yes' || (get_option('backup_rates_carrier_fails_to_return_response_unishippers_small') != 'yes' && get_option('backup_rates_carrier_returns_error_unishippers_small') != 'yes')) return;

                $backup_rates_type = get_option('backup_rates_category_unishippers_small');
                $backup_rates_cost = 0;

                if ($backup_rates_type == 'fixed_rate' && !empty(get_option('backup_rates_fixed_rate_unishippers_small'))) {
                    $backup_rates_cost = get_option('backup_rates_fixed_rate_unishippers_small');
                } elseif ($backup_rates_type == 'percentage_of_cart_price' && !empty(get_option('backup_rates_cart_price_percentage_unishippers_small'))) {
                    $cart_price_percentage = floatval(str_replace('%', '', get_option('backup_rates_cart_price_percentage_unishippers_small')));
                    $backup_rates_cost = ($cart_price_percentage * WC()->cart->get_subtotal()) / 100;
                } elseif ($backup_rates_type == 'function_of_weight' && !empty(get_option('backup_rates_weight_function_unishippers_small'))) {
                    $cart_weight = wc_get_weight(WC()->cart->get_cart_contents_weight(), 'lbs');
                    $backup_rates_cost = get_option('backup_rates_weight_function_unishippers_small') * $cart_weight;
                }

                if ($backup_rates_cost > 0) {
                    $backup_rates = array(
                        'id' => $this->id . ':' . 'backup_rates',
                        'label' => get_option('backup_rates_label_unishippers_small'),
                        'cost' => $backup_rates_cost,
                        'plugin_name' => 'unisheppers',
                        'plugin_type' => 'small',
                        'owned_by' => 'eniture'
                    );

                    $this->add_rate($backup_rates);
                }
            }

        }

    }
}
