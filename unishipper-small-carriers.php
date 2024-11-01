<?php
/**
 * Includes Engine class
 */

if (!defined('ABSPATH')) 
{
    exit;
}

if (!class_exists("Unishippers_SmallCarriers")) 
{
    class Unishippers_SmallCarriers
    {
        
        /**
         * construct
         */
        public function __construct() 
        {
            add_filter('unishepper_small_domestic_services' , array($this , 'unishepper_small_domestic_services') , 10 , 1);
            add_filter('unishepper_small_international_services' , array($this , 'unishepper_small_international') , 10 , 1);
        }

        public function unishepper_small_domestic_services()
        {

            $services = array();

            if (get_option('unishepper_next_day_air') == 'yes') {
                $services['ND'] = ['name' => 'UPS Next Day Air', 'markup' => get_option('unishepper_next_day_air_markup')];
            }

            if (get_option('unishepper_small_next_day_air_saver') == 'yes') {
                $services['ND4'] = ['name' => 'UPS Next Day Air Saver', 'markup' => get_option('unishepper_small_next_day_air_saver_markup')];
            }

            if (get_option('unishepper_small_next_day_air_early') == 'yes') {
                $services['ND5'] = ['name' => 'UPS Next Day Air Early A.M.', 'markup' => get_option('unishepper_small_next_day_air_early_markup')];
            }

            if (get_option('unishepper_small_2_day_air') == 'yes') {
                $services['SC'] = ['name' => 'UPS 2nd Day Air', 'markup' => get_option('unishepper_small_2_day_air_markup')];
            }

            if (get_option('unishepper_small_2_day_air_am') == 'yes') {
                $services['SC25'] = ['name' => 'UPS 2nd Day Air A.M.', 'markup' => get_option('unishepper_small_2_day_air_am_markup')];
            }

            if (get_option('unishepper_small_3_day_select') == 'yes') {
                $services['SC3'] = ['name' => 'UPS 3 Day Select', 'markup' => get_option('unishepper_small_3_day_select_markup')];
            }

            if (get_option('unishepper_small_ups_ground') == 'yes') {
                $services['SG'] = ['name' => 'UPS Ground', 'markup' => get_option('unishepper_small_ups_ground_markup')];
            }

            if (get_option('unishepper_small_ups_ground_residential_delivery') == 'yes') {
                $services['SGR'] = ['name' => 'UPS Ground (Residential Delivery)', 'markup' => get_option('unishepper_small_ups_ground_residential_delivery_markup')];
            }
            
            if (get_option('unishepper_small_sat_ups_next_day_air') == 'yes') {
                $services['SND'] = ['name' => 'Saturday - UPS Next Day Air', 'markup' => get_option('unishepper_small_sat_ups_next_day_air_markup')];
            }
            
            if (get_option('unishepper_small_sat_ups_next_day_air_early') == 'yes') {
                $services['SND5'] = ['name' => 'Saturday - UPS Next Day Air Early A.M.', 'markup' => get_option('unishepper_small_sat_ups_next_day_air_early_markup')];
            }
            
            if (get_option('unishepper_small_sat_ups_2_day_air') == 'yes') {
                $services['SSC'] = ['name' => 'Saturday - UPS 2nd Day Air', 'markup' => get_option('unishepper_small_sat_ups_2_day_air_markup')];
            }

            return $services;
        }

        public function unishepper_small_international()
        {
            $services = array();

            if (get_option('unishepper_small_worldwide_express') == 'yes') {
                $services['ZZ1'] = ['name' => 'Worldwide Express', 'markup' => get_option('unishepper_small_worldwide_express_markup')];
            }

            if (get_option('unishepper_small_worldwide_expedited') == 'yes') {
                $services['ZZ2'] = ['name' => 'Worldwide Expedited', 'markup' => get_option('unishepper_small_worldwide_expedited_markup')];
            }

            if (get_option('unishepper_small_worldwide_saver') == 'yes') {
                $services['ZZ90'] = ['name' => 'Worldwide Saver', 'markup' => get_option('unishepper_small_worldwide_saver_markup')];
            }

            if (get_option('unishepper_small_standard_canada') == 'yes') {
                $services['ZZ11'] = ['name' => 'Standard (Canada)', 'markup' => get_option('unishepper_small_standard_canada_markup')];
            }

            return $services;
        }
    }

    new Unishippers_SmallCarriers();
}