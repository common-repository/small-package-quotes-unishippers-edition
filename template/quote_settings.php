<?php

/**
 * Unishipper Small Quote Settings
 *
 * @package     Unishipper Small Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class For Quote Settings Tab
 */
class Unishippers_Unishipper_Small_Quote_Settings
{

    /**
     * Quote Setting Fields
     */
    function unishepper_small_quote_settings_tab()
    {

        $disable_transit = "";
        $transit_package_required = "";

        $disable_hazardous = "";
        $hazardous_package_required = "";

        $action_transit = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'transit_days');
        if (is_array($action_transit)) {
            $disable_transit = "disabled_me";
            $transit_package_required = apply_filters('unishippers_small_plans_notification_link', $action_transit);
        }

        $action_hazardous = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'hazardous_material');
        if (is_array($action_hazardous)) {
            $disable_hazardous = "disabled_me";
            $hazardous_package_required = apply_filters('unishippers_small_plans_notification_link', $action_hazardous);
        }

        //**Plan_Validation: Cut Off Time & Ship Date Offset
        $disable_unishippers_small_cutOffTime_shipDateOffset = "";
        $unishippers_small_cutOffTime_shipDateOffset_package_required = "";
        $action_unishippers_small_cutOffTime_shipDateOffset = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'unishippers_small_cutOffTime_shipDateOffset');
        if (is_array($action_unishippers_small_cutOffTime_shipDateOffset)) {
            $disable_unishippers_small_cutOffTime_shipDateOffset = "disabled_me";
            $unishippers_small_cutOffTime_shipDateOffset_package_required = apply_filters('unishippers_small_plans_notification_link', $action_unishippers_small_cutOffTime_shipDateOffset);
        }

        //**Plan_Validation: Cut Off Time & Ship Date Offset
        $disable_show_delivery_estimates = "";
        $unishippers_small_esimate_delivery_package_required = "";
        $action_estimate_delivery_action = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'unishippers_small_show_delivery_estimates');
        if (is_array($action_estimate_delivery_action)) {
            $disable_show_delivery_estimates = "disabled_me";
            $unishippers_small_esimate_delivery_package_required = apply_filters('unishippers_small_plans_notification_link', $action_estimate_delivery_action);
        }
        //**End: Cut Off Time & Ship Date Offset

        $package_type_options = [
            'ship_alone' => __('Quote each item as shipping as its own package', 'eniture-unishippers-small-quotes'),
            'ship_combine_and_alone' => __('Combine the weight of all items without dimensions and quote them as one package while quoting each item with dimensions as shipping as its own package', 'eniture-unishippers-small-quotes'),
            'ship_one_package_70' => __('Quote shipping as if all items ship as one package up to 70 LB each', 'eniture-unishippers-small-quotes'),
            'ship_one_package_150' => __('Quote shipping as if all items ship as one package up to 150 LB each', 'eniture-unishippers-small-quotes'),
        ];
        $package_type_default = 'ship_alone';
        $unishepper_small_packaging_type = get_option("unishepper_small_packaging_type");
        if(!empty($unishepper_small_packaging_type) && $unishepper_small_packaging_type == 'old'){
            $package_type_default = 'eniture_packaging';
            $package_type_options['eniture_packaging'] = __('Use the default Eniture packaging algorithm', 'eniture-unishippers-small-quotes');
        }

        // Error management
        if (empty(get_option('error_management_settings_unishepper_small_packages'))) {
            update_option('error_management_settings_unishepper_small_packages', 'quote_shipping');
        }

        // Backup rates
        if (empty(get_option('backup_rates_category_unishippers_small'))) {
            update_option('backup_rates_category_unishippers_small', 'fixed_rate');
        }

        if (empty(get_option('backup_rates_display_unishippers_small'))) {
            update_option('backup_rates_display_unishippers_small', 'no_other_rates');
        }

        echo '<div class="unishipper_small_quote_section">';

        $settings = array(
            'unishepper_small_services' => array(
                'name' => __('Quote Service Options ', 'eniture-unishippers-small-quotes'),
                'type' => 'title',
                'desc' => '',
                'id' => 'unishepper_small_quote_hdng'
            ),
            'unishepper_small_domastic_srvcs' => array(
                'name' => __('Domestic Services', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'id' => 'unishepper_small_dom_srvc_hdng',
                'class' => 'dom_int_srvc_hdng'
            ),
            'unishepper_small_int_srvcs' => array(
                'name' => __('International Services', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'id' => 'unishepper_small_int_srvc_hdng',
                'class' => 'dom_int_srvc_hdng'
            ),
            'unishepper_small_select_all_services' => array(
                'name' => __('All Domestic Service Levels', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'id' => 'wc_settings_select_all_',
                'class' => 'unishepper_small_all_services',
            ),
            'unishepper_small_select_all_int_services' => array(
                'name' => __('All International Service Levels', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'id' => 'wc_settings_select_int_all',
                'class' => 'unishepper_small_all_int_services',
            ),
            'unishepper_next_day_air' => array(
                'name' => __('UPS Next Day Air', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_next_day_air',
                'class' => 'unishepper_small_quotes_services ups_next_day_saver',
            ),
            'unishepper_small_worldwide_express' => array(
                'name' => __('Worldwide Express', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_worldwide_express',
                'class' => 'unishepper_small_int_quotes_services worldwide_international',
            ),
            'unishepper_next_day_air_markup' => array(
                'name' => '',
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_next_day_air_markup',
                'placeholder' => 'Markup',
                'class' => 'unishipper_small_quotes_markup_left_markup',
            ),
            'unishepper_small_worldwide_express_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_worldwide_express_markup',
                'class' => 'unishipper_small_quotes_markup_right_markup',
            ),
            'unishepper_small_next_day_air_saver' => array(
                'name' => __('UPS Next Day Air Saver', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_next_day_air_saver',
                'class' => 'unishepper_small_quotes_services',
            ),
            'unishepper_small_worldwide_expedited' => array(
                'name' => __('Worldwide Expedited', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_worldwide_expedited',
                'class' => 'unishepper_small_int_quotes_services',
            ),
            'unishepper_small_next_day_air_saver_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_next_day_air_saver_markup',
                'class' => 'unishipper_small_quotes_markup_left_markup',
            ),
            'unishepper_small_worldwide_expedited_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_worldwide_expedited_markup',
                'class' => 'unishipper_small_quotes_markup_right_markup',
            ),
            'unishepper_small_next_day_air_early' => array(
                'name' => __('UPS Next Day Air Early A.M.', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_next_day_air_early',
                'class' => 'unishepper_small_quotes_services',
            ),
            'unishepper_small_worldwide_saver' => array(
                'name' => __('Worldwide Saver', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_worldwide_saver',
                'class' => 'unishepper_small_int_quotes_services',
            ),
            'unishepper_small_next_day_air_early_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_next_day_air_early_markup',
                'class' => 'unishipper_small_quotes_markup_left_markup',
            ),
            'unishepper_small_worldwide_saver_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_worldwide_saver_markup',
                'class' => 'unishipper_small_quotes_markup_right_markup',
            ),
            'unishepper_small_2_day_air' => array(
                'name' => __('UPS 2nd Day Air', 'woocommerce-settings-unishepper_small_quotes'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_2_day_air',
                'class' => 'unishepper_small_quotes_services',
            ),
            'unishepper_small_standard_canada' => array(
                'name' => __('Standard (Canada)', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_standard_canada',
                'class' => 'unishepper_small_int_quotes_services',
            ),
            'unishepper_small_2_day_air_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_2_day_air_markup',
                'class' => 'unishipper_small_quotes_markup_left_markup',
            ),
            'unishepper_small_standard_canada_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_standard_canada_markup',
                'class' => 'unishipper_small_quotes_markup_right_markup',
            ),
            'unishepper_small_2_day_air_am' => array(
                'name' => __('UPS 2nd Day Air A.M.', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_2_day_air_am',
                'class' => 'unishepper_small_quotes_services',
            ),
            'unishepper_small_1' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_int_priority',
                'class' => 'unishepper_small_int_quotes_services hide_checkbox',
            ),
            'unishepper_small_2_day_air_am_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_2_day_air_am_markup',
                'class' => 'unishipper_small_quotes_markup_left_markup',
            ),
            'unishepper_small_2_day_air_am_markup_empty' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_2_day_air_am_markup_empty',
                'class' => 'unishipper_small_quotes_markup_right_markup unshipper_hidden_markup',
            ),
            'unishepper_small_3_day_select' => array(
                'name' => __('UPS 3 Day Select', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_3_day_select',
                'class' => 'unishepper_small_quotes_services',
            ),
            'unishepper_small_2' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_int_priority_distribution',
                'class' => 'unishepper_small_int_quotes_services hide_checkbox',
            ),
            'unishepper_small_3_day_select_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_3_day_select_markup',
                'class' => 'unishipper_small_quotes_markup_left_markup',
            ),
            'unishepper_small_3_day_select_markup_empty' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_3_day_select_markup_empty',
                'class' => 'unishipper_small_quotes_markup_right_markup unshipper_hidden_markup',
            ),
            'unishepper_small_ups_ground' => array(
                'name' => __('UPS Ground', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_ups_ground',
                'class' => 'unishepper_small_quotes_services',
            ),
            'unishepper_small_3' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_int_priority_freight',
                'class' => 'unishepper_small_int_quotes_services hide_checkbox',
            ),
            'unishepper_small_ups_ground_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_ups_ground_markup',
                'class' => 'unishipper_small_quotes_markup_left_markup',
            ),
            'unishepper_small_ups_ground_markup_empty' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_ups_ground_markup_empty',
                'class' => 'unishipper_small_quotes_markup_right_markup unshipper_hidden_markup',
            ),
            'unishepper_small_ups_ground_residential_delivery' => array(
                'name' => __('UPS Ground (Residential Delivery)', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_ups_ground_residential_delivery',
                'class' => 'unishepper_small_quotes_services',
            ),
            'unishepper_small_4' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_int_distribution_freight',
                'class' => 'unishepper_small_int_quotes_services hide_checkbox',
            ),
            'unishepper_small_ups_ground_residential_delivery_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_ups_ground_residential_delivery_markup',
                'class' => 'unishipper_small_quotes_markup_left_markup',
            ),
            'unishepper_small_ups_ground_residential_delivery_markup_empty' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_ups_ground_residential_delivery_markup_empty',
                'class' => 'unishipper_small_quotes_markup_right_markup unshipper_hidden_markup',
            ),
            'unishepper_small_sat_ups_next_day_air' => array(
                'name' => __('Saturday - UPS Next Day Air', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_sat_ups_next_day_air',
                'class' => 'unishepper_small_quotes_services',
            ),
            'unishepper_small_5' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_int_distribution_freight',
                'class' => 'unishepper_small_int_quotes_services hide_checkbox',
            ),
            'unishepper_small_sat_ups_next_day_air_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_sat_ups_next_day_air_markup',
                'class' => 'unishipper_small_quotes_markup_left_markup',
            ),
            'unishepper_small_sat_ups_next_day_air_markup_empty' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_sat_ups_next_day_air_markup_empty',
                'class' => 'unishipper_small_quotes_markup_right_markup unshipper_hidden_markup',
            ),
            'unishepper_small_sat_ups_next_day_air_early' => array(
                'name' => __('Saturday - UPS Next Day Air Early A.M.', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_sat_ups_next_day_air_early',
                'class' => 'unishepper_small_quotes_services',
            ),
            'unishepper_small_6' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_int_distribution_freight',
                'class' => 'unishepper_small_int_quotes_services hide_checkbox',
            ),
            'unishepper_small_sat_ups_next_day_air_early_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_sat_ups_next_day_air_early_markup',
                'class' => 'unishipper_small_quotes_markup_left_markup',
            ),
            'unishepper_small_sat_ups_next_day_air_early_markup_empty' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_sat_ups_next_day_air_early_markup_empty',
                'class' => 'unishipper_small_quotes_markup_right_markup unshipper_hidden_markup',
            ),
            'unishepper_small_sat_ups_2_day_air' => array(
                'name' => __('Saturday - UPS 2nd Day Air', 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_sat_ups_2_day_air',
                'class' => 'unishepper_small_quotes_services',
            ),
            'unishepper_small_7' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => '',
                'id' => 'unishepper_small_int_distribution_freight',
                'class' => 'unishepper_small_int_quotes_services hide_checkbox',
            ),
            'unishepper_small_sat_ups_2_day_air_markup' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_sat_ups_2_day_air_markup',
                'class' => 'unishipper_small_quotes_markup_left_markup',
            ),
            'unishepper_small_sat_ups_2_day_air_markup_empty' => array(
                'name' => '',
                'type' => 'text',
                'placeholder' => 'Markup',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%).', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_sat_ups_2_day_air_markup_empty',
                'class' => 'unishipper_small_quotes_markup_right_markup unshipper_hidden_markup',
            ),

            'price_sort_unisippers_small' => array(
                'name' => __("Don't sort shipping methods by price  ", 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => 'By default, the plugin will sort all shipping methods by price in ascending order.',
                'id' => 'shipping_methods_do_not_sort_by_price'
            ),

            // Package rating method when Standard Box Sizes isn't in use
            'unishippers_small_packaging_method_label' => array(
                'name' => __('Package rating method when Standard Box Sizes isn\'t in use', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'id' => 'unishippers_small_packaging_method_label'
            ),
            'unishippers_small_packaging_method' => array(
                'name' => '',
                'type' => 'radio',
                'default' => $package_type_default,
                'options' => $package_type_options,
                'id' => 'unishippers_small_packaging_method',
            ),

            // show delivery estimates options
            'service_unishippers_small_estimates_title' => array(
                'name' => __('Delivery Estimate Options ', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'desc' => $unishippers_small_esimate_delivery_package_required,
                'id' => 'service_unishippers_small_estimates_title'
            ),
            'dont_show_estimates_unishippers_small' => array(
                'name' => '',
                'type' => 'radio',
                'class' => "$disable_show_delivery_estimates",
                'default' => "dont_show_estimates",
                'options' => array(
                    'dont_show_estimates' => __("Don't display delivery estimates.", 'eniture-unishippers-small-quotes'),
                    'delivery_days' => __('Display estimated number of days until delivery.', 'eniture-unishippers-small-quotes'),
                    'delivery_date' => __('Display estimated delivery date.', 'eniture-unishippers-small-quotes'),
                ),
                'id' => 'unishippers_small_delivery_estimates',
            ),
            //**Start: Cut Off Time & Ship Date Offset
            'unishippers_small_cutOffTime_shipDateOffset_unishippers_small' => array(
                'name' => __('Cut Off Time & Ship Date Offset ', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => $unishippers_small_cutOffTime_shipDateOffset_package_required,
                'id' => 'unishippers_small_cutOffTime_shipDateOffset'
            ),
            'orderCutoffTime_unishippers_small' => array(
                'name' => __('Order Cut Off Time ', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'placeholder' => '--:-- --',
                'desc' => 'Enter the cut off time (e.g. 2.00) for the orders. Orders placed after this time will be quoted as shipping the next business day.',
                'id' => 'unishippers_small_orderCutoffTime',
                'class' => $disable_unishippers_small_cutOffTime_shipDateOffset,
            ),
            'shipmentOffsetDays_unishippers_small' => array(
                'name' => __('Fulfilment Offset Days ', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'desc' => 'The number of days the ship date needs to be moved to allow the processing of the order.',
                'placeholder' => 'Fulfilment Offset Days, e.g. 2',
                'id' => 'unishippers_small_shipmentOffsetDays',
                'class' => $disable_unishippers_small_cutOffTime_shipDateOffset,
            ),
            'all_shipment_days_unishippers_small' => array(
                'name' => __("What days do you ship orders?", 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => 'Select All',
                'class' => "all_shipment_days_unishippers_small $disable_unishippers_small_cutOffTime_shipDateOffset",
                'id' => 'all_shipment_days_unishippers_small'
            ),
            'monday_shipment_day_unishippers_small' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => 'Monday',
                'class' => "unishippers_small_shipment_day $disable_unishippers_small_cutOffTime_shipDateOffset",
                'id' => 'monday_shipment_day_unishippers_small'
            ),
            'tuesday_shipment_day_unishippers_small' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => 'Tuesday',
                'class' => "unishippers_small_shipment_day $disable_unishippers_small_cutOffTime_shipDateOffset",
                'id' => 'tuesday_shipment_day_unishippers_small'
            ),
            'wednesday_shipment_day_unishippers_small' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => 'Wednesday',
                'class' => "unishippers_small_shipment_day $disable_unishippers_small_cutOffTime_shipDateOffset",
                'id' => 'wednesday_shipment_day_unishippers_small'
            ),
            'thursday_shipment_day_unishippers_small' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => 'Thursday',
                'class' => "unishippers_small_shipment_day $disable_unishippers_small_cutOffTime_shipDateOffset",
                'id' => 'thursday_shipment_day_unishippers_small'
            ),
            'friday_shipment_day_unishippers_small' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => 'Friday',
                'class' => "unishippers_small_shipment_day $disable_unishippers_small_cutOffTime_shipDateOffset",
                'id' => 'friday_shipment_day_unishippers_small'
            ),
            // Start Transit days            
            'unishepper_sm_ground_transit_label' => array(
                'name' => __('Ground transit time restriction', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => $transit_package_required,
                'id' => 'unishepper_sm_ground_transit_label'
            ),
            'restrict_days_transit_package_unishepper_small' => array(
                'name' => __('Enter the number of transit days to restrict ground service to. Leave blank to disable this feature.', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'class' => $disable_transit,
                'id' => 'restrict_days_transit_package_unishepper_small'
            ),
            'restrict_radio_btn_transit_unishepper_small' => array(
                'name' => '',
                'type' => 'radio',
                'id' => 'restrict_transit_unishepper_small_packages',
                'class' => $disable_transit,
                'options' => array(
                    'TransitTimeInDays' => __('Restrict by the carrier\'s in transit days metric.', 'eniture-unishippers-small-quotes'),
                    'CalenderDaysInTransit' => __('Restrict by the calendar days in transit.', 'eniture-unishippers-small-quotes'),
                ),
                'id' => 'restrict_radio_btn_transit_unishepper_small',
            ),
            /*
             * Unishipper Residentail Delivery, Handeling Fee And Hazardous Fee
             */
            'residential_delivery_options_label' => array(
                'name' => __('Residential Delivery', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'id' => 'residential_delivery_options_label'
            ),
            'unishepper_small_residential_delivery' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => __('Always quote as residential delivery', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_quote_as_residential_delivery'
            ),
            // Auto-detect residential addresses notification
            'avaibility_auto_residential' => array(
                'name' => '',
                'type' => 'text',
                'class' => 'hidden',
                'desc' => "Click <a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/'>here</a> to add the Auto-detect residential addresses module. (<a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/#documentation'>Learn more</a>)",
                'id' => 'avaibility_auto_residential'
            ),
            // Use my standard box sizes notification
            'avaibility_box_sizing' => array(
                'name' => __('Use my standard box sizes', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => "Click <a target='_blank' href='https://eniture.com/woocommerce-standard-box-sizes/'>here</a> to add the Standard Box Sizes module. (<a target='_blank' href='https://eniture.com/woocommerce-standard-box-sizes/#documentation'>Learn more</a>)",
                'id' => 'avaibility_box_sizing'
            ),
            // Start Hazardous Material
            'unishepper_small_hazardous_fee' => array(
                'name' => __('Hazardous material settings', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => $hazardous_package_required,
                'id' => 'unishepper_small_hazardous_fee'
            ),
            'unishepper_small_hazardous_materials_shipments' => array(
                'name' => '',
                'type' => 'checkbox',
                'desc' => 'Only quote ground service for hazardous materials shipments',
                'class' => $disable_hazardous,
                'id' => 'unishepper_small_hazardous_materials_shipments',
            ),
            'en_unishippers_ground_hazardous_material_fee' => array(
                'name' => __('Ground Hazardous Material Fee', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'desc' => 'Enter an amount, e.g 20. or Leave blank to disable.',
                'class' => $disable_hazardous,
                'id' => 'en_unishippers_ground_hazardous_material_fee'
            ),
            'en_unishippers_air_hazardous_material_fee' => array(
                'name' => __('Air Hazardous Material Fee', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'desc' => 'Enter an amount, e.g 20. or Leave blank to disable.',
                'class' => $disable_hazardous,
                'id' => 'en_unishippers_air_hazardous_material_fee'
            ),
            // End Hazardous Material
            'unishepper_small_hand_free' => array(
                'name' => __('Handling Fee / Markup ', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'desc' => '<span class="desc_text_style">Amount excluding tax. Enter an amount, e.g 3.75, or a percentage, e.g, 5%. Leave blank to disable.</span>',
                'id' => 'unishipper_small_hand_fee_mark_up'
            ),
            'unishepper_small_enable_logs' => array(
                'name' => __("Enable Logs  ", 'eniture-unishippers-small-quotes'),
                'type' => 'checkbox',
                'desc' => 'When checked, the Logs page will contain up to 25 of the most recent transactions.',
                'id' => 'unishepper_small_enable_logs'
            ),
            'allow_other_plugins_unishepper_small' => array(
                'name' => __('Allow other plugins to show quotes ', 'eniture-unishippers-small-quotes'),
                'type' => 'select',
                'default' => '3',
                'desc' => '',
                'id' => 'unishepper_small_allow_other_plugins_option',
                'options' => array(
                    'no' => __('NO', 'eniture-unishippers-small-quotes'),
                    'yes' => __('YES', 'eniture-unishippers-small-quotes')
                )
            ),
            // Error Management
            'error_management_label_unishipper_small_packages' => array(
                'name' => __('Error Management', 'woocommerce-settings-unishepper_small_quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'id' => 'error_management_label_unishipper_small_packages'
            ),
            'error_management_settings_unishepper_small_packages' => array(
                'name' => __('', 'woocommerce-settings-unishepper_small_quotes'),
                'type' => 'radio',
                'class' => "restrict_by_calendar_days_in_transit_1st_option",
                'default' => 'quote_shipping',
                'options' => array(
                    'quote_shipping' => __('Quote shipping using known shipping parameters, even if other items are missing shipping parameters.', 'woocommerce'),
                    'dont_quote_shipping' => __('Don\'t quote shipping if one or more items are missing the required shipping parameters.', 'woocommerce'),
                ),
                'id' => 'error_management_settings_unishepper_small_packages',
            ),
            // Backup rates
            'unable_retrieve_shipping_clear_unishepper_small' => array(
                'title' => '',
                'name' => '',
                'desc' => '',
                'id' => 'wc_unable_retrieve_shipping_clear_unishepper_small',
                'css' => '',
                'default' => '',
                'type' => 'title',
            ),
            'unable_retrieve_shipping_unishippers_small' => array(
                'name' => __('Checkout options if the plugin fails to return a rate ', 'eniture-unishippers-small-quotes'),
                'type' => 'title',
                'id' => 'wc_settings_unable_retrieve_shipping_unishippers_small'
            ),
            'enable_backup_rates_unishippers_small' => array(
                'name' => __('', 'woocommerce-settings-odfl-quotes'),
                'type' => 'checkbox',
                'desc' => __('Present the user with a backup shipping rate.', 'woocommerce-settings-odfl-quotes'),
                'id' => 'enable_backup_rates_unishippers_small',
            ),
            'backup_rates_label_unishippers_small' => array(
                'name' => __('', 'woocommerce-settings-odfl-quotes'),
                'type' => 'text',
                'desc' => 'Label for backup shipping rate (Maximum of 50 characters).',
                'id' => 'backup_rates_label_unishippers_small'
            ),
            'backup_rates_category_unishippers_small' => array(
                'name' => __('', 'woocommerce-settings-odfl-quotes'),
                'type' => 'radio',
                'default' => 'fixed_rate',
                'options' => array(
                    'fixed_rate' => __('', 'woocommerce'),
                    'percentage_of_cart_price' => __('', 'woocommerce'),
                    'function_of_weight' => __('', 'woocommerce'),
                ),
                'id' => 'backup_rates_category_unishippers_small',
            ),
            'backup_rates_carrier_fails_to_return_response_unishippers_small' => array(
                'name' => __('', 'woocommerce-settings-odfl-quotes'),
                'type' => 'checkbox',
                'desc' => __('Display the backup rate if the carrier fails to return a response.', 'woocommerce-settings-odfl-quotes'),
                'id' => 'backup_rates_carrier_fails_to_return_response_unishippers_small',
            ),
            'backup_rates_carrier_returns_error_unishippers_small' => array(
                'name' => __('', 'woocommerce-settings-odfl-quotes'),
                'type' => 'checkbox',
                'desc' => __('Display the backup rate if the carrier returns an error.', 'woocommerce-settings-odfl-quotes'),
                'id' => 'backup_rates_carrier_returns_error_unishippers_small',
            ),
            'backup_rates_display_unishippers_small' => array(
                'name' => __('', 'woocommerce-settings-odfl-quotes'),
                'type' => 'radio',
                'default' => 'no_other_rates',
                'options' => array(
                    'no_plugin_rates' => __('Display the backup rate if the plugin fails to return a rate.', 'woocommerce'),
                    'no_other_rates' => __('Display the backup rate only if no rates, from any shipping method, are presented.', 'woocommerce'),
                ),
                'id' => 'backup_rates_display_unishippers_small',
            ),
            'section_end_quote' => array(
                'type' => 'sectionend',
                'id' => 'wc_settings_quote_section_end'
            )
        );
        return $settings;
    }

}
