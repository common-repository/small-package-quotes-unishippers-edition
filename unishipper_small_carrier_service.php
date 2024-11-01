<?php

/**
 * Unishipper Small Carrier Service
 *
 * @package     Unishipper Small Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get Quotes For Unishipper Small
 */
class Unishippers_Get_Shipping_Quotes extends Unishippers_EnUnishippersSmallFdo
{

    public $en_wd_origin_array;
    public $forcefully_residential_delivery = FALSE;
    public $forcefully_always_residential_delivery = FALSE;

    /** $unishepper_sm_errors */
    public $unishepper_sm_errors = array();
    public $simple_quotes;

    /** $no_services_select */
    public $no_services_select = array();
    public $product_detail = array();
    public $hazardous_status;

    /**
     * Array For Getting Quotes
     * @param $packages
     * @param $content
     * @return array
     */
    public function unishippers_small_shipping_array($packages, $content, $services_list, $package_plugin = "")
    {
        // FDO
        $en_fdo_meta_data = $post_data = array();

        $accessorial = array();
        $destinationAddressUnishipperSmall = $this->destinationAddressUnishipperSmall();
        $exceedWeight = get_option('wc_settings_wwe_return_LTL_quotes');
        (get_option('unishepper_small_quote_as_residential_delivery') == 'yes') ? $accessorial[] = 'REP' : '';
        $en_shipments = (isset($content['en_shipments'])) ? $content['en_shipments'] : [];
        $Pweight = 0;
        $findLtl = 0;
        $weight_threshold = get_option('en_weight_threshold_lfq');
        $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;
        $signatures = ['adult_signature' => 'ADV', 'signature' => 'SUR'];

        foreach ($packages as $package) {
            $productName = array();
            $productQty = array();
            $productPrice = array();
            $productWeight = array();
            $productLength = array();
            $productWidth = array();
            $productHeight = array();
            $product_name = array();
            $products = array();
            $product_count = array();
            $total_weight = 0;
            $total_girth = 0;
            $ship_item_alone = $product_tag = $pricing_per_product = $new_api_product_name = [];
            $product_insurance_apply = false;

            $this->en_wd_origin_array = (isset($package['origin'])) ? $package['origin'] : array();
            $package_zip = (isset($package['origin']['zip'])) ? $package['origin']['zip'] : '';

            // Remove signature accessorials for each package in multi-shipment case. So it should not contains previous shipment signature values
            foreach ($accessorial as $key => $acc) {
                if ($acc == $signatures['adult_signature'] || $acc == $signatures['signature']) {
                    unset($accessorial[$key]);
                }
            }

            if (!($exceedWeight == 'yes' && $Pweight > $weight_threshold) &&
                (empty($en_shipments) || (!empty($en_shipments) && isset($en_shipments[$package_zip]))) &&
                (!isset($package['is_shipment']) || (isset($package['is_shipment']) && $package['is_shipment'] != 'ltl'))) {

                $lineItem = array();
                $productIdCount = 0;
                $doNesting = '0';
                $product_markup_shipment = 0;
                
                foreach ($package['items'] as $item) {
                    $lineItem[$productIdCount] = array(
                        'lineItemWeight' => $item['productWeight'],
                        'lineItemLength' => $item['productLength'],
                        'lineItemWidth' => $item['productWidth'],
                        'lineItemHeight' => $item['productHeight'],
                        'lineItemDescription' => $item['productName'],
                        'lineItemPrice' => $item['productPrice'],
                        'piecesOfLineItem' => $item['productQty'],
                        'ship_item_alone' => isset($item['ship_item_alone']) ? $item['ship_item_alone'] : '',
                    );

                    if (isset($item['nestedMaterial']) && $item['nestedMaterial'] == 'yes') {
                        $doNesting = '1';

                        // product nesting details
                        $nestingOptions = array(
                            'nestingPercentage' => isset($item['nestedPercentage']) ? $item['nestedPercentage'] : '',
                            'nestingDimension' => isset($item['nestedDimension']) ? $item['nestedDimension'] : '',
                            'nestedLimit' => isset($item['nestedItems']) ? $item['nestedItems'] : '', 
                            'nestedStackProperty' => isset($item['stakingProperty']) ? $item['stakingProperty'] : '',
                        );

                        $lineItem[$productIdCount] = array_merge($lineItem[$productIdCount], $nestingOptions);
                    }

                    $lineItem[$productIdCount] = apply_filters('en_fdo_carrier_service', $lineItem[$productIdCount], $item);

                    $product_count[$productIdCount] = $item['productQty'];
                    $product_name[] = $item['product_name'];
                    $products[] = $item['products'];

                     if (!empty($item['markup']) && is_numeric($item['markup'])){
                            $product_markup_shipment += $item['markup'];
                    }

                    // Product signature required
                    if (isset($item['product_signature']) && isset($signatures[$item['product_signature']]) && !in_array($signatures['adult_signature'], $accessorial)) {
                        foreach ($accessorial as $key => $value) {
                            if ($value == $signatures['signature']) {
                                unset($accessorial[$key]);
                                break;
                            }
                        }

                        $accessorial[] = $signatures[$item['product_signature']];
                    }

                    // New API details block
                    $product_tag[$productIdCount] = (isset($item['product_tag'])) ? $item['product_tag'] : '';
                    $ship_item_alone[$productIdCount] = (isset($item['ship_item_alone'])) ? $item['ship_item_alone'] : '';
                    $product_id = (isset($item['variantId']) && $item['variantId'] > 0) ? $item['variantId'] : $item['productId'];

                    $productName[$productIdCount] = $item['productName'];
                    $productLength[$productIdCount] = $item['productLength'];
                    $productWidth[$productIdCount] = $item['productWidth'];
                    $productHeight[$productIdCount] = $item['productHeight'];
                    $productWeight[$productIdCount] = $item['productWeight'];
                    $productQty[$productIdCount] = $item['productQty'];
                    $productPrice[$productIdCount] = $item['productPrice'];
                    $productClass[$productIdCount] = isset($item['productClass']) ? $item['productClass'] : '';
                    $new_api_product_name[$productIdCount] = $item['product_name'];
                    $nestingPercentage[$productIdCount] = isset($item['nestedPercentage']) ? $item['nestedPercentage'] : '';
                    $nestedDimension[$productIdCount] = isset($item['nestedDimension']) ? $item['nestedDimension'] : '';
                    $nestedItems[$productIdCount] = isset($item['nestedItems']) ? $item['nestedItems'] : '';
                    $stakingProperty[$productIdCount] = isset($item['stakingProperty']) ? $item['stakingProperty'] : '';
                    
                    $product_insurance = isset($item['product_insurance']) ? $item['product_insurance'] : 0;
                    isset($product_insurance) && $product_insurance > 0 ? $product_insurance_apply = true : '';

                    // Shippable handling units
                    $pricing_per_product[$productIdCount] = [
                        'product_insurance' => $product_insurance,
                        'product_markup' => isset($item['product_markup']) ? $item['product_markup'] : '',
                        'product_rental' => isset($item['product_rental']) ? $item['product_rental'] : '',
                        'product_quantity' => isset($item['product_quantity']) ? $item['product_quantity'] : '',
                        'product_price' => isset($item['product_price']) ? $item['product_price'] : '',
                    ];

                    $productIdCount++;
                }

                $domain = unishippers_small_get_domain();

                $getVersion = $this->unishepperSmpkgWcVersionNumber();

                $shipmentWeekDays = "";
                $orderCutoffTime = "";
                $shipmentOffsetDays = "";
                $modifyShipmentDateTime = "";
                $storeDateTime = "";

                // Start Cut Off Time & Ship Date Offset
                $unishippers_small_delivery_estimates = get_option('unishippers_small_delivery_estimates');
                $unishippers_small_show_delivery_estimates = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'unishippers_small_show_delivery_estimates');
                // shipment days of a week
                $shipmentWeekDays = $this->unishippers_small_shipment_week_days();
                if ($unishippers_small_delivery_estimates == 'delivery_days' || $unishippers_small_delivery_estimates == 'delivery_date' && !is_array($unishippers_small_show_delivery_estimates)) {
                    $orderCutoffTime = get_option('unishippers_small_orderCutoffTime');
                    $shipmentOffsetDays = get_option('unishippers_small_shipmentOffsetDays');
                    $modifyShipmentDateTime = ($orderCutoffTime != '' || $shipmentOffsetDays != '' || (is_array($shipmentWeekDays) && count($shipmentWeekDays) > 0)) ? 1 : 0;
                    $storeDateTime = date('Y-m-d H:i:s', current_time('timestamp'));
                }

                $package_type = get_option('unishippers_small_packaging_method');
                $per_package_weight = '';
                if('ship_one_package_70' == $package_type){
                    $package_type = 'ship_as_one';
                    $per_package_weight = '70';
                }elseif('ship_one_package_150' == $package_type){
                    $package_type = 'ship_as_one';
                    $per_package_weight = '150';
                }

                // FDO
                $en_fdo_meta_data = $this->en_cart_package($package);
                $new_api_enabled = get_option('api_endpoint_unishippers_small') == 'unishippers_small_new_api';

                if ($new_api_enabled) {
                    $s_post_data = array(
                        'platform' => 'wordpress',
                        'carrierName' => 'WWE SmPkg',
                        'plugin_version' => $getVersion["unishepperSmpkg_plugin_version"],
                        'wordpress_version' => get_bloginfo('version'),
                        'woocommerce_version' => $getVersion["woocommerce_plugin_version"],
                        'plugin_licence_key' => get_option('unishepper_small_licence_key'),
                        'speed_ship_domain_name' => unishippers_small_get_domain($domain),
                        'ApiVersion' => '2.0',
                        'clientId' => get_option('unishippers_small_client_id'),
                        'clientSecret' => get_option('unishippers_small_client_secret'),
                        'speed_ship_username' => get_option('unishippers_small_new_api_username'),
                        'speed_ship_password' => get_option('unishippers_small_new_api_password'),
                        'speed_ship_reciver_city' => $destinationAddressUnishipperSmall['city'],
                        'speed_ship_receiver_state' => $destinationAddressUnishipperSmall['state'],
                        'speed_ship_receiver_zip_code' => $destinationAddressUnishipperSmall['zip'],
                        'speed_ship_senderCity' => isset($package['origin']['city']) ? $package['origin']['city'] : '',
                        'speed_ship_senderState' => isset($package['origin']['state']) ? $package['origin']['state'] : '',
                        'speed_ship_senderZip' => isset($package['origin']['zip']) ? $package['origin']['zip'] : '',
                        'speed_ship_senderCountryCode' => isset($package['origin']['country']) ? $package['origin']['country'] : '',
                        'residentials_delivery' => get_option('unishepper_small_quote_as_residential_delivery'),
                        // Product Information
                        'product_width_array' => $productWidth,
                        'product_height_array' => $productHeight,
                        'product_length_array' => $productLength,
                        'speed_ship_product_price_array' => $productPrice,
                        'speed_ship_product_weight' => $productWeight,
                        'speed_ship_title_array' => $new_api_product_name,
                        'speed_ship_quantity_array' => $productQty,
                        'sender_origin' => isset($package['origin']) ? $package['origin']['location'] . ": " . $package['origin']['city'] . ", " . $package['origin']['state'] . " " . $package['origin']['zip'] : '',
                        'product_name' => $new_api_product_name,
                        'products' => $products,
                        // FDO
                        'en_fdo_meta_data' => $en_fdo_meta_data,
                        'modifyShipmentDateTime' => $modifyShipmentDateTime,
                        'OrderCutoffTime' => $orderCutoffTime,
                        'shipmentOffsetDays' => $shipmentOffsetDays,
                        'storeDateTime' => $storeDateTime,
                        'shipmentWeekDays' => $shipmentWeekDays,
                        'nesting_percentage' => $nestingPercentage,
                        'nesting_dimension' => $nestedDimension,
                        'nested_max_limit' => $nestedItems,
                        'nested_stack_property' => $stakingProperty,
                        'product_tags_array' => $product_tag,
                        // Shippable item
                        'ship_item_alone' => $ship_item_alone,
                        'origin_markup' => (isset($package['origin']['origin_markup'])) ? $package['origin']['origin_markup'] : 0,
                        'product_level_markup' => $product_markup_shipment,
                        // Pricing per product
                        'pricing_per_product' => $pricing_per_product,
                        'packagesType' => $package_type,
                        'perPackageWeight' => $per_package_weight,
                        // Sbs optimization mode
                        'sbsMode' => get_option('box_sizing_optimization_mode'),
                        'senderZip' => $package['origin']['zip'],
                        'senderCountryCode' => $package['origin']['country'],
                        'receiverCountryCode' => $destinationAddressUnishipperSmall['country'],
                        'count' => $product_count,
                        'isUnishipperNewApi' => 'yes',
                        'requestFromUnishippersSmall' => '1'
                    );

                    if (in_array($signatures['adult_signature'], $accessorial)) {
                        $s_post_data['adultSignatureRequiredFlag'] = '1';
                    } elseif (in_array($signatures['signature'], $accessorial)) {
                        $s_post_data['isSignatureRequiredFlag'] = '1';
                    }
                } else {
                    $s_post_data = array(
                        'platform' => 'WordPress',
                        'plugin_version' => $getVersion["unishepperSmpkg_plugin_version"],
                        'wordpress_version' => get_bloginfo('version'),
                        'woocommerce_version' => $getVersion["woocommerce_plugin_version"],
                        'carrierName' => 'unisheppers',
                        'carrier_mode' => 'pro', // use test / pro
                        'username' => get_option('unishepper_username'),
                        'password' => get_option('unishepper_password'),
                        'requestkey' => get_option('unishepper_small_auth_key'),
                        'upsaccountnumber' => get_option('unishepper_ups_account_number'),
                        'unishipperscustomernumber' => get_option('unishepper_small_customer_account_number'),
                        'licence_key' => get_option('unishepper_small_licence_key'),
                        'server_name' => unishippers_small_get_domain($domain),
                        'receiverCity' => $destinationAddressUnishipperSmall['city'],
                        'receiverState' => $destinationAddressUnishipperSmall['state'],
                        'receiverZip' => $destinationAddressUnishipperSmall['zip'],
                        'receiverCountryCode' => $destinationAddressUnishipperSmall['country'],
                        'senderCity' => $package['origin']['city'],
                        'senderState' => $package['origin']['state'],
                        'senderZip' => $package['origin']['zip'],
                        'senderCountryCode' => $package['origin']['country'],
                        'accessorial' => $accessorial,
                        'service' => 'ALL',
                        'packagetype' => 'P',
                        'commdityDetails' => $lineItem,
                        'sender_origin' => (isset($package['origin']['sender_origin'])) ? $package['origin']['sender_origin'] : $package['origin']['location'] . ": " . $package['origin']['city'] . ", " . $package['origin']['state'] . " " . $package['origin']['zip'],
                        'product_name' => $product_name,
                        'products' => $products,
                        'modifyShipmentDateTime' => $modifyShipmentDateTime,
                        'OrderCutoffTime' => $orderCutoffTime,
                        'shipmentOffsetDays' => $shipmentOffsetDays,
                        'storeDateTime' => $storeDateTime,
                        'shipmentWeekDays' => $shipmentWeekDays,
                        'count' => $product_count,
                        // FDO
                        'en_fdo_meta_data' => $en_fdo_meta_data,
                        'packagesType' => $package_type,
                        'perPackageWeight' => $per_package_weight,
                        // Sbs optimization mode
                        'sbsMode' => get_option('box_sizing_optimization_mode'),
                        'origin_markup' => (isset($package['origin']['origin_markup'])) ? $package['origin']['origin_markup'] : 0,
                        'product_level_markup' => $product_markup_shipment
                    );
                }

                // get large cart settings shipping rules
                $large_cart_settings = (new EnUnishippersSmallShippingRulesAjaxReq())->get_large_cart_settings();
                $s_post_data = array_merge($s_post_data, $large_cart_settings);

                // Nesting materials
                $action_nesting = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'nested_material');
                $s_post_data['doNesting'] = !is_array($action_nesting) ? $doNesting : '';

                // Insurance Fee
                $action_insurance = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'insurance_fee');
                if (!is_array($action_insurance) && $product_insurance_apply) {
                    if ($new_api_enabled) {
                        $s_post_data['includeInsuranceValue'] = 1;
                        $s_post_data['declaredValueCurrencyCode'] = 'USD';
                    } else {
                        $s_post_data['includeDeclaredValue'] = 1;
                    }
                }

