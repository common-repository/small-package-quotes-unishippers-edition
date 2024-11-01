<?php
/*
  Plugin Name: Small Package Quotes - Unishippers Edition
  Plugin URI: https://eniture.com/products/
  Description: Dynamically retrieves your negotiated shipping rates from Unishippers and displays the results in the WooCommerce shopping cart.
  Version: 2.4.8
  Author: Eniture Technology
  Author URI: http://eniture.com/
  Text Domain: eniture-unishippers-small-quotes
  License: GPL-2.0-or-later
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Requires at least: 6.5
 */

/**
 * Unishipper Small Plugin
 *
 * @package     Unishipper Small Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('UNISHIPPERS_DOMAIN_HITTING_URL', 'https://ws062.eniture.com');
define('UNISHIPPERS_NEW_API_DOMAIN_HITTING_URL', 'https://ws002.eniture.com');
define('UNISHIPPERS_FDO_HITTING_URL', 'https://freightdesk.online/api/updatedWoocomData');
define('UNISHIPPERS_FDO_COUPON_BASE_URL', 'https://freightdesk.online');
define('UNISHIPPERS_VA_COUPON_BASE_URL', 'https://validate-addresses.com');

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

// Define reference
function unishippers_en_small_plugin($plugins)
{
    $plugins['spq'] = (isset($plugins['spq'])) ? array_merge($plugins['spq'], ['unishepper_small' => 'WC_unishipper_small']) : ['unishepper_small' => 'WC_unishipper_small'];
    return $plugins;
}

add_filter('en_plugins', 'unishippers_en_small_plugin');
/**
 * Array For common Plans Notification On Product Detail Page
 */
if (!function_exists('unishippers_en_woo_plans_notification_PD')) {

    function unishippers_en_woo_plans_notification_PD($product_detail_options)
    {
        $eniture_plugins_id = 'eniture_plugin_';
        for ($en = 1; $en <= 25; $en++) {
            $settings = get_option($eniture_plugins_id . $en);

            if (isset($settings) && (!empty($settings)) && (is_array($settings))) {

                $plugin_detail = current($settings);
                $plugin_name = (isset($plugin_detail['plugin_name'])) ? $plugin_detail['plugin_name'] : "";

                foreach ($plugin_detail as $key => $value) {
                    if ($key != 'plugin_name') {
                        $action = $value === 1 ? 'enable_plugins' : 'disable_plugins';
                        $product_detail_options[$key][$action] = (isset($product_detail_options[$key][$action]) && strlen($product_detail_options[$key][$action]) > 0) ? ", $plugin_name" : "$plugin_name";
                    }
                }
            }
        }
        return $product_detail_options;
    }

    add_filter('en_woo_plans_notification_action', 'unishippers_en_woo_plans_notification_PD', 10, 1);
}

/**
 * Show plan notification on product detail page
 */
if (!function_exists('unishippers_en_woo_plans_notification_message')) {

    function unishippers_en_woo_plans_notification_message($enable_plugins, $disable_plugins)
    {
        $enable_plugins = (strlen($enable_plugins) > 0) ? "$enable_plugins: <b> Enabled</b>. " : "";
        $disable_plugins = (strlen($disable_plugins) > 0) ? " $disable_plugins: Upgrade to <b>Standard Plan to enable</b>." : "";
        return $enable_plugins . "<br>" . $disable_plugins;
    }

    add_filter('en_woo_plans_notification_message_action', 'unishippers_en_woo_plans_notification_message', 10, 2);
}


/**
 * Load scripts for Unishippers Small json tree view
 */
if (!function_exists('unishippers_en_small_jtv_script')) {
    function unishippers_en_small_jtv_script()
    {
        wp_register_style('unishippers_small_json_tree_view_style', plugin_dir_url(__FILE__) . 'logs/en-json-tree-view/en-jtv-style.css');
        wp_register_script('unishippers_small_json_tree_view_script', plugin_dir_url(__FILE__) . 'logs/en-json-tree-view/en-jtv-script.js', ['jquery'], '1.0.1');

        wp_enqueue_style('unishippers_small_json_tree_view_style');
        wp_enqueue_script('unishippers_small_json_tree_view_script', [
            'en_tree_view_url' => plugins_url(),
        ]);

        // Shipping rules script and styles
        wp_enqueue_script('en_unishippers_small_sr_script', plugin_dir_url(__FILE__) . '/shipping-rules/assets/js/shipping_rules.js', array(), '1.0.0');
        wp_localize_script('en_unishippers_small_sr_script', 'script', array(
            'pluginsUrl' => plugins_url(),
        ));
        wp_register_style('en_unishippers_small_shipping_rules_section', plugin_dir_url(__FILE__) . '/shipping-rules/assets/css/shipping_rules.css', false, '1.0.0');
        wp_enqueue_style('en_unishippers_small_shipping_rules_section');
    }

    add_action('admin_init', 'unishippers_en_small_jtv_script');
}

