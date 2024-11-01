<?php

/**
 * Class Unishippers_Small_Curl_Request
 *
 * @package     Unishipper Small Quotes
 * @subpackage  Curl Call
 * @author      Eniture-Technology
 */


if (!defined('ABSPATH')) {
    exit; // exit if direct access
}

/**
 * Class to call curl request
 */
class Unishippers_Small_Curl_Request
{
    /**
     * Get Curl Response
     * @param  $url curl hitting url
     * @param  $postData post data to get response
     * @return json
     */

    function unishepper_small_get_curl_response($url, $postData)
    {
        if (!empty($url) && !empty($postData)) {
            $field_string = http_build_query($postData);

            // Eniture debug mood
            do_action("eniture_debug_mood", "Build Query (Unishipper Small)", $field_string);

            $response = wp_remote_post($url,
                array(
                    'method' => 'POST',
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => $field_string,
                )
            );

            $output = wp_remote_retrieve_body($response);

            return $output;
        }
    }

}