                // Hazardous Material
                $hazardous_material = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'hazardous_material');

                if (!is_array($hazardous_material)) {
                    if ($new_api_enabled) {
                        (isset($package['hazardous_material'])) ? $s_post_data['hazardous_material'] = TRUE : "";
                        (isset($package['hazardous_material'])) ? $s_post_data['hazardous_material'] = 'yes' : "";
                    } else {
                        (isset($package['hazardous_material'])) ? $this->hazardous_status = 'yes' : $this->hazardous_status = '';
                        (isset($package['hazardous_material'])) ? $s_post_data['hazardous_status'] = 'yes' : '';
                    }


                    // FDO
                    $s_post_data['en_fdo_meta_data'] = array_merge($s_post_data['en_fdo_meta_data'], $this->en_package_hazardous($package, $en_fdo_meta_data));
                }

                //Except Ground Transit Restriction
                $exempt_ground_restriction_plan = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'transit_days');
                if (!is_array($exempt_ground_restriction_plan)) {
                    (isset($package['exempt_ground_transit_restriction'])) ? $s_post_data['exempt_ground_transit_restriction'] = 'yes' : '';
                }

                // In-store pickup and local delivery
                $instore_pickup_local_devlivery_action = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'instore_pickup_local_devlivery');

                if (!is_array($instore_pickup_local_devlivery_action)) {
                    $zip_index = $new_api_enabled ? 'speed_ship_receiver_zip_code' : 'receiverZip';
                    $s_post_data = apply_filters('en_wd_standard_plans', $s_post_data, $s_post_data[$zip_index], $this->en_wd_origin_array, $package_plugin);
                }
            }

            $s_post_data = $this->_is_smart_post_enable($s_post_data);

            $post_data[$package['origin']['zip']] = apply_filters("en_woo_addons_carrier_service_quotes_request", $s_post_data, unishippers_en_woo_plugin_unishepper_small);

            $post_data = apply_filters(
                'enit_box_sizes_post_array_filter', $post_data, $package, $package['origin']['zip'], $services_list
            );

            // Compatability with OLD SBS Addon
            $zip_code = (isset($package['origin']['zip'])) ? $package['origin']['zip'] : 0;
            if (isset($post_data[$zip_code]['vertical_rotation'], $post_data[$zip_code]['length']) &&
                count($post_data[$zip_code]['length']) == count($post_data[$zip_code]['vertical_rotation']) &&
                !empty($post_data[$zip_code]['vertical_rotation'])) {
                $post_data[$zip_code]['vertical_rotation'] = array_combine(array_keys($post_data[$zip_code]['length']), $post_data[$zip_code]['vertical_rotation']);
            }
            if (isset($post_data[$zip_code]['shipBinAlone'], $post_data[$zip_code]['length']) &&
                count($post_data[$zip_code]['length']) == count($post_data[$zip_code]['shipBinAlone']) &&
                !empty($post_data[$zip_code]['shipBinAlone'])) {
                $post_data[$zip_code]['shipBinAlone'] = array_combine(array_keys($post_data[$zip_code]['length']), $post_data[$zip_code]['shipBinAlone']);
            }
        }

        // Error Management
        $post_data = $this->applyErrorManagement($post_data);

        // Eniture debug mood
        do_action("eniture_debug_mood", "Unishipper small Features", get_option('eniture_plugin_21'));
        do_action("eniture_debug_mood", "Request (Unishipper small)", $post_data);
        do_action("eniture_debug_mood", "Build Query (Unishipper small)", http_build_query($post_data));
        return $post_data;
    }

    /**
     * @return shipment days of a week
     */
    public function unishippers_small_shipment_week_days()
    {

        $shipment_days_of_week = array();

        if (get_option('all_shipment_days_unishippers_small') == 'yes') {
            return $shipment_days_of_week;
        }

        if (get_option('monday_shipment_day_unishippers_small') == 'yes') {
            $shipment_days_of_week[] = 1;
        }
        if (get_option('tuesday_shipment_day_unishippers_small') == 'yes') {
            $shipment_days_of_week[] = 2;
        }
        if (get_option('wednesday_shipment_day_unishippers_small') == 'yes') {
            $shipment_days_of_week[] = 3;
        }
        if (get_option('thursday_shipment_day_unishippers_small') == 'yes') {
            $shipment_days_of_week[] = 4;
        }
        if (get_option('friday_shipment_day_unishippers_small') == 'yes') {
            $shipment_days_of_week[] = 5;
        }

        return $shipment_days_of_week;
    }

    function _is_smart_post_enable($post_data)
    {
        $hub_id = get_option('hub_id_unishepper_small');
        $hub_post = get_option('unishepper_small_smart_post');

        $valid_weight = (isset($post_data['total_weight']) && ($post_data['total_weight'] <= 70)) ? TRUE : FALSE;
        $total_girth = (isset($post_data['total_girth']) && ($post_data['total_girth'] <= 130)) ? TRUE : FALSE;

        if ($total_girth && $valid_weight && $hub_id > 0 && $hub_post == "yes" && !empty($post_data)) {
            $post_data['smartPOST'] = array(
                'hubId' => $hub_id,
                'indicia' => 'PARCEL_SELECT'
            );
        }

        return $post_data;
    }

    /**
     * URL Rewriting
     * @param $domain
     * @return url
     */
    function unishepper_small_parse_url($domain)
    {
        $domain = trim($domain);
        $parsed = parse_url($domain);
        if (empty($parsed['scheme'])) {
            $domain = 'http://' . ltrim($domain, '/');
        }
        $parse = parse_url($domain);
        $refinded_domain_name = $parse['host'];
        $domain_array = explode('.', $refinded_domain_name);
        if (in_array('www', $domain_array)) {
            $key = array_search('www', $domain_array);
            unset($domain_array[$key]);
            if(phpversion() < 8) {
                $refinded_domain_name = implode($domain_array, '.'); 
            }else {
                $refinded_domain_name = implode('.', $domain_array);
            }
        }
        return $refinded_domain_name;
    }

    /**
     * Get Nearest Address If Multiple Warehouses
     * @param $warehous_list
     * @param $receiverZipCode
     * @return array
     */
    function unishepper_Small_multi_warehouse($warehous_list, $receiverZipCode)
    {
        if (count($warehous_list) == 1) {
            $warehous_list = reset($warehous_list);
            return $this->unishepper_small_origin_array($warehous_list);
        }

        $unishepper_Small_distance_request = new Unishippers_Get_unishepper_small_distance();
        $accessLevel = "MultiDistance";
        $response_json = $unishepper_Small_distance_request->unishepper_small_address($warehous_list, $accessLevel, $this->destinationAddressUnishipperSmall());

        $response_json = json_decode($response_json);
        return $this->unishepper_small_origin_array((isset($response_json->origin_with_min_dist)) ? $response_json->origin_with_min_dist : "");
    }

    /**
     * destinationAddressUnishipperSmall
     * @return array type
     */
    function destinationAddressUnishipperSmall()
    {
        $en_order_accessories = apply_filters('en_order_accessories', []);
        if (isset($en_order_accessories) && !empty($en_order_accessories)) {
            return $en_order_accessories;
        }

        $unishepper_small_woo_obj = new Unishippers_Small_Woo_Update_Changes();
        $freight_zipcode = (strlen(WC()->customer->get_shipping_postcode()) > 0) ? WC()->customer->get_shipping_postcode() : $unishepper_small_woo_obj->unishepper_small_postcode();
        $freight_state = (strlen(WC()->customer->get_shipping_state()) > 0) ? WC()->customer->get_shipping_state() : $unishepper_small_woo_obj->unishepper_small_getState();
        $freight_country = (strlen(WC()->customer->get_shipping_country()) > 0) ? WC()->customer->get_shipping_country() : $unishepper_small_woo_obj->unishepper_small_getCountry();
        $freight_city = (strlen(WC()->customer->get_shipping_city()) > 0) ? WC()->customer->get_shipping_city() : $unishepper_small_woo_obj->unishepper_small_getCity();
        $address = $unishepper_small_woo_obj->unishepper_small_getAddress1();

        return array(
            'city' => $freight_city,
            'state' => $freight_state,
            'zip' => $freight_zipcode,
            'country' => $freight_country,
            'address' => $address,
        );
    }

    /**
     * Create Origin Array
     * @param $origin
     * @return array
     */
    function unishepper_small_origin_array($origin)
    {
//      In-store pickup and local delivery
        if (has_filter("en_wd_origin_array_set")) {
            return apply_filters("en_wd_origin_array_set", $origin);
        }

        $zip = (isset($origin->zip)) ? $origin->zip : "";
        $city = (isset($origin->city)) ? $origin->city : "";
        $state = (isset($origin->state)) ? $origin->state : "";
        $country = (isset($origin->country)) ? $origin->country : "";
        $country = ($country == "CN") ? "CA" : $country;
        $location = (isset($origin->location)) ? $origin->location : "";
        $locationId = (isset($origin->id)) ? $origin->id : "";
        return array(
            'locationId' => $locationId,
            'zip' => $zip,
            'city' => $city,
            'state' => $state,
            'location' => $location,
            'country' => $country,
            'sender_origin' => $location . ", " . $zip . ", " . $city . ", " . $state . ", " . $country,
        );
    }

    /**
     * Get Unishipper Small Web Quotes
     * @param $request_data
     * @return array
     */
    function unishepper_small_get_quotes($request_data, $package_plugin = "")
    {
//      check response from session 
        $currentData = md5(wp_json_encode($request_data));

        $requestFromSession = WC()->session->get('previousRequestData');

        $requestFromSession = ((is_array($requestFromSession)) && (!empty($requestFromSession))) ? $requestFromSession : array();

        if (isset($requestFromSession[$currentData]) && (!empty($requestFromSession[$currentData]))) {
            $requestFromSession = json_decode($requestFromSession[$currentData]);
//          Eniture debug mood

            do_action("eniture_debug_mood", " Unishipper Small Features", get_option('eniture_plugin_21'));
            do_action("eniture_debug_mood", "Quotes session Response (Unishipper Small)", $requestFromSession);
            return $requestFromSession;
        }

        if (is_array($request_data) && count($request_data) > 0) {

            $Unishipper_Small_Curl_Request = new Unishippers_Small_Curl_Request();
            // requestKeySBS
            if (isset($request_data['requestKeySBS']) && strlen($request_data['requestKeySBS']) > 0) {
                $request_data['requestKey'] = $request_data['requestKeySBS'];
            } else {
                $request_data['requestKey'] = (isset($request_data['requestKey'])) ? $request_data['requestKey'] : md5(microtime() . rand());
            }

            $url = $this->getEndPoint();
            $output = $Unishipper_Small_Curl_Request->unishepper_small_get_curl_response($url, $request_data);

//      set response in session
            $response = json_decode($output, TRUE);

            if (isset($response['q'])) {
                if (isset($response['autoResidentialSubscriptionExpired']) &&
                    ($response['autoResidentialSubscriptionExpired'] == 1)) {
                    $flag_api_response = "no";
                    $request_data['residential_detecion_flag'] = $flag_api_response;
                    $currentData = md5(wp_json_encode($request_data));
                }

                $requestFromSession[$currentData] = $output;
                WC()->session->set('previousRequestData', $requestFromSession);
            }

//          Eniture debug mood
            do_action("eniture_debug_mood", " Unishipper Small Features", get_option('eniture_plugin_21'));
            do_action("eniture_debug_mood", "Quotes Response (Unishipper Small)", json_decode($output));

            return json_decode($output);
        }
    }

    public function quote_detail($result)
    {
        return isset($result->RateReplyDetails) ? $result->RateReplyDetails : ((isset($result->q)) ? $result->q : [1]);
    }

    /**
     * Get Shipping Array For Single Shipment
     * @param $result
     * @param $serviceType
     * @return array
     */
    function parse_unishipper_small_output($result, $id_services, $product_detail, $quote_settings)
    {
        // API time out or empty response
        if (isset($result->backupRate) && $result->backupRate == 1) {
            return ['error' => 'backup_rate'];
        }
        
        $en_box_fee = 0;
        $this->hazardous_status = (isset($product_detail['hazardous_status'])) ? $product_detail['hazardous_status'] : FALSE;
        $hazardous_material = isset($this->hazardous_status) && ($this->hazardous_status == 'yes') ? true : false;

        $all_services_array = array();
        $transit_time = "";
        $hazardous_fee = 0;
        $meta_data = array();
        $accessorials = array();
        $EnUnishipperSmallTransitDays = new Unishippers_EnUnishipperSmallTransitDays();

        $WC_unishipper_small = new WC_unishipper_small;

        $en_always_accessorial = [];
        $multiple_accessorials[] = ['S'];

        $this->forcefully_residential_delivery ? $multiple_accessorials[] = ['R'] : '';

        (isset($quote_settings['residential_delivery']) && $quote_settings['residential_delivery'] == 'yes') ? $en_always_accessorial[] = 'R' : '';
        ($hazardous_material) ? $en_always_accessorial[] = 'H' : '';
        $en_auto_residential_status = !in_array('R', $en_always_accessorial) && isset($result->residentialStatus) && $result->residentialStatus == 'r' ? 'r' : '';
        $meta_data['accessorials'] = wp_json_encode($en_always_accessorial);
        $meta_data['sender_origin'] = (isset($product_detail['sender_origin'])) ? $product_detail['sender_origin'] : '';
        $meta_data['product_name'] = (isset($product_detail['product_name'])) ? $product_detail['product_name'] : '';
        $meta_data['plugin_name'] = "unishepper_small";

        // FDO
        $en_fdo_meta_data = (isset($product_detail['en_fdo_meta_data'])) ? $product_detail['en_fdo_meta_data'] : '';
        // FDO
        $en_auto_residential_status == 'r' ? $en_fdo_meta_data['accessorials']['residential'] = true : '';

        $package_bins = (isset($product_detail['package_bins'])) ? $product_detail['package_bins'] : [];
        $en_box_fee_arr = (isset($product_detail['en_box_fee']) && !empty($product_detail['en_box_fee'])) ? $product_detail['en_box_fee'] : [];
        $en_multi_box_qty = (isset($product_detail['en_multi_box_qty']) && !empty($product_detail['en_multi_box_qty'])) ? $product_detail['en_multi_box_qty'] : [];
        $products = (isset($product_detail['products'])) ? $product_detail['products'] : [];

        if (isset($en_box_fee_arr) && is_array($en_box_fee_arr) && !empty($en_box_fee_arr)) {
            foreach ($en_box_fee_arr as $en_box_fee_key => $en_box_fee_value) {
                $en_multi_box_quantity = (isset($en_multi_box_qty[$en_box_fee_key])) ? $en_multi_box_qty[$en_box_fee_key] : 0;
                $en_box_fee += $en_box_fee_value * $en_multi_box_quantity;
            }
        }

        $bin_packaging_filtered = $this->en_bin_packaging_detail($result);
        $bin_packaging_filtered = !empty($bin_packaging_filtered) ? json_decode(wp_json_encode($bin_packaging_filtered), TRUE) : [];

        // Bin Packaging Box Fee|Product Title Start
        $en_box_total_price = 0;
        if (isset($bin_packaging_filtered['bins_packed']) && !empty($bin_packaging_filtered['bins_packed'])) {
            foreach ($bin_packaging_filtered['bins_packed'] as $bins_packed_key => $bins_packed_value) {
                $bin_data = (isset($bins_packed_value['bin_data'])) ? $bins_packed_value['bin_data'] : [];
                $bin_items = (isset($bins_packed_value['items'])) ? $bins_packed_value['items'] : [];
                $bin_id = (isset($bin_data['id'])) ? $bin_data['id'] : '';
                $bin_type = (isset($bin_data['type'])) ? $bin_data['type'] : '';
                $bins_detail = (isset($package_bins[$bin_id])) ? $package_bins[$bin_id] : [];
                $en_box_price = (isset($bins_detail['box_price'])) ? $bins_detail['box_price'] : 0;
                $en_box_total_price += $en_box_price;

                foreach ($bin_items as $bin_items_key => $bin_items_value) {
                    $bin_item_id = (isset($bin_items_value['id'])) ? $bin_items_value['id'] : '';
                    $get_product_name = (isset($products[$bin_item_id])) ? $products[$bin_item_id] : '';
                    if ($bin_type == 'item') {
                        $bin_packaging_filtered['bins_packed'][$bins_packed_key]['bin_data']['product_name'] = $get_product_name;
                    }

                    if (isset($bin_packaging_filtered['bins_packed'][$bins_packed_key]['items'][$bin_items_key])) {
                        $bin_packaging_filtered['bins_packed'][$bins_packed_key]['items'][$bin_items_key]['product_name'] = $get_product_name;
                    }
                }
            }
        }

        $en_box_total_price += $en_box_fee;

        $meta_data['bin_packaging'] = wp_json_encode($bin_packaging_filtered);
        // FDO
        $en_fdo_meta_data['bin_packaging'] = $bin_packaging_filtered;
        $en_fdo_meta_data['bins'] = $package_bins;

        $quote = array();
        $en_count_rates = 0;
        $bin_packaging = array();
        $en_with_residential_delivery = $this->forcefully_always_residential_delivery && $en_auto_residential_status != 'r' ? ' with residential delivery.' : '';

        $quote = [];
        $en_sorting_rates = [];
        $handling_fee = get_option('unishipper_small_hand_fee_mark_up');
        $new_api_enabled = get_option('api_endpoint_unishippers_small') == 'unishippers_small_new_api';
        $this->updateAPISelection($result);

        $no_quotes = false;
        $error = isset($result->severity) && $result->severity == 'ERROR';
        if (!isset($result->q) && !$error) {
            $result = (object)['q' => (object)[1]];
            $no_quotes = true;
        }

        if ((isset($result->q) || isset($result->RateReplyDetails)) || $no_quotes) {
            $quote = $this->quote_detail($result);
            $services = $id_services;
            $service_key_name = key($services);

            foreach ($quote as $service_name => $services_list) {

                if ($new_api_enabled) {
                    $services_list = $this->formatQuoteDetails($services_list);
                }

                if(!(isset($product_detail['exempt_ground_transit_restriction']) && $product_detail['exempt_ground_transit_restriction'] == 'yes')){
                    $services_list = $EnUnishipperSmallTransitDays->unishipper_enable_disable_service_ground($services_list);
                }

                if (isset($services_list->serviceType, $services[$service_key_name][$services_list->serviceType]) || $no_quotes) {
                    $service_type = (isset($services_list->serviceType)) ? $services_list->serviceType : '';
                    $transit_time = (isset($services_list->deliveryDayOfWeek)) ? $services_list->deliveryDayOfWeek : '';

                    $service_title = (isset($services[$service_key_name][$service_type])) ? $services[$service_key_name][$service_type]['name'] : "";
                    $service_level_markup = (isset($services[$service_key_name][$service_type]['markup'])) ? $services[$service_key_name][$service_type]['markup'] : "";

                    $total_charge = (isset($services_list->totalNetCharge, $services_list->totalNetCharge->Amount)) ? $services_list->totalNetCharge->Amount : 0;

                    // Product level markup
                    if(!empty($product_detail['product_level_markup'])){
                        $total_charge = $this->unishippers_small_calculate_service_level_markup($total_charge, $product_detail['product_level_markup']);
                    }
                    
                    // origin level markup
                    if(!empty($product_detail['origin_markup'])){
                        $total_charge = $this->unishippers_small_calculate_service_level_markup($total_charge, $product_detail['origin_markup']);
                    }
                    
                    // adding service level markup
                    if (isset($total_charge) && !empty($total_charge) && isset($service_level_markup) && !empty($service_level_markup)) {
                        $total_charge = $this->unishippers_small_calculate_service_level_markup($total_charge, $service_level_markup);
                    }

                    // adding markup / handling fee
                    $grand_total = strlen($handling_fee) > 0 ? $this->calculate_handeling_fee($handling_fee, $total_charge) : $total_charge;

                    $meta_data['service_type'] = $service_type;
                    $meta_data['service_name'] = $service_name;

                    $hazardous_material_fee = ($hazardous_material) ? $WC_unishipper_small->add_hazardous_material($meta_data['service_type']) : 0;

                    $service_cost = $grand_total > 0 ? (float)$grand_total + (float)$hazardous_material_fee + (float)$en_box_total_price : 0;
                    $surcharges = [];

                    $transit_time = (isset($services_list->deliveryDate)) ? $services_list->deliveryDate : '';
                    $delivery_days = (isset($services_list->totalTransitTimeInDays)) ? $services_list->totalTransitTimeInDays : '';

                    $en_service = array(
                        'id' => 'en_uni_' . $service_type,
                        'service_type' => $service_type . "_" . $service_name,
                        'cost' => $service_cost,
                        'rate' => $service_cost,
                        'transit_time' => $transit_time,
                        'delivery_days' => $delivery_days,
                        'title' => $service_title,
                        'label' => $service_title,
                        'label_as' => $service_title,
                        'service_name' => $service_name,
                        'meta_data' => $meta_data,
                        'surcharges' => $this->en_get_accessorials_prices($surcharges, $en_always_accessorial, $en_auto_residential_status, $service_cost),
                        'plugin_name' => 'unisheppers',
                        'plugin_type' => 'small',
                        'owned_by' => 'eniture'
                    );

                    // Only quote ground service for hazardous materials shipments
                    if ($hazardous_material && $service_type != "SG") {
                        if (isset($quote_settings['hazardous_materials_shipments']) && ($quote_settings['hazardous_materials_shipments'] == "yes")) {
                            unset($en_service);
                        }
                    }

                    if (!empty($en_service) && is_array($en_service)) {
                        foreach ($multiple_accessorials as $multiple_accessorials_key => $accessorial) {
                            $en_fliped_accessorial = array_flip($accessorial);

                            // When auto-rad detected
                            (!$this->forcefully_residential_delivery && $en_auto_residential_status == 'r') ? $accessorial[] = 'R' : '';

                            ($this->forcefully_always_residential_delivery && !in_array('R', $accessorial)) ? $accessorial[] = 'R' : '';

                            $en_extra_charges = array_diff_key((isset($en_service['surcharges']) ? $en_service['surcharges'] : []), $en_fliped_accessorial);

                            $en_accessorial_type = implode('', $accessorial);
                            $en_rates[$en_accessorial_type][$en_count_rates] = $en_service;

                            // Cost of the rates
                            $en_sorting_rates
                            [$en_accessorial_type]
                            [$en_count_rates]['cost'] = // Used for sorting of rates
                            $en_rates
                            [$en_accessorial_type]
                            [$en_count_rates]['cost'] = (isset($en_service['cost']) ? $en_service['cost'] : 0) - array_sum($en_extra_charges);

                            $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['label_sufex'] = wp_json_encode($accessorial);
                            $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['accessorial_charges'] = wp_json_encode($en_service['surcharges']);
                            $en_rates[$en_accessorial_type][$en_count_rates]['label_sufex'] = $accessorial;

                            if (isset($en_rates[$en_accessorial_type][$en_count_rates]['service_name']) && strlen($en_accessorial_type) > 0) {
                                $en_rates[$en_accessorial_type][$en_count_rates]['id'] .= '_' . $en_accessorial_type;
                            } else {
                                $alphabets = 'abcdefghijklmnopqrstuvwxyz';
                                $rand_string = substr(str_shuffle(str_repeat($alphabets, mt_rand(1, 10))), 1, 10);
                                $en_rates[$en_accessorial_type][$en_count_rates]['id'] .= $rand_string;
                            }

                            // FDO
                            $en_fdo_meta_data['rate'] = $en_rates[$en_accessorial_type][$en_count_rates];
                            if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                                unset($en_fdo_meta_data['rate']['meta_data']);
                            }
                            $en_fdo_meta_data['quote_settings'] = $quote_settings;
                            $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;

                            $en_count_rates++;
                        }
                    }
                }
            }
        }

        $en_rates['en_sorting_rates'] = $en_sorting_rates;

        return $en_rates;
    }

    /**
     * Return the array
     * @param object $result
     * @return object
     */
    public function en_bin_packaging_detail($result)
    {
        return isset($result->binPackaging->response) ? $result->binPackaging->response : [];
    }

    /**
     * Get accessorials prices from api response
     * @param array $accessorials
     * @return array
     */
    public function en_get_accessorials_prices($accessorials, $en_always_accessorial, $en_auto_residential_status, $total_price)
    {
        $surcharges = [];
        $fuel_surcharges = 0;
        $mapp_surcharges = [
            'RESIDENTIAL_DELIVERY' => 'R',
        ];

        foreach ($accessorials as $key => $accessorial) {
            if (isset($mapp_surcharges[$key])) {
                $accessorial = (isset($accessorial->Amount->Amount)) ? $accessorial->Amount->Amount : 0;
                in_array($mapp_surcharges[$key], $en_always_accessorial) && !$this->forcefully_residential_delivery ?
                    $accessorial = 0 : '';
                $en_auto_residential_status == 'r' && $mapp_surcharges[$key] == 'R' && !$this->forcefully_residential_delivery ?
                    $accessorial = 0 : '';
                $surcharges[$mapp_surcharges[$key]] = $accessorial;
            }
        }
        return $surcharges;
    }

    /**
     * Get Calculate service level markup
     * @param $total_charge
     * @param $international_markup
     */
    function unishippers_small_calculate_service_level_markup($total_charge, $international_markup)
    {
        $international_markup = !$total_charge > 0 ? 0 : $international_markup;
        $grandTotal = 0;
        if (floatval($international_markup)) {
            $pos = strpos($international_markup, '%');
            if ($pos > 0) {
                $rest = substr($international_markup, $pos);
                $exp = explode($rest, $international_markup);
                $get = $exp[0];
                $percnt = $get / 100 * $total_charge;
                $grandTotal += $total_charge + $percnt;
            } else {
                $grandTotal += floatval($total_charge) +  floatval($international_markup);
            }
        } else {
            $grandTotal += floatval($total_charge);
        }
        return $grandTotal;
    }

    /**
     * Calculate Handling Fee For Each Shipment
     * @param $handeling_fee
     * @param $total
     * @return int
     */
    function calculate_handeling_fee($handeling_fee, $total)
    {
        $handeling_fee = !$total > 0 ? 0 : $handeling_fee;
        $grandTotal = 0;
        if (floatval($handeling_fee)) {
            $pos = strpos($handeling_fee, '%');
            if ($pos > 0) {
                $rest = substr($handeling_fee, $pos);
                $exp = explode($rest, $handeling_fee);
                $get = $exp[0];
                $percnt = $get / 100 * $total;
                $grandTotal += $total + $percnt;
            } else {
                $grandTotal += $total + $handeling_fee;
            }
        } else {
            $grandTotal += $total;
        }
        return $grandTotal;
    }

    /**
     * Unishipper Get Shipment Rated Array
     * @param $locationGroups
     */
    function RatedShipmentDetails($locationGroups)
    {
        $rates_option = get_option('wc_pulish_negotiate_unishepper_small');
        ($rates_option == 'negotiated') ? $searchword = 'PAYOR_ACCOUNT' : $searchword = 'PAYOR_LIST';

        $allLocations = array_filter($locationGroups, function ($var) use ($searchword) {
            return preg_match("/^$searchword/", $var->ShipmentRateDetail->RateType);
        });

        return $allLocations;
    }

    /**
     * Return woocomerce and abf version
     */
    function unishepperSmpkgWcVersionNumber()
    {
        if (!function_exists('get_plugins'))
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');

        $pluginFolder = get_plugins('/' . 'woocommerce');
        $pluginFile = 'woocommerce.php';
        $wwesmpkPluginFolder = get_plugins('/' . 'small-package-quotes-unishippers-edition');
        $wwesmpkgPluginFile = 'small-package-quotes-unishipper-edition.php';
        $wcPlugin = (isset($pluginFolder[$pluginFile]['Version'])) ? $pluginFolder[$pluginFile]['Version'] : "";
        $wwesmpkgPlugin = (isset($wwesmpkPluginFolder[$wwesmpkgPluginFile]['Version'])) ? $wwesmpkPluginFolder[$wwesmpkgPluginFile]['Version'] : "";

        $pluginVersions = array(
            "woocommerce_plugin_version" => $wcPlugin,
            "unishepperSmpkg_plugin_version" => $wwesmpkgPlugin
        );

        return $pluginVersions;
    }

    function getEndPoint()
    {
        return get_option('api_endpoint_unishippers_small') == 'unishippers_small_new_api' ? UNISHIPPERS_NEW_API_DOMAIN_HITTING_URL . '/carriers/wwe-small/speedshipQuotes.php' : UNISHIPPERS_DOMAIN_HITTING_URL . '/index.php';
    }

    function formatQuoteDetails($quote)
    {
        if (!empty($quote->timeInTransit)) {
            // Saturday sersvice check
            $isSaturdayService = isset($quote->timeInTransit->isSaturdayAvailable) && $quote->timeInTransit->isSaturdayAvailable;
            $quote->serviceType = !empty($quote->timeInTransit->upsServiceCode) ? $this->getServiceCode($quote->timeInTransit->upsServiceCode, $isSaturdayService) : '';
            $quote->TransitTimeInDays = !empty($quote->timeInTransit->transitDays) ? $quote->timeInTransit->transitDays : '';
            $quote->CalenderDaysInTransit = !empty($quote->timeInTransit->CalenderDaysInTransit) ? $quote->timeInTransit->CalenderDaysInTransit : '';
            $quote->deliveryDate = !empty($quote->timeInTransit->estimatedDeliveryDate) ? $quote->timeInTransit->estimatedDeliveryDate : '';
            $quote->totalTransitTimeInDays = !empty($quote->timeInTransit->totalTransitTimeInDays) ? $quote->timeInTransit->totalTransitTimeInDays : '';
            $quote->totalNetCharge = (object) ['Amount' => !empty($quote->totalOfferPrice->value) ? $quote->totalOfferPrice->value : ''];
        }

        return $quote;
    }

    function getServiceCode($serviceCode, $isSaturday = false)
    {
        $servicesCodes = [
            'GND' => 'SG',      // UPS Ground
            '3DS' => 'SC3',     // UPS 3 Day Select
            '2DA' => $isSaturday ? 'SSC' : 'SC',      // UPS 2nd Day Air
            '2DM' => 'SC25',    // UPS 2nd Day Air Early / UPS 2nd Day Air A.M
            '1DP' => 'ND4',     // UPS Next Day Air Saver 
            '1DA' => $isSaturday ? 'SND' : 'ND',      // UPS Next Day Air
            '1DM' => $isSaturday ? 'SND5' : 'ND5',     // UPS Next Day Air Early / UPS Next Day Air Early A.M,
            '01'  => 'ZZ1',     // UPS Worldwide Express
            '05'  => 'ZZ2',     // UPS Worldwide Expedited
            '28'  => 'ZZ90',    // UPS Worldwide Saver
            '03'  => 'ZZ11'     // UPS Standard (Canada)
        ];

        return isset($servicesCodes[$serviceCode]) ? $servicesCodes[$serviceCode] : $serviceCode;
    }

    function updateAPISelection($result)
    {
        // New API to Old API migration
        $newAPICredentials = isset($result->newAPICredentials) ? $result->newAPICredentials : [];

        if (!empty($newAPICredentials) && isset($newAPICredentials->client_id) && isset($newAPICredentials->client_secret)) {
            $username = get_option('unishepper_username');
            $password = get_option('unishepper_password');

            // Update customer's API selection and creds info
            update_option('api_endpoint_unishippers_small', 'unishippers_small_new_api');
            update_option('unishippers_small_client_id', $newAPICredentials->client_id);
            update_option('unishippers_small_client_secret', $newAPICredentials->client_secret);
            update_option('unishippers_small_new_api_username', $username);
            update_option('unishippers_small_new_api_password', $password);
        }
        
        // Old API to New API migration
        $oldAPICredentials = isset($result->oldAPICredentials) ? $result->oldAPICredentials : [];
        if (!empty($oldAPICredentials) && isset($oldAPICredentials->account_number)) {
            update_option('api_endpoint_unishippers_small', 'unishippers_small_old_api');
        }
    }

    function applyErrorManagement($quotes_request)
    {
        if (empty($quotes_request)) return $quotes_request;
        
        // error management will be applied only for more than 1 product
        $products_count = 0;
        foreach ($quotes_request as $qr_value) {
            if (!empty($qr_value['products']) && is_array($qr_value['products'])) {
                $products_count = count($qr_value['products']);
                if ($products_count > 1) break;
            }
        }

        $error_option = get_option('error_management_settings_unishepper_small_packages');
        $error_option = !empty($error_option) ? $error_option : 'quote_shipping';
        $dont_quote_shipping = false;
        $items_ids = [];

        // Legacy API
        if (get_option('api_endpoint_unishippers_small') == 'unishippers_small_old_api') {
            foreach ($quotes_request as $loc_id => $pckg) {
                if (empty($pckg['commdityDetails'])) continue;

                foreach ($pckg['commdityDetails'] as $key => $product) {    
                    if (empty($product['lineItemWeight'])) {
                        if ($error_option == 'dont_quote_shipping') {
                            $dont_quote_shipping = true;
                            break;
                        } else {
                            unset($quotes_request[$loc_id]['commdityDetails'][$key]);
                            $items_ids[] = $key;
                        }
                    }
                }

                $quotes_request[$loc_id]['error_management'] = $error_option;
                // error management will be applied for all products in case of dont quote shipping option
                if ($dont_quote_shipping) $quotes_request[$loc_id]['commdityDetails'] = [];
            }
        } else {
            $dimsArr = ['product_width_array', 'product_height_array', 'product_length_array', 'speed_ship_product_weight'];
            $otherArr = array_merge($dimsArr, ['speed_ship_product_price_array', 'speed_ship_title_array', 'speed_ship_quantity_array', 'product_name', 'products', 'nesting_percentage','nesting_dimension', 'nested_max_limit', 'nested_stack_property', 'ship_item_alone', 'product_tags_array']);
            
            foreach ($quotes_request as $org_key => $value) {
                foreach ($value['product_width_array'] as $k => $v) {
                    if (empty($value['speed_ship_product_weight'][$k])) {
                        if ($error_option == 'dont_quote_shipping') {
                            $dont_quote_shipping = true;
                            break;
                        } else {
                            foreach ($otherArr as $other_value) {
                                unset($quotes_request[$org_key][$other_value][$k]);
                                $quotes_request[$org_key]['error_management'] = $error_option;
                                $items_ids[] = $k;
                            }
                        }
                    }
                }

                if ($dont_quote_shipping) break;
            }

            // error management will be applied for all products in case of dont quote shipping option
            if ($dont_quote_shipping) {
                foreach ($quotes_request as $key => $value) {
                    foreach ($otherArr as $k => $v) {
                        $quotes_request[$key][$v] = [];
                    }

                    $quotes_request[$key]['error_management'] = $error_option;
                }
            }
        }

        // set error property for items in fdo meta-data array to hide them on order widget details
        if (!empty($items_ids) && !$dont_quote_shipping) {
            foreach ($quotes_request as $loc_id => $pckg) {
                if (empty($pckg['en_fdo_meta_data'])) continue;

                foreach ($pckg['en_fdo_meta_data']['items'] as $key => $item) {
                    if (!isset($item['id'])) continue;

                    if (in_array($key, $items_ids)) {
                        $quotes_request[$loc_id]['en_fdo_meta_data']['items'][$key]['error_management'] = true;
                    }
                }
            }
        }

        return $quotes_request;
    }
}