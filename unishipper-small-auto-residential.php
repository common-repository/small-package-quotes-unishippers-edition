<?php

if(!defined("ABSPATH"))
{
    exit();
}

if(!class_exists("Unishippers_Small_Auto_Residential_Detection"))
{
    class Unishippers_Small_Auto_Residential_Detection{
        
        public $label_sfx_arr;
        
        public function __construct()
        {
            $this->label_sfx_arr = array();
        }
        
        public function filter_label_sufex_array_unishepper_small($result)
        {               
            (isset($result->residentialStatus) && ($result->residentialStatus == "r")) ? array_push($this->label_sfx_arr, "R") : "";
            (isset($result->liftGateStatus) && ($result->liftGateStatus == "l")) ? array_push($this->label_sfx_arr, "L") : "";
            
            (isset($result->one_rate_pricing->residentialStatus) && ($result->one_rate_pricing->residentialStatus == "r")) ? array_push($this->label_sfx_arr, "R") : "";
            (isset($result->one_rate_pricing->liftGateStatus) && ($result->one_rate_pricing->liftGateStatus == "l")) ? array_push($this->label_sfx_arr, "L") : "";
            
            (isset($result->home_ground_pricing->residentialStatus) && ($result->home_ground_pricing->residentialStatus == "r")) ? array_push($this->label_sfx_arr, "R") : "";
            (isset($result->home_ground_pricing->liftGateStatus) && ($result->home_ground_pricing->liftGateStatus == "l")) ? array_push($this->label_sfx_arr, "L") : "";
            
            (isset($result->weight_based_pricing->residentialStatus) && ($result->weight_based_pricing->residentialStatus == "r")) ? array_push($this->label_sfx_arr, "R") : "";
            (isset($result->weight_based_pricing->liftGateStatus) && ($result->weight_based_pricing->liftGateStatus == "l")) ? array_push($this->label_sfx_arr, "L") : "";
            return array_unique($this->label_sfx_arr);
        }
    }
    
    new Unishippers_Small_Auto_Residential_Detection();
}

