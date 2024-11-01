<?php

/**
 * Unishipper Small Tab Class
 * 
 * @package     Unishipper Small Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Woocommerce Setting Tab Class
 */
class WC_Settings_Unishipper_Small extends WC_Settings_Page {

    /**
     * COnstructor
     */
    public function __construct() {
        $this->id = 'unishipper_small';
        add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
        add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));
        add_action('woocommerce_settings_' . $this->id, array($this, 'output'));
        add_action('woocommerce_settings_save_' . $this->id, array($this, 'save'));
    }

    /**
     * Unishipper Small Setting Tab For Woocommerce
     * @param array $settings_tabs
     * @return array
     */
    public function add_settings_tab($settings_tabs) {
        $settings_tabs[$this->id] = __('Unishippers', 'eniture-unishippers-small-quotes');
        return $settings_tabs;
    }

    /**
     * Unishipper Small Setting Sections
     */
    public function get_sections() {
        $sections = array(
            '' => __('Connection Settings', 'woocommerce-settings-unishepper_small'),
            'section-1' => __('Quote Settings', 'woocommerce-settings-unishepper_small'),
            'section-2' => __('Warehouses', 'woocommerce-settings-unishepper_small'),
            'shipping-rules' => __('Shipping Rules', 'woocommerce-settings-unishepper_small'),
            'section-4' => __('FreightDesk Online', 'woocommerce-settings-unishepper_small'),
            'section-5' => __('Validate Addresses', 'woocommerce-settings-unishepper_small'),
            'section-3' => __('User Guide', 'woocommerce-settings-unishepper_small')
        );

        // Logs data
        $enable_logs = get_option('unishepper_small_enable_logs');
        if ($enable_logs == 'yes') {
            $sections['en-logs'] = 'Logs';
        }

        $sections = apply_filters('en_woo_addons_sections', $sections, unishippers_en_woo_plugin_unishepper_small);
        return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
    }

    /**
     * Unishipper Small Warehouse Tab
     */
    public function unishepper_small_warehouse() {
        require_once 'warehouse-dropship/wild/warehouse/warehouse_template.php';
        require_once 'warehouse-dropship/wild/dropship/dropship_template.php';
    }

    /**
     * Unishipper Small User Guide Tab
     */
    public function unishepper_small_user_guide() {
        include_once( 'template/guide.php' );
    }

    /**
     * Get Settings
     * @param $section
     * @return array
     */
    public function get_settings($section = null) {
        $unishepper_small_con_settings = new Unishippers_Small_Connection_Settings();
        ob_start();
        switch ($section) {
            case 'section-0' :
                $settings = $unishepper_small_con_settings->unishepper_small_con_setting();
                break;
            case 'section-1' :
                $unishepper_small_qsettings = new Unishippers_Unishipper_Small_Quote_Settings();
                $settings = $unishepper_small_qsettings->unishepper_small_quote_settings_tab();
                break;
            case 'section-2':
                $this->unishepper_small_warehouse();
                $settings = array();
                break;
            case 'shipping-rules' :
                include_once('shipping-rules/shipping-rules-template.php');
                $settings = [];
                break;
            case 'section-3' :
                $this->unishepper_small_user_guide();
                $settings = array();
                break;

            case 'section-4' :
                $this->freightdesk_online_section();
                $settings = [];
                break;

            case 'section-5' :
                $this->validate_addresses_section();
                $settings = [];
                break;

            case 'en-logs' :
                require_once 'logs/en-logs.php';
                $settings = [];
                break;

            default:
                $settings = $unishepper_small_con_settings->unishepper_small_con_setting();

                break;
        }

        $settings = apply_filters('en_woo_addons_settings', $settings, $section, unishippers_en_woo_plugin_unishepper_small);
        $settings = $this->avaibility_addon($settings);
        return apply_filters('woocommerce-settings-unishepper_small', $settings, $section);
    }

    /**
     * avaibility_addon
     * @param array type $settings
     * @return array type
     */
    function avaibility_addon($settings) {
        if (is_plugin_active('residential-address-detection/residential-address-detection.php')) {
            unset($settings['avaibility_auto_residential']);
        }
        if (is_plugin_active('standard-box-sizes/en-standard-box-sizes.php')) {
            unset($settings['avaibility_box_sizing']);
            
            $subscription_packages_response = get_option('subscription_packages_response');
            $suspend_automatic_detection = get_option('suspend_automatic_detection_of_box_sizing');
            if ($subscription_packages_response != "yes" || $suspend_automatic_detection == "yes") {
//                      One Rate   
                $one_rate_services = apply_filters('unishepper_small_one_rate_services', array());
                $display = (!empty($one_rate_services)) ? "block" : 'none';
                echo '<div class="notice notice-error one_rate_error" style="display: ' . esc_attr($display) . '"><p> Standard Box size feature is required for the One Rate services. Click <a target="_blank" href="https://eniture.com/woocommerce-standard-box-sizes/">here</a> to add the Standard Box Sizes module. (<a target="_blank" href="https://eniture.com/woocommerce-standard-box-sizes/#documentation">Learn more</a>)</p></div>';
            }
        } else {
//                  One Rate   
            $one_rate_services = apply_filters('unishepper_small_one_rate_services', array());
            $display = (!empty($one_rate_services)) ? "block" : 'none';
            echo '<div class="notice notice-error one_rate_error" style="display: ' . esc_attr($display) . '"><p> Standard Box size feature is required for the One Rate services. Click <a target="_blank" href="https://eniture.com/woocommerce-standard-box-sizes/">here</a> to add the Standard Box Sizes module. (<a target="_blank" href="https://eniture.com/woocommerce-standard-box-sizes/#documentation">Learn more</a>)</p></div>';
        }

        return $settings;
    }

    /**
     * Output
     * @global $current_section
     */
    public function output() {
        global $current_section;
        $settings = $this->get_settings($current_section);
        WC_Admin_Settings::output_fields($settings);
    }

    /**
     * Unishipper Small Save Settings
     */
    public function save() {
        global $current_section;
        $settings = $this->get_settings($current_section);

        if (isset($_POST['unishippers_small_orderCutoffTime']) && strlen(sanitize_text_field( wp_unslash($_POST['unishippers_small_orderCutoffTime']))) > 0) {
            $time24Formate = $this->getTimeIn24Hours(sanitize_text_field( wp_unslash($_POST['unishippers_small_orderCutoffTime'])));
            $_POST['unishippers_small_orderCutoffTime'] = $time24Formate;
        }

        // backup rates
        $backup_rates_fields = ['backup_rates_fixed_rate_unishippers_small', 'backup_rates_cart_price_percentage_unishippers_small', 'backup_rates_weight_function_unishippers_small'];
        foreach ($backup_rates_fields as $field) {
            if (isset($_POST[$field])) update_option($field, $_POST[$field]);
        }
        
        WC_Admin_Settings::save_fields($settings);
    }

    /**
     * @param $timeStr
     * @return false|string
     */
    public function getTimeIn24Hours($timeStr) {
        $cutOffTime = explode(' ', $timeStr);
        $hours = $cutOffTime[0];
        $separator = $cutOffTime[1];
        $minutes = $cutOffTime[2];
        $meridiem = $cutOffTime[3];
        $cutOffTime = "{$hours}{$separator}{$minutes} $meridiem";
        return date("H:i", strtotime($cutOffTime));
    }

    /**
     * FreightDesk Online section
     */
    public function freightdesk_online_section()
    {

        include_once('fdo/freightdesk-online-section.php');
    }

    /**
     * Validate Addresses Section
     */
    public function validate_addresses_section()
    {
        include_once('fdo/validate-addresses-section.php');
    }

}

return new WC_Settings_Unishipper_Small();
