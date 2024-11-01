<?php
/**
 * Unishipper Small Test Connection
 * 
 * @package     Unishipper Small Quotes
 * @author      Eniture-Technology
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

/**
 * Unishipper Small Test Connection AJAX Request
 */

add_action( 'wp_ajax_nopriv_unishipper_small_test_connection', 'unishippers_test_submit' );
add_action( 'wp_ajax_unishipper_small_test_connection', 'unishippers_test_submit' );
/**
 * Test Connection FUnction
 */
function unishippers_test_submit() 
{
    $auth                   = ( isset( $_POST['unishepper_small_auth'] ) )       ? sanitize_text_field( wp_unslash($_POST['unishepper_small_auth'])): '';
    $ups_account_number     = ( isset( $_POST['unishepper_ups_account_number'] ) )   ? sanitize_text_field( wp_unslash($_POST['unishepper_ups_account_number']))   : ''; 
    $customer_acc_number    = ( isset( $_POST['unishepper_small_customer_acc_number'] ) ) ? sanitize_text_field( wp_unslash($_POST['unishepper_small_customer_acc_number'])) : ''; 
    $username               = ( isset( $_POST['unishepper_username'] ) )      ? sanitize_text_field( wp_unslash($_POST['unishepper_username']) ) : ''; 
    $password               = ( isset( $_POST['unishepper_password'] ) )      ? sanitize_text_field( wp_unslash($_POST['unishepper_password']) ) : ''; 
    $licence_key            = ( isset( $_POST['unishepper_small_license'] ) )    ? sanitize_text_field( wp_unslash($_POST['unishepper_small_license']) ) : ''; 
    $client_id              = (isset($_POST['client_id'])) ? sanitize_text_field( wp_unslash($_POST['client_id'])) : '';
    $client_secret          = (isset($_POST['client_secret'])) ? sanitize_text_field( wp_unslash($_POST['client_secret'])) : '';
    $domain                 = unishippers_small_get_domain();

    $api_end_point = isset($_POST['api_end_point']) ? sanitize_text_field( wp_unslash($_POST['api_end_point'])) : '';

    if ($api_end_point == 'unishippers_small_new_api') {
        $data = array(
            'platform'                      => 'WordPress',
            'ApiVersion'                    => '2.0',
            'isUnishipperNewApi'            => 'yes',
            'requestFromUnishippersSmall'   => '1',
            'clientId'                      => $client_id,
            'clientSecret'                  => $client_secret,
            'speed_freight_username'        => $username,
            'speed_freight_password'        => $password,
            'plugin_domain_name'            => $domain,
            'plugin_licence_key'            => $licence_key,
        );
    } else {
        $data = array(
            'carrierName'                   => 'unisheppers',
            'plateform'                     => 'WordPress',
            'carrier_mode'                  => 'test',
            'unishipperscustomernumber'     => $customer_acc_number,
            'upsaccountnumber'              => $ups_account_number,
            'username'                      => $username,
            'password'                      => $password,
            'requestkey'                    => $auth,
            'sever_name'                    => $domain,
            'licence_key'                   => $licence_key
        );
    }
    
    $url = UNISHIPPERS_DOMAIN_HITTING_URL . '/index.php';
    if ($api_end_point == 'unishippers_small_new_api') {
        $url = UNISHIPPERS_NEW_API_DOMAIN_HITTING_URL . '/carriers/wwe-small/speedshipTest.php';
    }

    $field_string = http_build_query($data);
    $response = wp_remote_post($url,
        array(
            'method' => 'POST',
            'timeout' => 60,
            'redirection' => 5,
            'blocking' => true,
            'body' => $field_string,
        )
    );
    
    $Response = wp_remote_retrieve_body($response);
    echo wp_json_encode(json_decode($Response));
    exit();
}