if (!function_exists('is_plugin_active')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

/**
 * Check woocommerce installlation
 */
if (!is_plugin_active('woocommerce/woocommerce.php')) {
    add_action('admin_notices', 'unishippers_small_wc_avaibility_err');
}

/**
 * Woo Availability error
 */
function unishippers_small_wc_avaibility_err()
{
    $message = "Unishipper Small is enabled but not effective. It requires WooCommerce to work, please <a target='_blank' href='https://wordpress.org/plugins/woocommerce/installation/'>Install</a> WooCommerce Plugin.";
    echo '<div class="error"> <p>'.esc_html($message).'</p></div>';
}

/**
 * Check woocommerce version compatibility
 */
add_action('admin_init', 'unishippers_small_check_woo_version');

/**
 * Check Woo Version
 */
function unishippers_small_check_woo_version()
{
    $woo_version = unishippers_small_wc_version_number();
    $version = '2.6';
    if (!version_compare($woo_version, $version, ">=")) {
        add_action('admin_notices', 'unishippers_small_wc_version_failure');
    }
}

/**
 * Woo Version Failure
 */
function unishippers_small_wc_version_failure()
{
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            esc_html_e('Unishipper Small plugin requires WooCommerce version 2.6 or higher to work. Functionality may not work properly.', 'eniture-unishippers-small-quotes');
            ?>
        </p>
    </div>
    <?php
}

/**
 * Return woocomerce version
 */
function unishippers_small_wc_version_number()
{
    $plugin_folder = get_plugins('/' . 'woocommerce');
    $plugin_file = 'woocommerce.php';

    if (isset($plugin_folder[$plugin_file]['Version'])) {
        return $plugin_folder[$plugin_file]['Version'];
    } else {
        return NULL;
    }
}

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) || is_plugin_active_for_network('woocommerce/woocommerce.php')) {

    /**
     * Load scripts for Unishipper Small
     */
    add_action('admin_enqueue_scripts', 'unishippers_small_admin_script');

    /**
     * Admin Script
     */
    function unishippers_small_admin_script()
    {

        wp_register_style('unishipper_small_style', plugin_dir_url(__FILE__) . '/css/unishipper_small_style.css', false, '2.1.2');
        wp_enqueue_style('unishipper_small_style');

        wp_register_style('unishippers_small_wickedpicker_style', 'https://cdn.jsdelivr.net/npm/wickedpicker@0.4.3/dist/wickedpicker.min.css', false, '2.0.3');
        wp_enqueue_style('unishippers_small_wickedpicker_style');
        wp_register_script('unishippers_small_wickedpicker_style', plugin_dir_url(__FILE__) . '/js/wickedpicker.js', false, '1.0.0');
        wp_enqueue_script('unishippers_small_wickedpicker_style');

        if(is_admin() && (!empty( $_GET['page']) && 'wc-orders' == $_GET['page'] ) && (!empty( $_GET['action']) && 'new' == $_GET['action'] )) {
            if (!wp_script_is('eniture_calculate_shipping_admin', 'enqueued')) {
                wp_enqueue_script('eniture_calculate_shipping_admin', plugin_dir_url(__FILE__) . 'js/eniture-calculate-shipping-admin.js', array(), '1.0.0' );
            }
        }

    }

    /**
     * Unishipper Small action links
     */
    add_filter('plugin_action_links', 'unishippers_small_add_action_plugin', 10, 5);

    /**
     * Add Plugin Actions
     * @staticvar $plugin
     * @param $actions
     * @param $plugin_file
     * @return array
     */
    function unishippers_small_add_action_plugin($actions, $plugin_file)
    {
        static $plugin;
        if (!isset($plugin))
            $plugin = plugin_basename(__FILE__);
        if ($plugin == $plugin_file) {
            $settings = array('settings' => '<a href="admin.php?page=wc-settings&tab=unishipper_small">' . __('Settings', 'eniture-unishippers-small-quotes') . '</a>');
            $site_link = array('support' => '<a href="https://support.eniture.com/" target="_blank">Support</a>');
            $actions = array_merge($settings, $actions);
            $actions = array_merge($site_link, $actions);
        }

        return $actions;
    }

    /**
     * Get Host
     * @param type $url
     * @return type
     */
    if (!function_exists('unishippers_getHost')) {

        function unishippers_getHost($url)
        {
            $parseUrl = parse_url(trim($url));
            if (isset($parseUrl['host'])) {
                $host = $parseUrl['host'];
            } else {
                $path = explode('/', $parseUrl['path']);
                $host = $path[0];
            }
            return trim($host);
        }

    }

    /**
     * Get Domain Name
     */
    if (!function_exists('unishippers_small_get_domain')) {

        function unishippers_small_get_domain()
        {
            global $wp;
            $wp_request = (isset($wp->request)) ? $wp->request : '';
            $url = home_url($wp_request);
            return unishippers_getHost($url);
        }

    }

    add_action('admin_enqueue_scripts', 'unishippers_en_small_script');

    /**
     * Load Front-end scripts for unishippers
     */
    function unishippers_en_small_script()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('unishippers_en_small_script', plugin_dir_url(__FILE__) . 'js/en-unishippers-small.js', array(), '1.0.6');
        wp_localize_script('unishippers_en_small_script', 'unishippers_en_small_admin_script', array(
            'plugins_url' => plugins_url(),
            'allow_proceed_checkout_eniture' => trim(get_option("allow_proceed_checkout_eniture")),
            'prevent_proceed_checkout_eniture' => trim(get_option("prevent_proceed_checkout_eniture")),
            'unishippers_small_order_cutoff_time' => get_option("unishippers_small_orderCutoffTime"),
            'unishepper_small_packaging_type' => get_option("unishepper_small_packaging_type"),
            'backup_rates_fixed_rate_unishippers_small' => get_option("backup_rates_fixed_rate_unishippers_small"),
            'backup_rates_cart_price_percentage_unishippers_small' => get_option("backup_rates_cart_price_percentage_unishippers_small"),
            'backup_rates_weight_function_unishippers_small' => get_option("backup_rates_weight_function_unishippers_small"),
        ));
    }

    /**
     * Inlude Plugin Files
     */
    require_once('warehouse-dropship/wild-delivery.php');
    require_once('warehouse-dropship/get-distance-request.php');

    require_once('standard-package-addon/standard-package-addon.php');

    require_once 'update-plan.php';
    require_once 'fdo/en-fdo.php';
    require_once 'fdo/en-sbs.php';
    require_once 'unishippers_small_version_compact.php';

    require_once 'helper/en_helper_class.php';
    require_once('db/unishipper_small_db.php');
    require_once('unishipper-small-carriers.php');
    require_once('unishipper_small_admin_filter.php');
    require_once('unishipper-small-curl-class.php');
    require_once('unishipper-small-auto-residential.php');
    require_once('unishipper_small_shipping_class.php');
    require_once('template/connection_settings.php');
    require_once('template/quote_settings.php');
    require_once 'template/csv-export.php';
    require_once('unishipper_small_test_connection.php');
    require_once('unishipper_small_carrier_service.php');
    require_once('unishipper_small_group_package.php');
    require_once('unishipper_small_wc_update_change.php');
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    require_once('order-details/en-order-export.php');
    require_once('order-details/en-order-widget.php');
    require_once('order-details/rates/order-rates.php');
    require_once('template/en-product-detail.php');
    require_once('template/products-insurance-option.php');
    require_once('template/products-nested-options.php');
    require_once('shipping-rules/shipping-rules-save.php');
    /**
     * Unishipper Small Activation Hook
     */
    register_activation_hook(__FILE__, 'unishippers_create_small_wh_db');
    register_activation_hook(__FILE__, 'unishippers_create_small_option');
    register_activation_hook(__FILE__, 'unishippers_small_activate_hit_to_update_plan');
    register_deactivation_hook(__FILE__, 'unishippers_small_deactivate_hit_to_update_plan');
    register_deactivation_hook(__FILE__, 'unishippers_small_shipping_rules_db');

    register_activation_hook(__FILE__, 'unishippers_small_update_coupon_status_activate_en_fdo');
    register_deactivation_hook(__FILE__, 'unishippers_small_update_coupon_status_deactivate_en_fdo');
    register_activation_hook(__FILE__, 'unishippers_small_update_coupon_status_activate_en_va');
    register_deactivation_hook(__FILE__, 'unishippers_small_update_coupon_status_deactivate_en_va');
    register_deactivation_hook(__FILE__, 'unishippers_small_deactivate_plugin');

    /**
     * Hook to call when plugin upgrade
     */
    function unishippers_en_small_plugin_update( $upgrader_object, $options ) {
        $plugin_path_name = plugin_basename( __FILE__ );

        if ($options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
            foreach($options['plugins'] as $each_plugin) {
                if ($each_plugin == $plugin_path_name) {
                    if (!function_exists('unishippers_small_activate_hit_to_update_plan')) {
                        require_once(__DIR__ . '/update-plan.php');
                    }
        
                    unishippers_create_small_wh_db();
                    unishippers_create_small_option();
                    unishippers_small_activate_hit_to_update_plan();
                }
            }
        }
    }

    add_action( 'upgrader_process_complete', 'unishippers_en_small_plugin_update',10, 2);

    /**
     * unishepper small plugin update now
     * @param array type $upgrader_object
     * @param array type $options
     */
    function unishippers_en_unishepper_small_update_now()
    {
        $index = 'small-package-quotes-unishippers-edition/small-package-quotes-unishippers-edition.php';
        $plugin_info = get_plugins();
        $plugin_version = (isset($plugin_info[$index]['Version'])) ? $plugin_info[$index]['Version'] : '';
        $update_now = get_option('en_unishepper_small_update_now');

        if ($update_now != $plugin_version) {
            if (!function_exists('unishippers_small_activate_hit_to_update_plan')) {
                require_once(__DIR__ . '/update-plan.php');
            }

            unishippers_create_small_wh_db();
            unishippers_create_small_option();
            unishippers_small_activate_hit_to_update_plan();

            update_option('en_unishepper_small_update_now', $plugin_version);
        }
    }

    add_action('init', 'unishippers_en_unishepper_small_update_now');

    /**
     * Unishipper Small Action And Filters
     */
    add_filter('woocommerce_shipping_methods', 'unishippers_add_unishepper_small');
    add_filter('woocommerce_get_settings_pages', 'unishippers_small_shipping_sections');
    add_action('woocommerce_shipping_init', 'unishippers_small_init');
    add_filter('woocommerce_package_rates', 'unishippers_small_hide_shipping');
    add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');
    add_action('init', 'unishippers_small_no_method_available');
    // Origin terminal address
    add_action('admin_init', 'unishippers_create_small_wh_db');
    add_action('admin_init', 'unishippers_small_update_warehouse');
    add_action('admin_init', 'unishippers_small_shipping_rules_db');
}

