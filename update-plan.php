<?php

/**
 * Unishipper Small Update Plan
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_unishippers_small_activate_hit_to_update_plan', 'unishippers_small_activate_hit_to_update_plan');
add_action('wp_ajax_nopriv_unishippers_small_activate_hit_to_update_plan', 'unishippers_small_activate_hit_to_update_plan');

/**
 * Activate Unishipper SMALL
 */
function unishippers_small_activate_hit_to_update_plan($network_wide = null)
{
    if ( is_multisite() && $network_wide ) {

        foreach (get_sites(['fields'=>'ids']) as $blog_id) {
            switch_to_blog($blog_id);
            $domain = unishippers_small_get_domain();

            $index = 'small-package-quotes-unishippers-edition/small-package-quotes-unishippers-edition.php';
            $plugin_info = get_plugins();
            $plugin_version = isset($plugin_info[$index]['Version']) ? $plugin_info[$index]['Version'] : '';

            $plugin_dir_url = plugin_dir_url(__FILE__) . 'en-hit-to-update-plan.php';
            $post_data = array(
                'platform' => 'wordpress',
                'carrier' => '62',
                'store_url' => $domain,
                'webhook_url' => $plugin_dir_url,
                'plugin_version' => $plugin_version
            );

            $license_key = get_option('unishepper_small_licence_key');
            strlen($license_key) > 0 ? $post_data['license_key'] = $license_key : '';

            $url = UNISHIPPERS_DOMAIN_HITTING_URL . "/web-hooks/subscription-plans/create-plugin-webhook.php?";
            $response = wp_remote_get($url,
                array(
                    'method' => 'GET',
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => $post_data,
                )
            );
            $output = wp_remote_retrieve_body($response);
            $response = json_decode($output, TRUE);

            $plan = isset($response['pakg_group']) ? $response['pakg_group'] : '';
            $expire_day = isset($response['pakg_duration']) ? $response['pakg_duration'] : '';
            $expiry_date = isset($response['expiry_date']) ? $response['expiry_date'] : '';
            $plan_type = isset($response['plan_type']) ? $response['plan_type'] : '';
            $packaging_type = isset($response['packaging_type']) ? $response['packaging_type'] : '';

            if ($response['pakg_price'] == '0') {
                $plan = '0';
            }

            update_option('unishepper_small_package', "$plan");
            update_option('unishepper_small_package_expire_days', "$expire_day");
            update_option('unishepper_small_package_expire_date', "$expiry_date");
            update_option('unishepper_small_quotes_store_type', "$plan_type");
            update_option('unishepper_small_packaging_type', $packaging_type);

            unishippers_en_check_unishepper_small_plan_on_product_detail();
            restore_current_blog();
        }

    } else {
        $domain = unishippers_small_get_domain();

        $index = 'small-package-quotes-unishippers-edition/small-package-quotes-unishippers-edition.php';
        $plugin_info = get_plugins();
        $plugin_version = isset($plugin_info[$index]['Version']) ? $plugin_info[$index]['Version'] : '';

        $plugin_dir_url = plugin_dir_url(__FILE__) . 'en-hit-to-update-plan.php';
        $post_data = array(
            'platform' => 'wordpress',
            'carrier' => '62',
            'store_url' => $domain,
            'webhook_url' => $plugin_dir_url,
            'plugin_version' => $plugin_version,
        );

        $license_key = get_option('unishepper_small_licence_key');
        strlen($license_key) > 0 ? $post_data['license_key'] = $license_key : '';

        $url = UNISHIPPERS_DOMAIN_HITTING_URL . "/web-hooks/subscription-plans/create-plugin-webhook.php?";
        $response = wp_remote_get($url,
            array(
                'method' => 'GET',
                'timeout' => 60,
                'redirection' => 5,
                'blocking' => true,
                'body' => $post_data,
            )
        );
        $output = wp_remote_retrieve_body($response);
        $response = json_decode($output, TRUE);

        $plan = isset($response['pakg_group']) ? $response['pakg_group'] : '';
        $expire_day = isset($response['pakg_duration']) ? $response['pakg_duration'] : '';
        $expiry_date = isset($response['expiry_date']) ? $response['expiry_date'] : '';
        $plan_type = isset($response['plan_type']) ? $response['plan_type'] : '';
        $packaging_type = isset($response['packaging_type']) ? $response['packaging_type'] : '';

        if ($response['pakg_price'] == '0') {
            $plan = '0';
        }

        update_option('unishepper_small_package', "$plan");
        update_option('unishepper_small_package_expire_days', "$expire_day");
        update_option('unishepper_small_package_expire_date', "$expiry_date");
        update_option('unishepper_small_quotes_store_type', "$plan_type");
        update_option('unishepper_small_packaging_type', $packaging_type);

        unishippers_en_check_unishepper_small_plan_on_product_detail();
    }

}

