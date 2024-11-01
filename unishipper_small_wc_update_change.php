<?php
/**
 * Unishipper Small Check Woo Update
 * 
 * @package     Unishipper Small Quotes
 * @author      Eniture-Technology
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}
    
/**
 * YRC Woocommerce Class for new and old functions
 */

class Unishippers_Small_Woo_Update_Changes 
{
    /** $WooVersion */ public $WooVersion;
    /**
     * Constructor
     */
    function __construct() 
    {
        if (!function_exists('get_plugins'))
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $plugin_folder    = get_plugins('/' . 'woocommerce');
        $plugin_file      = 'woocommerce.php';
        $this->WooVersion = $plugin_folder[$plugin_file]['Version'];
        
    }
    /**
     * Get Postcode
     */
    function unishepper_small_postcode()
    { 
        $sPostCode = "";
        switch ($this->WooVersion) 
        {  
            case ($this->WooVersion <= '2.7'):
                $sPostCode = WC()->customer->get_postcode();
                break;
            
            case ($this->WooVersion >= '3.0'):
                $sPostCode = WC()->customer->get_billing_postcode();
                break;

            default:
                break;
        }
        return $sPostCode;
    }
    /**
     * Get State
     */
    function unishepper_small_getState()
    { 
        $sState = "";
        switch ($this->WooVersion) 
        {  
            case ($this->WooVersion <= '2.7'):
                $sState = WC()->customer->get_state();
                break;
            
            case ($this->WooVersion >= '3.0'):
                $sState = WC()->customer->get_billing_state();
                break;

            default:
                break;
        }
        return $sState;
    }
    /**
     * Get City
     */
    function unishepper_small_getCity()
    { 
        $sCity = "";
        switch ($this->WooVersion) 
        {  
            case ($this->WooVersion <= '2.7'):
                $sCity = WC()->customer->get_city();
                break;
            
            case ($this->WooVersion >= '3.0'):
                $sCity = WC()->customer->get_billing_city();
                break;

            default:
                break;
        }
        return $sCity;
    }
    /**
     * Get Country
     */
    function unishepper_small_getCountry()
    { 
        $sCountry = "";
        switch ($this->WooVersion) 
        {  
            case ($this->WooVersion <= '2.7'):
                $sCountry = WC()->customer->get_country();
                break;
            
            case ($this->WooVersion >= '3.0'):
                $sCountry = WC()->customer->get_billing_country();
                break;

            default:
                break;
        }
        return $sCountry;
    }
    
    /**
    * Address
    * @return string type
    */
    function unishepper_small_getAddress1()
    { 
        $sAddress = "";
        switch ($this->WooVersion) 
        {  
            case ($this->WooVersion <= '2.7'):
                $sAddress = WC()->customer->get_address();
                break;
            case ($this->WooVersion >= '3.0'):
                $sAddress = WC()->customer->get_billing_address_1();
                break;

            default:
                break;
        }
        return $sAddress;
    }
    
}