<?php

/**
 * Unishipper Small Grouping
 * @package     Unishipper Small Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get Shipping Package Class
 */
class Unishippers_Small_Shipping_Get_Package
{

    /** $hasLTLShipment */
    public $hasLTLShipment = 0;

    /** $errors */
    public $errors = [];
    public $order_details;
    public $products_details = [];
    // Images for FDO
    public $en_fdo_image_urls = [];
    // Micro Warehouse
    public $products = [];
    public $dropship_location_array = [];
    public $warehouse_products = [];
    public $destination_Address_uni_spq;
    public $origin = [];

    /**
     * Grouping For Shipments
     * @param $package
     * @param $unishepper_small_res_inst
     * @param $dest_zipcode
     * @return string
     */
    function group_unishepper_small_shipment($package, $unishepper_small_res_inst, $dest_zipcode)
    {
        $unishepper_small_package = [];
        if (empty($dest_zipcode)) {
            return [];
        }

        if (isset($package['sPackage']) && !empty($package['sPackage'])) {
            return $package['sPackage'];
        }
        $weight_threshold = get_option('en_weight_threshold_lfq');
        $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;

        $pStatus = (isset($package['itemType']) && !empty($package['itemType'])) ? $package['itemType'] : "";
        $unishepper_small_woo_obj = new Unishippers_Small_Woo_Update_Changes();
        $sm_zipcode = $unishepper_small_woo_obj->unishepper_small_postcode();
        if (isset($package['contents'])) {

            $wc_settings_wwe_ignore_items = get_option("en_ignore_items_through_freight_classification");
            $en_get_current_classes = strlen($wc_settings_wwe_ignore_items) > 0 ? trim(strtolower($wc_settings_wwe_ignore_items)) : '';
            $en_get_current_classes_arr = strlen($en_get_current_classes) > 0 ? array_map('trim', explode(',', $en_get_current_classes)) : [];

            // Micro Warehouse
            $smallPluginExist = 0;
            $unishepper_small_package = $items = $items_shipment = [];
            $uni_get_shipping_quotes = new Unishippers_Get_Shipping_Quotes();
            $this->destination_Address_uni_spq = $uni_get_shipping_quotes->destinationAddressUnishipperSmall();

            $flat_rate_shipping_addon = apply_filters('en_add_flat_rate_shipping_addon', false);

            $pack = $package['contents'];
            foreach ($pack as $item_id => $values) {
                $_product = $values['data'];

                // Images for FDO
                $this->en_fdo_image_urls($values, $_product);

                // Start compatability with Woocommerce Product Add-ons
                $product_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
                $parent_id = $product_id;
                $variation_id = (isset($values['variation_id'])) ? $values['variation_id'] : 0;
                if(isset($values['variation_id']) && $values['variation_id'] > 0){
                    $variation = wc_get_product($values['variation_id']);
                    $parent_id = $variation->get_parent_id();   
                }
                $product_unique_key = $product_id . $variation_id;
                if (in_array($product_unique_key, $this->products_details)) {
                    continue;
                }

                // Flat rate pricing
                $en_flat_rate_price = $this->en_get_flat_rate_price($values, $_product);
                if ($flat_rate_shipping_addon && isset($en_flat_rate_price) && strlen($en_flat_rate_price) > 0) {
                    continue;
                }

                //get product shipping class
                $en_ship_class = strtolower($values['data']->get_shipping_class());
                if (in_array($en_ship_class, $en_get_current_classes_arr)) {
                    continue;
                }

                // Shippable handling units
                $values = apply_filters('en_shippable_handling_units_request', $values, $values, $_product);
                $shippable = [];
                if (isset($values['shippable']) && !empty($values['shippable'])) {
                    $shippable = $values['shippable'];
                }

                $this->products_details[] = $product_unique_key;
                // End compatability with Woocommerce Product Add-ons

                $locationId = 0;

                $dimension_unit = get_option('woocommerce_dimension_unit');
                // convert product dimensions in feet, centimeter, miles, kilometer into Inches
                if ($dimension_unit == 'ft' || $dimension_unit == 'cm' || $dimension_unit == 'mi' || $dimension_unit == 'km') {

                    $dimensions = $this->dimensions_conversion($_product);
                    $height = $dimensions['height'];
                    $width = $dimensions['width'];
                    $length = $dimensions['length'];
                } else {
                    $p_height = str_replace( array( "'",'"' ),'',$_product->get_height());
                    $p_width = str_replace( array( "'",'"' ),'',$_product->get_width());
                    $p_length = str_replace( array( "'",'"' ),'',$_product->get_length());
                    $p_height = is_numeric($p_height) ? $p_height : 0;
                    $p_width = is_numeric($p_width) ? $p_width : 0;
                    $p_length = is_numeric($p_length) ? $p_length : 0;
                    $height = wc_get_dimension($p_height, 'in');
                    $width = wc_get_dimension($p_width, 'in');
                    $length = wc_get_dimension($p_length, 'in');
                }

                $height = (strlen($height) > 0) ? $height : "0";
                $width = (strlen($width) > 0) ? $width : "0";
                $length = (strlen($length) > 0) ? $length : "0";

                $product_weight = wc_get_weight($_product->get_weight(), 'lbs');
                $product_weight = (strlen($product_weight) > 0) ? $product_weight : "0";

                $dimenssions = $length * $width * $height;
                $exceedWeight = get_option('en_plugins_return_LTL_quotes');
                $weight = ($product_weight * $values['quantity']);

                $freight_enable_class = $this->unishepper_small_check_freight_class($_product);
                $locations_list = $this->unishepper_small_origin_address($values, $_product);
                $origin_address = $unishepper_small_res_inst->unishepper_Small_multi_warehouse($locations_list, $sm_zipcode);
                $ptype = $this->unishepper_small_check_product_type($freight_enable_class, $exceedWeight, $product_weight);
                $insurance = $this->en_insurance_checked($values, $_product);
                $product_level_markup = $this->unishippers_small_get_product_level_markup($_product, $values['variation_id'], $product_id, $values['quantity']);
                $locationId = (isset($origin_address['id'])) ? $origin_address['id'] : $origin_address['locationId'];
                $locationZip = (isset($origin_address['zip'])) ? $origin_address['zip'] : '';
                $locationId = $locationZip;

                // Set product signature value
                $product_signature = get_post_meta($product_id, '_signature_required', true);
                if ($variation_id > 0) {
                    $variable_product_signature = get_post_meta($variation_id, '_signature_required', true);
                    if (!empty($variable_product_signature)) {
                        $product_signature = $variable_product_signature;
                    }
                }

                // Micro Warehouse
                (isset($values['variation_id']) && $values['variation_id'] > 0) ? $post_id = $values['variation_id'] : $post_id = $_product->get_id();
                $this->products[] = $post_id;

                if (isset($unishepper_small_package[$locationId]['is_shipment']) && $unishepper_small_package[$locationId]['is_shipment'] == 'ltl') {
                    $unishepper_small_package[$locationId]['is_shipment'] = 'ltl';
                } else {
                    $unishepper_small_package[$locationId]['is_shipment'] = $ptype;
                }

                // Nesting Material
                $nestedPercentage = 0;
                $nestedDimension = "";
                $nestedItems = "";
                $StakingProperty = "";

                if (!empty($origin_address) && $product_weight <= $weight_threshold) {

                    // Nested Material
                    $nested_material = $this->en_nested_material($values, $_product);
                    if ($nested_material == "yes") {
                        $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
                        $nestedPercentage = get_post_meta($post_id, '_nestedPercentage', true);
                        $nestedDimension = get_post_meta($post_id, '_nestedDimension', true);
                        $nestedItems = get_post_meta($post_id, '_maxNestedItems', true);
                        $StakingProperty = get_post_meta($post_id, '_nestedStakingProperty', true);
                    }

                    $unishepper_small_package[$locationId]['origin'] = $origin_address;
                    $unishepper_small_package[$locationId]['origin']['ptype'] = $ptype;
                    if (!$_product->is_virtual()) {
                        $_product = $values['data'];

                        // Hazardous Material
                        $hazardous_material = $this->en_hazardous_material($values, $_product);
                        $hm_plan = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'hazardous_material');
                        $hm_status = (!is_array($hm_plan) && $hazardous_material == 'yes') ? TRUE : FALSE;

                        $product_title = str_replace(array("'", '"'), '', $_product->get_title());

                        // Shippable handling units
                        $ship_item_alone = '0';
                        extract($shippable);

                        $en_items = array(
                            'productId' => $parent_id,
                            'productName' => str_replace(array("'", '"'), '', $_product->get_name()),
                            'productQty' => $values['quantity'],
                            'product_name' => $values['quantity'] . " x " . $product_title,
                            'products' => $product_title,
                            'productPrice' => $insurance == "yes" ? $_product->get_price() : 0,
                            'productWeight' => $product_weight,
                            'productLength' => $length,
                            'productWidth' => $width,
                            'productHeight' => $height,
                            'ptype' => $ptype,
                            'hazardousMaterial' => $hm_status,
                            'hazmat' => $hm_status,
                            'productType' => ($_product->get_type() == 'variation') ? 'variant' : 'simple',
                            'productSku' => $_product->get_sku(),
                            'actualProductPrice' => $_product->get_price(),
                            'attributes' => $_product->get_attributes(),
                            'variantId' => ($_product->get_type() == 'variation') ? $_product->get_id() : '',
                            // Nested Material
                            'nestedMaterial' => $nested_material,
                            'nestedPercentage' => $nestedPercentage,
                            'nestedDimension' => $nestedDimension,
                            'nestedItems' => $nestedItems,
                            'stakingProperty' => $StakingProperty,

                            // Shippable handling units
                            'ship_item_alone' => $ship_item_alone,
                            'markup' => $product_level_markup,
                            'product_signature' => $product_signature,
                            'product_insurance' => $insurance == 'yes' ? 1 : 0
                        );

                        // Hook for flexibility adding to package
                        $en_items = apply_filters('en_group_package', $en_items, $values, $_product);

                        // Micro Warehouse
                        $items[$post_id] = $en_items;

                        $unishepper_small_package[$locationId]['items'][] = $en_items;

                        // Hazardous Material
                        if ($hazardous_material == "yes" && !isset($unishepper_small_package[$locationId]['hazardous_material'])) {
                            $unishepper_small_package[$locationId]['hazardous_material'] = TRUE;
                        }

                        // Except Ground Transit
                        $exempt_ground_transit_restriction = $this->exempt_ground_transit_restriction($values, $_product);
                        if($exempt_ground_transit_restriction == 'yes' && !isset($unishepper_small_package[$locationId]['exempt_ground_transit_restriction'])){
                            $unishepper_small_package[$locationId]['exempt_ground_transit_restriction'] = 1;
                        }

                        // Product tags
                        $product_tags = get_the_terms($product_id, 'product_tag');
                        $product_tags = empty($product_tags) ? get_the_terms($parent_id, 'product_tag') : $product_tags;
                        if (!empty($product_tags)) {
                            $product_tag_names = array_map(function($tag) { return $tag->term_id; }, $product_tags);

                            if (isset($unishepper_small_package[$locationId]['product_tags'])) {
                                $unishepper_small_package[$locationId]['product_tags'] = array_merge($unishepper_small_package[$locationId]['product_tags'], $product_tag_names);
                            } else {
                                $unishepper_small_package[$locationId]['product_tags'] = $product_tag_names;
                            }
                        } else {
                            $unishepper_small_package[$locationId]['product_tags'] = [];
                        }

                        // Product quantity
                        if (isset($unishepper_small_package[$locationId]['product_quantities'])) {
                            $unishepper_small_package[$locationId]['product_quantities'] += floatval($values['quantity']);
                        } else {
                            $unishepper_small_package[$locationId]['product_quantities'] = floatval($values['quantity']);
                        }

                        // Product price
                        if (isset($unishepper_small_package[$locationId]['product_prices'])) {
                            $unishepper_small_package[$locationId]['product_prices'] += (floatval($_product->get_price()) * floatval($values['quantity']));
                        } else {
                            $unishepper_small_package[$locationId]['product_prices'] = (floatval($_product->get_price()) * floatval($values['quantity']));
                        }
                    }
                }

                // check if LTL enable
                $ltl_enable = $this->unishippers_small_enable_shipping_class($_product);

                // Micro Warehouse
                $items_shipment[$post_id] = $ltl_enable;
                $unishepper_small_package[$locationId]['shipment_weight'] = isset($unishepper_small_package[$locationId]['shipment_weight']) ? $unishepper_small_package[$locationId]['shipment_weight'] + $weight : $weight;

                if ($pStatus == '' && $ptype == 'ltl') {
                    return $unishepper_small_package = [];
                }
                if ($dimenssions == 0 && $product_weight == 0) {
                    $unishepper_small_package[$locationId]['no_parameter'] = 'NOPARAM';
                }
            }

            $smallPluginExist = 0;
            $calledMethod = [];
            $eniturePluigns = json_decode(get_option('EN_Plugins'));
            if (!empty($eniturePluigns)) {
                foreach ($eniturePluigns as $enIndex => $enPlugin) {

                    $freightSmallClassName = 'WC_' . $enPlugin;

                    if (!in_array($freightSmallClassName, $calledMethod)) {

                        if (class_exists($freightSmallClassName)) {
                            $smallPluginExist = 1;
                        }

                        $calledMethod[] = $freightSmallClassName;
                    }
                }
            }

            // Micro Warehouse
            $eniureLicenceKey = get_option('unishepper_small_licence_key');
            $unishepper_small_package = apply_filters('en_micro_warehouse', $unishepper_small_package, $this->products, $this->dropship_location_array, $this->destination_Address_uni_spq, $this->origin, $smallPluginExist, $items, $items_shipment, $this->warehouse_products, $eniureLicenceKey, 'small');
            return $unishepper_small_package;
        }
    }

    /**
     * Set images urls | Images for FDO
     * @param array type $en_fdo_image_urls
     * @return array type
     */
    public function en_fdo_image_urls_merge($en_fdo_image_urls)
    {
        return array_merge($this->en_fdo_image_urls, $en_fdo_image_urls);
    }

    /**
     * Get images urls | Images for FDO
     * @param array type $values
     * @param array type $_product
     * @return array type
     */
    public function en_fdo_image_urls($values, $_product)
    {
        $product_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
        $gallery_image_ids = $_product->get_gallery_image_ids();
        foreach ($gallery_image_ids as $key => $image_id) {
            $gallery_image_ids[$key] = $image_id > 0 ? wp_get_attachment_url($image_id) : '';
        }

        $image_id = $_product->get_image_id();
        $this->en_fdo_image_urls[$product_id] = [
            'product_id' => $product_id,
            'image_id' => $image_id > 0 ? wp_get_attachment_url($image_id) : '',
            'gallery_image_ids' => $gallery_image_ids
        ];

        add_filter('en_fdo_image_urls_merge', [$this, 'en_fdo_image_urls_merge'], 10, 1);
    }

    /**
     * Hazardous Material in Product Detail page
     * @param array $values
     * @param array $_product
     * @return string
     */
    function en_hazardous_material($values, $_product)
    {
        $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
        return get_post_meta($post_id, '_hazardousmaterials', true);
    }

    /**
     *
     * @param array $values
     * @param array $_product
     * @return string
     */
    function en_insurance_checked($values, $_product)
    {
        $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
        return get_post_meta($post_id, '_en_insurance_fee', true);
    }

    /**
     * Get Enabled Shipping Class Of Product
     * @param $_product
     */
    function unishepper_small_check_freight_class($_product)
    {
        if ($_product->get_type() == 'variation') {

            $ship_class_id = $_product->get_shipping_class_id();

            if ($ship_class_id == 0) {
                $parent_data = $_product->get_parent_data();
                $get_parent_term = get_term_by('id', $parent_data['shipping_class_id'], 'product_shipping_class');
                $freight_enable_class = (isset($get_parent_term->slug)) ? $get_parent_term->slug : "";
            } else {
                $freight_enable_class = $_product->get_shipping_class();
            }
        } else {
            $freight_enable_class = $_product->get_shipping_class();
        }

        return $freight_enable_class;
    }

    /**
     * Check Product Type
     * @param $freight_enable_class
     * @param $exceedWeight
     * @param $weight
     * @return string
     */
    function unishepper_small_check_product_type($freight_enable_class, $exceedWeight, $weight)
    {
        $weight_threshold = get_option('en_weight_threshold_lfq');
        $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;
        if ($freight_enable_class == 'ltl_freight') {
            $ptype = 'ltl';
        } elseif ($exceedWeight == 'yes' && $weight > $weight_threshold) {
            $ptype = 'ltl';
        } else {
            $ptype = 'small';
        }

        return $ptype;
    }

    /**
     * Get Warehouses/Dropship List
     * @param array $values
     * @param array $_product
     * @return array
     * @global object $wpdb
     */
    function unishepper_small_origin_address($values, $_product)
    {

        global $wpdb;

        //      UPDATE QUERY In-store pick up                           
        $en_wd_update_query_string = apply_filters("en_wd_update_query_string", "");

        (isset($values['variation_id']) && $values['variation_id'] > 0) ? $post_id = $values['variation_id'] : $post_id = $_product->get_id();
        $enable_dropship = get_post_meta($post_id, '_enable_dropship', true);
        if ($enable_dropship == 'yes') {
            $get_loc = get_post_meta($post_id, '_dropship_location', true);
            if ($get_loc == '') {
                // Micro Warehouse
                $this->warehouse_products[] = $post_id;
                return array('error' => 'wwe small dp location not found!');
            }

            //          Multi Dropship
            $multi_dropship = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'multi_dropship');

            if (is_array($multi_dropship)) {
                $locations_list = $wpdb->get_results(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE location = 'dropship' LIMIT 1"
                );
            } else {
                $get_loc = ($get_loc !== '') ? maybe_unserialize($get_loc) : $get_loc;
                $get_loc = is_array($get_loc) ? implode(" ', '", $get_loc) : $get_loc;
                $locations_list = $wpdb->get_results(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE id IN ('" . $get_loc . "')"
                );
            }

            // Micro Warehouse
            $this->multiple_dropship_of_prod($locations_list, $post_id);
            $eniture_debug_name = "Dropships";
        } else {

            //          Multi Warehouse
            $multi_warehouse = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'multi_warehouse');
            if (is_array($multi_warehouse)) {
                $locations_list = $wpdb->get_results(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE location = 'warehouse' LIMIT 1"
                );
            } else {
                $locations_list = $wpdb->get_results(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE location = 'warehouse'"
                );
            }

            // Micro Warehouse
            $this->warehouse_products[] = $post_id;
            $eniture_debug_name = "Warehouses";
        }

        do_action("eniture_debug_mood", "Quotes $eniture_debug_name (s)", $locations_list);
        return $locations_list;
    }

    // Micro Warehouse
    public function multiple_dropship_of_prod($locations_list, $post_id)
    {
        $post_id = (string)$post_id;

        foreach ($locations_list as $key => $value) {
            $dropship_data = $this->address_array($value);

            $this->origin["D" . $dropship_data['zip']] = $dropship_data;
            if (!isset($this->dropship_location_array["D" . $dropship_data['zip']]) || !in_array($post_id, $this->dropship_location_array["D" . $dropship_data['zip']])) {
                $this->dropship_location_array["D" . $dropship_data['zip']][] = $post_id;
            }
        }

    }

    // Micro Warehouse
    public function address_array($value)
    {
        $dropship_data = [];

        $dropship_data['locationId'] = (isset($value->id)) ? $value->id : "";
        $dropship_data['zip'] = (isset($value->zip)) ? $value->zip : "";
        $dropship_data['city'] = (isset($value->city)) ? $value->city : "";
        $dropship_data['state'] = (isset($value->state)) ? $value->state : "";
        // Origin terminal address
        $dropship_data['address'] = (isset($value->address)) ? $value->address : "";
        // Terminal phone number
        $dropship_data['phone_instore'] = (isset($value->phone_instore)) ? $value->phone_instore : "";
        $dropship_data['location'] = (isset($value->location)) ? $value->location : "";
        $dropship_data['country'] = (isset($value->country)) ? $value->country : "";
        $dropship_data['enable_store_pickup'] = (isset($value->enable_store_pickup)) ? $value->enable_store_pickup : "";
        $dropship_data['fee_local_delivery'] = (isset($value->fee_local_delivery)) ? $value->fee_local_delivery : "";
        $dropship_data['suppress_local_delivery'] = (isset($value->suppress_local_delivery)) ? $value->suppress_local_delivery : "";
        $dropship_data['miles_store_pickup'] = (isset($value->miles_store_pickup)) ? $value->miles_store_pickup : "";
        $dropship_data['match_postal_store_pickup'] = (isset($value->match_postal_store_pickup)) ? $value->match_postal_store_pickup : "";
        $dropship_data['checkout_desc_store_pickup'] = (isset($value->checkout_desc_store_pickup)) ? $value->checkout_desc_store_pickup : "";
        $dropship_data['enable_local_delivery'] = (isset($value->enable_local_delivery)) ? $value->enable_local_delivery : "";
        $dropship_data['miles_local_delivery'] = (isset($value->miles_local_delivery)) ? $value->miles_local_delivery : "";
        $dropship_data['match_postal_local_delivery'] = (isset($value->match_postal_local_delivery)) ? $value->match_postal_local_delivery : "";
        $dropship_data['checkout_desc_local_delivery'] = (isset($value->checkout_desc_local_delivery)) ? $value->checkout_desc_local_delivery : "";

        $dropship_data['sender_origin'] = $dropship_data['location'] . ": " . $dropship_data['city'] . ", " . $dropship_data['state'] . " " . $dropship_data['zip'];

        return $dropship_data;
    }

    /**
     * Check Product Enable Against LTL Freight
     * @param $_product
     * @return string
     */
    function unishippers_small_enable_shipping_class($_product)
    {
        if ($_product->get_type() == 'variation') {
            $ship_class_id = $_product->get_shipping_class_id();

            if ($ship_class_id == 0) {
                $parent_data = $_product->get_parent_data();
                $get_parent_term = get_term_by('id', $parent_data['shipping_class_id'], 'product_shipping_class');
                $get_shipping_result = (isset($get_parent_term->slug)) ? $get_parent_term->slug : '';
            } else {
                $get_shipping_result = $_product->get_shipping_class();
            }

            $ltl_enable = ($get_shipping_result && $get_shipping_result == 'ltl_freight') ? true : false;
        } else {
            $get_shipping_result = $_product->get_shipping_class();
            $ltl_enable = ($get_shipping_result == 'ltl_freight') ? true : false;
        }

        return $ltl_enable;
    }

    /**
     * @param type object
     * @return type array
     */
    function dimensions_conversion($_product)
    {

        $dimension_unit = get_option('woocommerce_dimension_unit');
        $dimensions = [];
        $height = is_numeric($_product->get_height()) ? $_product->get_height() : 0;
        $width = is_numeric($_product->get_width()) ? $_product->get_width() : 0;
        $length = is_numeric($_product->get_length()) ? $_product->get_length() : 0;
        switch ($dimension_unit) {

            case 'ft':
                $dimensions['height'] = round($height * 12, 2);
                $dimensions['width'] = round($width * 12, 2);
                $dimensions['length'] = round($length * 12, 2);
                break;

            case 'cm':
                $dimensions['height'] = round($height * 0.3937007874, 2);
                $dimensions['width'] = round($width * 0.3937007874, 2);
                $dimensions['length'] = round($length * 0.3937007874, 2);
                break;

            case 'mi':
                $dimensions['height'] = round($height * 63360, 2);
                $dimensions['width'] = round($width * 63360, 2);
                $dimensions['length'] = round($length * 63360, 2);
                break;

            case 'km':
                $dimensions['height'] = round($height * 39370.1, 2);
                $dimensions['width'] = round($width * 39370.1, 2);
                $dimensions['length'] = round($length * 39370.1, 2);
                break;
        }

        return $dimensions;
    }

    /**
     * Check except transit time restriction
     * @param array $values
     * @param array $_product
     * @return string
     */
    function exempt_ground_transit_restriction($values, $_product)
    {
        $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
        return get_post_meta($post_id, '_en_exempt_ground_transit_restriction', true);
    }

    /**
     * Returns product level markup
     */
    function unishippers_small_get_product_level_markup($_product, $variation_id, $product_id, $quantity)
    {
        $product_level_markup = 0;
        if ($_product->get_type() == 'variation') {
            $product_level_markup = get_post_meta($variation_id, '_en_product_markup_variation', true);
            if(empty($product_level_markup) || $product_level_markup == 'get_parent'){
                $product_level_markup = get_post_meta($_product->get_id(), '_en_product_markup', true);
            }
        } else {
            $product_level_markup = get_post_meta($_product->get_id(), '_en_product_markup', true);
        }

        if(empty($product_level_markup)) {
            $product_level_markup = get_post_meta($product_id, '_en_product_markup', true);
        }

        if(!empty($product_level_markup) && strpos($product_level_markup, '%') === false 
        && is_numeric($product_level_markup) && is_numeric($quantity))
        {
            $product_level_markup *= $quantity;
            
        }else if(!empty($product_level_markup) && strpos($product_level_markup, '%') > 0 && is_numeric($quantity)){
            $position = strpos($product_level_markup, '%');
            $first_str = substr($product_level_markup, $position);
            $arr = explode($first_str, $product_level_markup);
            $percentage_value = $arr[0];
            $product_price = $_product->get_price();
            if(!empty($product_price)){
                $product_level_markup = $percentage_value / 100 * ($product_price * $quantity);
            }else{
                $product_level_markup = 0;
            }
        }

        return $product_level_markup;
    }

    /**
     * Returns flat rate price and quantity
     */
    function en_get_flat_rate_price($values, $_product)
    {
        if ($_product->get_type() == 'variation') {
            $flat_rate_price = get_post_meta($values['variation_id'], 'en_flat_rate_price', true);
            if (strlen($flat_rate_price) < 1) {
                $flat_rate_price = get_post_meta($values['product_id'], 'en_flat_rate_price', true);
            }
        } else {
            $flat_rate_price = get_post_meta($_product->get_id(), 'en_flat_rate_price', true);
        }

        return $flat_rate_price;
    }

    /**
     * Nested Material
     * @param array type $values
     * @param array type $_product
     * @return string type
     */
    function en_nested_material($values, $_product)
    {
        $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();

        return get_post_meta($post_id, '_nestedMaterials', true);
    }
    
}
