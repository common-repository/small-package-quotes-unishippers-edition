<?php

/**
 * WWE Small Get Distance
 *
 * @package     WWE Small Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Distance Request Class
 */
class Unishippers_Get_unishepper_small_distance
{

    function __construct()
    {
        add_filter("en_wd_get_address", array($this, "unishepper_small_address"), 10, 2);
    }

    /**
     * Get Address Upon Access Level
     * @param $map_address
     * @param $accessLevel
     */
    function unishepper_small_address($map_address, $accessLevel, $destinationZip = array())
    {

        $domain = unishippers_small_get_domain();
        $postData = array(
            'acessLevel' => $accessLevel,
            'address' => $map_address,
            'originAddresses' => (isset($map_address)) ? $map_address : "",
            'destinationAddress' => (isset($destinationZip)) ? $destinationZip : "",
            'eniureLicenceKey' => get_option('unishepper_small_licence_key'),
            'ServerName' => $domain,
        );
        $Unishipper_Small_Curl_Request = new Unishippers_Small_Curl_Request();
        $output = $Unishipper_Small_Curl_Request->unishepper_small_get_curl_response(UNISHIPPERS_DOMAIN_HITTING_URL . '/addon/google-location.php', $postData);

        return $output;
    }

}
