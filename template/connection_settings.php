<?php
/**
 * Unishipper Small Connection Settings
 *
 * @package     Unishipper Small Quotes
 * @author      Eniture-Technology
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Unishipper Small Connection Settings Tab Class
 */
class Unishippers_Small_Connection_Settings
{

    /**
     * Connection Settings Fields
     */

    public function unishepper_small_con_setting()
    {
        echo '<div class="unishepper_small_connection_section">';
        $default_api_endpoint = !empty(get_option('unishepper_username')) ? 'unishippers_small_old_api' : 'unishippers_small_new_api';

        $settings = array(
            'section_title_unishepper_small' => array(
                'name' => '',
                'type' => 'title',
                'desc' => '<br> ',
                'id' => 'unishepper_small_connection_title',
            ),
            'api_endpoint_unishippers_small' => array(
                'name' => __('Which API will you connect to? ', 'eniture-unishippers-small-quotes'),
                'type' => 'select',
                'default' => $default_api_endpoint,
                'id' => 'api_endpoint_unishippers_small',
                'options' => array(
                    'unishippers_small_old_api' => __('Legacy API', 'eniture-unishippers-small-quotes'),
                    'unishippers_small_new_api' => __('New API', 'eniture-unishippers-small-quotes'),
                )
            ),

            // New API
            'unishippers_small_client_id' => array(
                'name' => __('Client ID ', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'desc' => '',
                'id' => 'unishippers_small_client_id',
                'class' => 'unishippers_small_new_api_field'
            ),

            'unishippers_small_client_secret' => array(
                'name' => __('Client Secret ', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'desc' => '',
                'id' => 'unishippers_small_client_secret',
                'class' => 'unishippers_small_new_api_field'
            ),
            'unishippers_small_new_api_username' => array(
                'name' => __('Username ', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'desc' => '',
                'id' => 'unishippers_small_new_api_username',
                'class' => 'unishippers_small_new_api_field'
            ),
            'unishippers_small_new_api_password' => array(
                'name' => __('Password ', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'desc' => '',
                'id' => 'unishippers_small_new_api_password',
                'class' => 'unishippers_small_new_api_field'
            ),

            // Old API 
            'unishepper_small_customer_account_number' => array(
                'name' => __('Unishippers Customer Number ', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'desc' => '',
                'id' => 'unishepper_small_customer_account_number',
                'class' => 'unishippers_small_old_api_field'
            ),

            'unishepper_ups_account_number' => array(
                'name' => __('UPS Account Number ', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'desc' => '',
                'id' => 'unishepper_ups_account_number',
                'class' => 'unishippers_small_old_api_field'
            ),

            'unishepper_username' => array(
                'name' => __('Username ', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'desc' => '',
                'id' => 'unishepper_username',
                'class' => 'unishippers_small_old_api_field'
            ),

            'unishepper_password' => array(
                'name' => __('Password ', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'desc' => '',
                'id' => 'unishepper_password',
                'class' => 'unishippers_small_old_api_field'
            ),

            'auth_key_unishepper_small' => array(
                'name' => __('Request Key ', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'desc' => '',
                'id' => 'unishepper_small_auth_key',
                'class' => 'unishippers_small_old_api_field'
            ),

            'licence_key_unishepper_small' => array(
                'name' => __('Eniture API Key ', 'eniture-unishippers-small-quotes'),
                'type' => 'text',
                'desc' => __('Obtain a Eniture API Key from <a href="https://eniture.com/woocommerce-unishippers-small-package-plugin/" target="_blank" >eniture.com </a>', 'eniture-unishippers-small-quotes'),
                'id' => 'unishepper_small_licence_key'
            ),

            'section_end_unishepper_small' => array(
                'type' => 'sectionend',
                'id' => 'unishepper_small_licence_key'
            ),
        );

        return $settings;
    }
}