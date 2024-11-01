<?php

/**
 * transit days
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("Unishippers_EnUnishipperSmallTransitDays")) {

    class Unishippers_EnUnishipperSmallTransitDays
    {
        public function unishipper_enable_disable_service_ground($service)
        {
            $transit_day_type = get_option('restrict_radio_btn_transit_unishepper_small'); // Get value of check box to see which one is checked
            $days_to_restrict = get_option('restrict_days_transit_package_unishepper_small');
            $transit_days = apply_filters('unishippers_small_quotes_plans_suscription_and_features', 'transit_days');
            if (!is_array($transit_days) && strlen($days_to_restrict) > 0 && strlen($transit_day_type) > 0) {
                if ($service->serviceType == "SG" && isset($service->$transit_day_type) && ($service->$transit_day_type > $days_to_restrict)) {
                    unset($service);
                }
            }

            return isset($service) ? $service : (object)[];  // return the encoded json response
        }
    }
}