define("unishippers_en_woo_plugin_unishepper_small", "unishepper_small");

add_action('wp_enqueue_scripts', 'en_unishepper_small_frontend_checkout_script');

/**
 * Load Frontend scripts for Unishipper Small
 */
function en_unishepper_small_frontend_checkout_script()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('en_unishepper_small_frontend_checkout_script', plugin_dir_url(__FILE__) . 'front/js/en-unishepper-small-checkout.js', array(), '1.0.1');
    wp_localize_script('en_unishepper_small_frontend_checkout_script', 'frontend_script', array(
        'pluginsUrl' => plugins_url(),
    ));
}

/**
 * Plans Common Hooks
 */
add_filter('unishippers_small_quotes_plans_suscription_and_features', 'unishippers_small_quotes_plans_suscription_and_features', 1);

/**
 * Features with their plans
 * @param string $feature
 * @return Array/Boolean
 */
function unishippers_small_quotes_plans_suscription_and_features($feature)
{
    $package = get_option('unishepper_small_package');

    $features = array
    (
        'instore_pickup_local_devlivery' => array('3'),
        'transit_days' => array('3'),
        'hazardous_material' => array('2', '3'),
        'insurance_fee' => array('2', '3'),
        'multi_warehouse' => array('2', '3'),
        'unishippers_small_cutOffTime_shipDateOffset' => array('2', '3'),
        'unishippers_small_show_delivery_estimates' => array('0', '1', '2', '3'),
        'nested_material' => array('3'),
    );

    return (isset($features[$feature]) && (in_array($package, $features[$feature]))) ? TRUE : ((isset($features[$feature])) ? $features[$feature] : '');
}

