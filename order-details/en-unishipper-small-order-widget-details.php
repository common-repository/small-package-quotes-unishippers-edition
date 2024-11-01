<?php

/**
 * WWE Small Group Packaging
 *
 * @package     WWE Small Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists("Unishippers_Small_Order_Widget_Details")) {

    class Unishippers_Small_Order_Widget_Details
    {

        /**
         * Handling fee status
         * @var string
         */
        public $handling_fee;

        /**
         * Selected shipping status.
         * @var string/int
         */
        public $ship_status;

        /**
         *  current curreny symbol.
         * @var string
         */
        public $currency_symbol;

        /**
         *  Response of order from our custom table.
         * @var array
         */
        public $result_details;

        /**
         * Order key.
         * @var string
         */
        public $order_key;

        /**
         * Selected shippping title.
         * @var type
         */
        public $shipping_method_title;

        /**
         * Selected shippping ID.
         * @var string
         */
        public $shipping_method_id;

        /**
         * Selected shippping price.
         * @var int/float/string
         */
        public $shipping_method_total;

        /**
         * Set 1 if any eniture service selected.
         * @var string
         */
        public $shipment_status;

        /**
         * Set 1 if any eniture service selected.
         * @var string
         */
        public $hazardous_material;

        /**
         * Multishipment id.
         * @var string
         */
        public $multi_ship_id;

        /**
         * Helper object.
         * @var object
         */
        public $helper_obj;
        public $get_formatted_meta_data = [];
        public $en_shipping_id = '';

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->multi_ship_id = 'multi_unishepper_small';
            $this->helper_obj = new Unishippers_En_Fed_Sml_Helper_Class();
            $this->en_call_hooks();
        }

        /**
         * Call needed hooks.
         */
        public function en_call_hooks()
        {
            /* Woocommerce order action hook */
            add_action(
                'woocommerce_order_actions', array($this, 'en_assign_order_details'), 10
            );
        }

        /**
         * Adding Meta container admin shop_order pages
         * @param $actions
         */
        function en_create_meta_box_order_details()
        {
            $this->en_assign_order_details();
        }

        /**
         * Assign order details.
         */
        function en_assign_order_details($actions)
        {
            global $wpdb;
            $this->shipment_status = 'single';
            $order_id = get_the_ID();
            $order = new WC_Order($order_id);
            $this->order_key = $order->get_order_key();
            $shipping_details = $order->get_items('shipping');

            foreach ($shipping_details as $item_id => $shipping_item_obj) {
                $this->shipping_method_title = $shipping_item_obj->get_method_title();
                $this->shipping_method_id = $shipping_item_obj->get_method_id();
                $this->shipping_method_total = $shipping_item_obj->get_total();
                $this->get_formatted_meta_data = $shipping_item_obj->get_formatted_meta_data();
            }

            $this->result_details = [];
            $enit_order_details_table = $wpdb->prefix . "enit_order_details";
            if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($enit_order_details_table))) == $enit_order_details_table) {
                $this->result_details = $wpdb->get_results(
                    $wpdb->prepare("SELECT * FROM `" . $wpdb->prefix . "enit_order_details` WHERE order_id = %s", $this->order_key),
                    ARRAY_A
                );
            }

            foreach ($this->get_formatted_meta_data as $id => $meta_data) {
                (isset($meta_data->key) && $meta_data->key === 'en_shipping_id') ?
                    $this->en_shipping_id = $meta_data->value : '';
            }

            if ($this->en_shipping_id == 'in-store-pick-up' || $this->en_shipping_id == 'local-delivery') {
                return $actions;
            }

            /* Add metabox if user selected our service */
            if (!empty($this->result_details) && count($this->result_details) > 0) {
                /* Add metabox for 3dbin visual details */
                add_meta_box(
                    'en_additional_order_details', __('Additional Order Details', 'eniture-unishippers-small-quotes'), array($this, 'en_add_meta_box_order_widget'), 'shop_order', 'side', 'low', 'core');
            } elseif (!empty($this->get_formatted_meta_data) && count($this->get_formatted_meta_data) > 0) {
                add_meta_box('en_additional_order_details', __('Additional Order Details', 'eniture-unishippers-small-quotes'), array($this, 'en_unishippers_small_add_meta_box_order_widget'), 'shop_order', 'side', 'low', 'core');
            }

            return $actions;
        }

        /**
         * Add order details in metabox.
         */
        public function en_add_meta_box_order_widget()
        {
            /* In case of single shipment remove index 0 */
            if (count($this->result_details) == 1) {
                $order_details = reset($this->result_details);
            }

            /* In case of multishipment */
            if (count($this->result_details) > 1) {
                $order_details = $this->en_return_multiship_row($this->result_details);
            }

            /* Check multi-shipment or single-shipment */
            if (!is_array(json_decode($order_details['data'], true))) {
                $this->shipment_status = 'multishipment';
                $this->en_multi_shipment_order($order_details, $this->shipment_status, $this->order_key);
            } elseif (is_array(json_decode($order_details['data'], true))) {

                $this->shipment_status = 'single';
                $single_price_details['ship_details'] = array(
                    'title' => $this->shipping_method_title,
                    'id' => $this->shipping_method_id,
                    'rate' => $this->shipping_method_total,
                );
                $this->en_single_shipment_order($order_details, $this->shipment_status, $single_price_details);
            }
        }

        /**
         * Return the multiship row.
         */
        public function en_return_multiship_row($details)
        {
            foreach ($details as $key => $value) {
                $data = json_decode($value['data']);
                if (is_string($data)) {
                    return $value;
                }
            }
            return false;
        }

        /**
         * Single shipment order details.
         */
        function en_single_shipment_order($order_details, $shipment_status, $single_price_details)
        {
            $ship_count = 1;
            $service_details = reset($order_details);
            $this->en_origin_services_details($order_details, $shipment_status, $ship_count, $single_price_details);
        }

        /**
         * Multi shipment order details.
         */
        function en_multi_shipment_order($order_details, $shipment_status, $order_key)
        {
            global $wpdb;
            $cheapest_ids = explode(", ", $order_details['data']);
            $ship_count = 1;
            foreach ($cheapest_ids as $key => $value) {
                $service_id = str_replace('"', "", $value);
                $service_details = $this->en_get_service_details_by_id($service_id, $order_key);
                $this->en_origin_services_details($service_details[0], $shipment_status, $ship_count);
                $ship_count++;
                /* Horizontal line */
                echo "<hr>";
            }
        }

        /**
         * Get service details from id
         */
        function en_get_service_details_by_id($id, $order_key)
        {
            global $wpdb;
            $result_details = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM `" . $wpdb->prefix . "enit_order_details` WHERE `service_id` = %s AND order_id = %s",
                    $id,
                    $order_key
                ),
                ARRAY_A
            );
            return $result_details;
        }

        /**
         * Origin & Services details.
         */
        function en_origin_services_details($order_data, $shipment_status, $ship_count, $single_price_details = array())
        {

            $this->currency_symbol = get_woocommerce_currency_symbol(get_option('woocommerce_currency'));
            $code = $order_data['service_id'];
            $service_order_data = json_decode($order_data['data']);
//             Check handling fee 
            /* In case of single shipment reset the array */
            if ($shipment_status == 'single') {
                $service_order_data = reset($service_order_data);
            }

            echo '<h4 style="text-decoration: underline;margin: 4px 0px 4px 0px;">Shipment ' . esc_attr( $ship_count ) . " > Origin & Services </h4>";
            echo '<ul class="en-list" style="list-style: disc;    list-style-position: inside;">';
            echo '<li>';
            echo esc_attr( ucwords($service_order_data->origin->location) ). ', ';
            echo esc_attr( $service_order_data->origin->city) . ', ';
            echo esc_attr( $service_order_data->origin->state) . ', ';
            echo esc_attr( $service_order_data->origin->zip) . ', ';
            echo esc_attr( $service_order_data->origin->country) . "<br />";
            echo '</li>';
            /* Run in case of multishipment only */
            if ($shipment_status != 'single') {
                if (
                    isset($service_order_data->accessorials->R) &&
                    $service_order_data->accessorials->R == 'R'
                ) {

                    if (isset($service_order_data->cheapest_services->title) && $service_order_data->cheapest_services->title != '') {
                        $resd = 'with residential delivery';
                        $title = $service_order_data->cheapest_services->title . ' ' . $resd . ' : ';
                        $resd = '';
                    } else {
                        $resd = '';
                        $title = '';
                    }
                    /* Run in case of single shipment only */
                    if (isset($service_order_data->cheapest_services->rate)) {
                        echo '<li>';
                        echo esc_attr( $title) . ' ' . esc_attr( $resd) . ' ' . esc_attr( $this->en_format_price($service_order_data->cheapest_services->rate));
                        echo '</li>';
                    } else {
                        echo '<li>';
                        echo esc_attr( $title ) . ' ' . esc_attr( $resd ) . ' ' . esc_attr( $this->currency_symbol) . '0.00';
                        echo '</li>';
                    }
                } else {
                    if (isset($service_order_data->cheapest_services->title) && $service_order_data->cheapest_services->title != '') {
                        $resd = $service_order_data->cheapest_services->title . ' : ';
                    } else {
                        $resd = '';
                    }
                    /* Run in case of single shipment only */
                    if (isset($service_order_data->cheapest_services->rate)) {
                        echo '<li>';
                        echo esc_attr( $resd ) . '  ' . esc_attr( $this->en_format_price($service_order_data->cheapest_services->rate));
                        echo '</li>';
                    } else {

                        echo '<li>';
                        echo esc_attr( $resd) . '  ' . esc_attr( $this->currency_symbol) . '0.00';
                        echo '</li>';
                    }
                }
            } else {
                if (isset($single_price_details['ship_details']['rate'])) {
                    /* Run in case of single shipment only */
                    echo '<li>' . esc_attr( $single_price_details['ship_details']['title']) . ' : ' . esc_attr( $this->en_format_price($single_price_details['ship_details']['rate'])) . '</li>';
                }
            }

            /* Show accessorials */
            $this->en_show_accessorials($service_order_data, $shipment_status);
            echo "</ul>";
            echo "<br />";
            echo '<h4 style="    text-decoration: underline;margin: 4px 0px 4px 0px;">Shipment ' . esc_attr( $ship_count) . " > items </h4>";
            echo '<ul id="product-details-order" class="en-list" style="list-style: disc;    list-style-position: inside;">';
            foreach ($service_order_data->items as $value) {
                /* Check for variations */
                $product_name = wc_get_product($value->productId);
                echo '<li>' . esc_attr( $value->productQty) . ' x ' . esc_attr( $product_name->get_name()) . '</li>';
            }
            echo '</ul>';
            echo "<br /><br />";
        }

        /**
         * Price format.
         * @param int/double/string $dollars
         * @return string
         */
        function en_format_price($dollars)
        {
            return $this->currency_symbol . number_format(sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $dollars)), 2);
        }

        /**
         * Show accessorial.
         */
        public function en_show_accessorials($service_order_data, $shipment_status)
        {
            /* Show accessorials code here */
            /* Hazardous check */
            if (isset($service_order_data->hazardous_material) &&
                $service_order_data->hazardous_material == "yes"
            ) {
                echo '<li>Hazardous Material</li>';
            }
            $residential_del = get_option('unishepper_small_quote_as_residential_delivery');
            /* Residential feature */
            if (
                (isset($residential_del) &&
                    $residential_del == 'yes') || (isset($service_order_data->accessorials->R) &&
                    $service_order_data->accessorials->R == 'R')
            ) {
                echo '<li>Residential Delivery</li>';
            }
        }

        /**
         * Add order details in metabox.
         */
        public function en_unishippers_small_add_meta_box_order_widget()
        {
            $order_details = $this->get_formatted_meta_data;
            $this->en_unishippers_small_origin_services_details($order_details);
        }

        /**
         * Origin & Services details.
         * @param array $order_data
         */
        function en_unishippers_small_origin_services_details($order_data)
        {
            $this->currency_symbol = get_woocommerce_currency_symbol(get_option('woocommerce_currency'));
            $this->count = 0;

            $shipment = 'single';
            foreach ($order_data as $key => $is_meta_data) {
                (isset($is_meta_data->key) && $is_meta_data->key === "min_prices") ? $shipment = 'multiple' : '';
            }

            if ($shipment == 'multiple') {
                $order_data = reset($order_data);
                if (isset($order_data->key) && $order_data->key === "min_prices") {
                    $order_data = json_decode($order_data->value, TRUE);
                    foreach ($order_data as $key => $quote) {

                        if (isset($quote['meta_data']['min_prices'])) {
                            $order_data_spq = json_decode($quote['meta_data']['min_prices'], true);
                            foreach ($order_data_spq as $key_spq => $quote_spq) {
                                $this->en_get_services_details_through_meta_data($quote_spq);
                            }
                        } else {
                            $this->en_get_services_details_through_meta_data($quote);
                        }
                    }
                }
            } else {
                foreach ($order_data as $key => $value) {
                    $this->get_meta_data_from_rate($value);
                }
                $this->count++;
                $this->shipping_method_title .= ": ";
                $this->show_order_widget_detail();
            }
        }

        /**
         * Get Services details from meta data.
         * @param array $order_data
         */
        function en_get_services_details_through_meta_data($quote)
        {
            $this->get_meta_data_for_mutiple_ship($quote);
            $label = (isset($quote['label']) && strlen($quote['label']) > 0) ? $quote['label'] : "Shipping";
            $this->shipping_method_title = $this->filter_from_label_sufex($this->label_sufex, $label) . ": ";
            $this->shipping_method_total = (isset($quote['cost'])) ? $quote['cost'] : 0;
            $this->count++;
            $this->show_order_widget_detail();
        }

        /**
         * Get data from meta array
         * @param array $meta_data
         */
        public function get_meta_data_for_mutiple_ship($meta_data)
        {
            $this->sender_origin = (isset($meta_data['meta_data']['sender_origin'])) ?
                ucwords($meta_data['meta_data']['sender_origin']) : '';
            $this->accessorials = (isset($meta_data['meta_data']['accessorials'])) ?
                json_decode($meta_data['meta_data']['accessorials'], true) : [];
            $this->product_name = (isset($meta_data['meta_data']['product_name'])) ?
                json_decode($meta_data['meta_data']['product_name'], true) : [];
            $this->label_sufex = (isset($meta_data['label_sufex'])) ?
                $meta_data['label_sufex'] : [];
        }

        /**
         * Get data from meta array
         * @param array $meta_data
         */
        public function get_meta_data_from_rate($meta_data)
        {
            (isset($meta_data->key) && $meta_data->key === 'sender_origin') ?
                $this->sender_origin = ucwords($meta_data->value) : '';
            (isset($meta_data->key) && $meta_data->key === 'accessorials') ?
                $this->accessorials = json_decode($meta_data->value, true) : '';
            (isset($meta_data->key) && $meta_data->key === 'label_sufex') ?
                $this->label_sufex = json_decode($meta_data->value, true) : '';
            (isset($meta_data->key) && $meta_data->key === 'product_name') ?
                $this->product_name = json_decode($meta_data->value, true) : '';
        }

        /**
         * Show Order Detai on order page
         */
        public function show_order_widget_detail()
        {
            if (!strlen($this->sender_origin) > 0) {
                return;
            }
            $this->label_sufex = is_array($this->label_sufex) ? $this->label_sufex : [];
            echo '<h4 style="text-decoration: underline;margin: 4px 0px 4px 0px;">Shipment ' . esc_attr( $this->count) . " > Origin & Services </h4>";
            echo '<ul class="en-list" style="list-style: disc;list-style-position: inside;">';
            echo '<li>';

            echo esc_attr($this->sender_origin);

            echo '<br />';

            echo '</li>';

            echo '<li>' . esc_attr($this->shipping_method_title) . esc_attr( $this->en_format_price($this->shipping_method_total)) . '</li>';

            /* Show accessorials */
            $this->en_unishippers_small_show_accessorials(array_unique(array_merge($this->accessorials, $this->label_sufex)));

            echo "</ul>";
            echo "<br />";
            echo '<h4 style="text-decoration: underline;margin: 4px 0px 4px 0px;">Shipment ' . esc_attr( $this->count) . " > items </h4>";
            echo '<ul id="product-details-order" class="en-list" style="list-style: disc;list-style-position: inside;">';

            foreach (array_filter($this->product_name) as $product_str) {
                echo '<li>' . esc_attr($product_str) . '</li>';
            }

            echo '</ul>';
            echo "<br /><br />";
        }

        /**
         * set accessorials in label of rate
         * @param array $label_sufex
         * @param string $label
         * @return string
         */
        public function filter_from_label_sufex($label_sufex, $label)
        {
            $accessorials = [
                'L' => 'liftgate delivery',
                'T' => 'tailgate delivery',
            ];

            if (strpos($label, 'residential delivery') == false) {
                $accessorials['R'] = 'residential delivery';
            }

            $label_sufex = is_array($label_sufex) ? $label_sufex : [];
            $label_sufex = array_intersect_key($accessorials, array_flip($label_sufex));
            $label .= (!empty($label_sufex)) ? ' with ' . implode(' and ', $label_sufex) : '';
            return $label;
        }

        /**
         * Show accessorial on order detail page.
         * @param array $service_order_data
         */
        public function en_unishippers_small_show_accessorials($service_order_data)
        {
            foreach ($service_order_data as $key => $value) {
                echo ($value === 'R') ? '<li>Residential delivery</li>' : "";
                echo ($value == 'L') ? '<li>Lift gate delivery</li>' : "";
                echo ($value == 'H') ? '<li>Hazardous Material</li>' : "";
                echo ($value == 'HAT') ? '<li>Hold At Terminal</li>' : "";
            }
        }

        /**
         * Check accessorial.
         * @param array $service_order_data
         * @param string $shipment_status
         */
        public function en_check_accessorials($service_order_data, $shipment_status)
        {
            /* In case of singleshipment */
            if ($shipment_status == 'single') {
                $service_order_data = reset($service_order_data);
            }
            if (isset($service_order_data->handling_fee) && $service_order_data->handling_fee == 1) {
                $this->handling_fee = 1;
            }
        }

        /**
         * Items details.
         * @param array $order_details
         * @param string $shipment_status
         */
        function en_order_items_details($order_details, $shipment_status)
        {
            foreach ($order_details->items as $items) {
                echo esc_attr( $items->productQty ) . ' x ' . esc_attr( $items->productName);
            }
        }
    }

    /* Initialize class object */
    new Unishippers_Small_Order_Widget_Details();
}