function unishippers_en_check_unishepper_small_plan_on_product_detail()
{

    $hazardous_feature_PD = 0;
    $insurance_feature_PD = 0;
    $dropship_feature_PD = 1;

//  Hazardous Material
    $hazardous_material = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'hazardous_material');

    if (!is_array($hazardous_material)) {
        $hazardous_feature_PD = 1;
    }

//  Insurance Fee
    $action_insurance = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'insurance_fee');
    if (!is_array($action_insurance)) {
        $insurance_feature_PD = 1;
    }

//  Dropship
    if (get_option('unishepper_small_quotes_store_type') == "1") {
        $action_dropship = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'multi_dropship');
        if (!is_array($action_dropship)) {
            $dropship_feature_PD = 1;
        } else {
            $dropship_feature_PD = 0;
        }
    }

    update_option('eniture_plugin_21', array('unishepper_small_package' => array('plugin_name' => 'Small Package Quotes - Unishipper Edition', 'multi_dropship' => $dropship_feature_PD, 'insurance_fee' => $insurance_feature_PD, 'hazardous_material' => $hazardous_feature_PD)));
}

/**
 * Deactivate Unishipper SMALL
 */
function unishippers_small_deactivate_hit_to_update_plan($network_wide = null)
{
    if ( is_multisite() && $network_wide ) {

        foreach (get_sites(['fields'=>'ids']) as $blog_id) {
            switch_to_blog($blog_id);
            delete_option('eniture_plugin_21');
            delete_option('unishepper_small_package');
            delete_option('unishepper_small_package_expire_days');
            delete_option('unishepper_small_package_expire_date');
            delete_option('unishepper_small_quotes_store_type');
            restore_current_blog();
        }

    } else {
        delete_option('eniture_plugin_21');
        delete_option('unishepper_small_package');
        delete_option('unishepper_small_package_expire_days');
        delete_option('unishepper_small_package_expire_date');
        delete_option('unishepper_small_quotes_store_type');
    }

}

/**
 * Get Unishipper Small Plan
 * @return string
 */
function unishippers_small_plan_name()
{
    $plan = get_option('unishepper_small_package');
    $expire_days = get_option('unishepper_small_package_expire_days');
    $expiry_date = get_option('unishepper_small_package_expire_date');
    $plan_name = "";

    switch ($plan) {
        case 3:
            $plan_name = "Advanced Plan";
            break;
        case 2:
            $plan_name = "Standard Plan";
            break;
        case 1:
            $plan_name = "Basic Plan";
            break;
        case 0:
            $plan_name = "Trial Plan";
            break;
    }
    $package_array = array(
        'plan_number' => $plan,
        'plan_name' => $plan_name,
        'expire_days' => $expire_days,
        'expiry_date' => $expiry_date
    );
    return $package_array;
}

/**
 * Show Unishipper Small Plan Notice
 * @return string
 */
function unishippers_small_plan_notice()
{

    if (isset($_GET['tab']) && ($_GET['tab'] == "unishipper_small")) {
        $plan_number = get_option('unishepper_small_package');
        $store_type = get_option('unishepper_small_quotes_store_type');

        if ($store_type == "1" || $store_type == "0" && ($plan_number == "0" || $plan_number == "1" || $plan_number == "2" || $plan_number == "3")) {

            $plan_package = unishippers_small_plan_name();

            if (isset($plan_package) && !empty($plan_package)) {

                if (isset($plan_package['plan_number']) && $plan_package['plan_number'] == '0') {

                    echo '<div class="notice notice-success is-dismissible">
                       <p> You are currently on the ' . esc_attr( $plan_package['plan_name'] ) . '. Your plan will be expire within ' . esc_attr( $plan_package['expire_days'] ) . ' days and plan renews on ' . esc_attr( $plan_package['expiry_date'] ) .'<a href="javascript:void(0)" data-action="unishippers_small_activate_hit_to_update_plan" onclick="en_update_plan(this);">Click here</a> to refresh the plan.</p>
                    </div>';
                } else if (isset($plan_package['plan_number']) && $plan_package['plan_number'] == '1' || $plan_package['plan_number'] == '2' || $plan_package['plan_number'] == '3') {

                    echo '<div class="notice notice-success is-dismissible">
                        <p>You are currently on the ' . esc_attr( $plan_package['plan_name']) . '. The plan renews on ' . esc_attr( $plan_package['expiry_date']) .' <a href="javascript:void(0)" data-action="unishippers_small_activate_hit_to_update_plan" onclick="en_update_plan(this);">Click here</a> to refresh the plan.</p>
                    </div>';
                } else {
                    echo '<div class="notice notice-warning is-dismissible">
                    <p>Your currently plan subscription is inactive. <a href="javascript:void(0)" data-action="unishippers_small_activate_hit_to_update_plan" onclick="en_update_plan(this);"> Click here</a> to refresh the plan to check the subscription status. If the subscription status remains inactive, log into eniture.com and update your license.</p>
                    </div>';
                }
            }
        }

    }
}

add_action('admin_notices', 'unishippers_small_plan_notice');