add_filter('unishippers_small_plans_notification_link', 'unishippers_small_plans_notification_link', 1);

/**
 * Plan Notification URL To Redirect eniture.com
 * @param array $plans
 * @return string
 */
function unishippers_small_plans_notification_link($plans)
{
    $plan = current($plans);
    $plan_to_upgrade = "";
    switch ($plan) {
        case 2:
            $plan_to_upgrade = "<a target='_blank' href='https://eniture.com/woocommerce-unishippers-small-package-plugin/'>Standard Plan required</a>";
            break;
        case 3:
            $plan_to_upgrade = "<a target='_blank' href='https://eniture.com/woocommerce-unishippers-small-package-plugin/'>Advanced Plan required</a>";
            break;
    }

    return $plan_to_upgrade;
}

if (!function_exists('unishippers_check_ground_transit_restrict_status')) {

    function unishippers_check_ground_transit_restrict_status($ground_transit_statuses)
    {
        $ground_transit_restrict_plan = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'transit_days');
        $ground_restrict_value = (false !== get_option('restrict_days_transit_package_unishepper_small')) ? get_option('restrict_days_transit_package_unishepper_small') : '';
        if ('' !== $ground_restrict_value && strlen(trim($ground_restrict_value)) && !is_array($ground_transit_restrict_plan)) {
            $ground_transit_statuses['unishippers'] = '1';
        }

        return $ground_transit_statuses;
    }

    add_filter('en_check_ground_transit_restrict_status', 'unishippers_check_ground_transit_restrict_status', 9, 1);
}

/**
 * Function that will trigger on activation
 */
function unishippers_small_update_coupon_status_activate_en_fdo()
{
    $fdo_coupon_data = get_option('en_fdo_coupon_data');
    if (!empty($fdo_coupon_data)) {
        $fdo_coupon_data_decorded = json_decode($fdo_coupon_data);
        if (isset($fdo_coupon_data_decorded->promo)) {
            $data = array(
                'marketplace' => 'wp',
                'promocode' => $fdo_coupon_data_decorded->promo->coupon,
                'action' => 'install',
                'carrier' => 'UNI_PL'
            );

            $url = UNISHIPPERS_FDO_COUPON_BASE_URL . "/change_promo_code_status";
            $response = wp_remote_get($url,
                array(
                    'method' => 'GET',
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => $data,
                )
            );
        }
    }
}

/**
 * Function that will trigger on deactivation
 */
function unishippers_small_update_coupon_status_deactivate_en_fdo()
{
    $fdo_coupon_data = get_option('en_fdo_coupon_data');
    if (!empty($fdo_coupon_data)) {
        $fdo_coupon_data_decorded = json_decode($fdo_coupon_data);
        if (isset($fdo_coupon_data_decorded->promo)) {
            $data = array(
                'marketplace' => 'wp',
                'promocode' => $fdo_coupon_data_decorded->promo->coupon,
                'action' => 'uninstall',
                'carrier' => 'UNI_PL'
            );

            $url = UNISHIPPERS_FDO_COUPON_BASE_URL . "/change_promo_code_status";
            $response = wp_remote_get($url,
                array(
                    'method' => 'GET',
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => $data,
                )
            );
        }
    }
}

/**
 * Function that will trigger on activation
 */
function unishippers_small_update_coupon_status_activate_en_va()
{
    $va_coupon_data = get_option('en_va_coupon_data');
    if (!empty($va_coupon_data)) {
        $va_coupon_data_decorded = json_decode($va_coupon_data);
        if (isset($va_coupon_data_decorded->promo)) {
            $data = array(
                'marketplace' => 'wp',
                'promocode' => $va_coupon_data_decorded->promo->coupon,
                'action' => 'install',
                'carrier' => 'UNI_PL'
            );

            $url = UNISHIPPERS_VA_COUPON_BASE_URL . "/change_promo_code_status?";
            $response = wp_remote_get($url,
                array(
                    'method' => 'GET',
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => $data,
                )
            );
        }
    }
}

/**
 * Function that will trigger on deactivation
 */
function unishippers_small_update_coupon_status_deactivate_en_va()
{
    $va_coupon_data = get_option('en_va_coupon_data');
    if (!empty($va_coupon_data)) {
        $va_coupon_data_decorded = json_decode($va_coupon_data);
        if (isset($va_coupon_data_decorded->promo)) {
            $data = array(
                'marketplace' => 'wp',
                'promocode' => $va_coupon_data_decorded->promo->coupon,
                'action' => 'uninstall',
                'carrier' => 'UNI_PL'
            );

            $url = UNISHIPPERS_VA_COUPON_BASE_URL . "/change_promo_code_status?";
            $response = wp_remote_get($url,
                array(
                    'method' => 'GET',
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => $data,
                )
            );
        }
    }
}

require_once 'fdo/en-coupon-api.php';
new Unishippers_EnUniSmallCouponAPI();
