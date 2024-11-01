<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Unishippers_EnUniSmallCouponAPI')) {
    class Unishippers_EnUniSmallCouponAPI
    {
        public function __construct()
        {
            add_action('rest_api_init', [$this, 'en_rest_api_init']);
            add_action('wp_ajax_en_unishippers_small_fdo_connection_status_refresh', [$this, 'en_unishippers_small_fdo_connection_status_refresh']);
            add_action('wp_ajax_nopriv_en_unishippers_small_fdo_connection_status_refresh', [$this, 'en_unishippers_small_fdo_connection_status_refresh']);

            add_action('wp_ajax_en_unishippers_small_fdo_apply_coupon', [$this, 'en_unishippers_small_fdo_apply_coupon']);
            add_action('wp_ajax_nopriv_en_unishippers_small_fdo_apply_coupon', [$this, 'en_unishippers_small_fdo_apply_coupon']);

            add_action('wp_ajax_en_unishippers_small_va_connection_status_refresh', [$this, 'en_unishippers_small_va_connection_status_refresh']);
            add_action('wp_ajax_nopriv_en_unishippers_small_va_connection_status_refresh', [$this, 'en_unishippers_small_va_connection_status_refresh']);

            add_action('wp_ajax_en_unishippers_small_va_apply_coupon', [$this, 'en_unishippers_small_va_apply_coupon']);
            add_action('wp_ajax_nopriv_en_unishippers_small_va_apply_coupon', [$this, 'en_unishippers_small_va_apply_coupon']);
            add_action('wp_ajax_nopriv_unishippers_s_fd', [$this, 'unishippers_s_fd_api']);
            add_action('wp_ajax_unishippers_s_fd', [$this, 'unishippers_s_fd_api']);
            add_action('rest_api_init', [$this, 'en_rest_api_init_status_unishippers_s']);
        }

        // initialize the en-fdo-coupon hook
        public function en_rest_api_init()
        {
            register_rest_route('fdo-coupon-update', '/v1', array(
                'methods' => 'POST',
                'callback' => [$this, 'en_fdo_coupon_data_update'],
                'permission_callback' => '__return_true'
            ));

            register_rest_route('fdo-coupon-update', '/carriers-list', array(
                'methods' => 'GET',
                'callback' => [$this, 'en_fdo_get_carriers_list'],
                'permission_callback' => '__return_true'
            ));

            register_rest_route('va-coupon-update', '/v1', array(
                'methods' => 'POST',
                'callback' => [$this, 'en_va_coupon_data_update'],
                'permission_callback' => '__return_true'
            ));
        }

        /**
         * Update FDO coupon data
         * @param array $request
         * @return array|void
         */
        public function en_fdo_coupon_data_update(WP_REST_Request $request)
        {
            $coupon_data = $request->get_body();
            $coupon_data_decoded = json_decode($coupon_data);
            if(!empty($coupon_data_decoded->promo)){
                update_option('en_fdo_coupon_data', $coupon_data);
            }

            return $this->get_active_discounted_carrier();
        }

        /**
         * Return active eniture's plugins those are qualifing for free coupon
         * @param array $request
         * @return array|void
         */
        public function en_fdo_get_carriers_list(WP_REST_Request $request)
        {
            return $this->get_active_discounted_carrier();
        }

        /**
         * Function that returns discounted active carriers
         */
        public function get_active_discounted_carrier(){
            $activaCarrArr = apply_filters('active_plugins', get_option('active_plugins'));

            $carriersArr['install_carriers'] = ['UNI_PL'];

            foreach ($activaCarrArr as $key => $carrier) {
                if(strpos($carrier, 'small-package-quotes-wwe-edition') !== false){ 
                    $carriersArr['install_carriers'][] = 'WWE_PL';
                }else if(strpos($carrier, 'ltl-freight-quotes-worldwide-express-edition') !== false){ 
                    $carriersArr['install_carriers'][] = 'WWE_LTL';
                }else if(strpos($carrier, 'ltl-freight-quotes-unishippers-edition') !== false){ 
                    $carriersArr['install_carriers'][] = 'Unishiper';
                }else if(strpos($carrier, 'ltl-freight-quotes-globaltranz-edition') !== false){ 
                    $carriersArr['install_carriers'][] = 'GTZ';
                }
            }

            return $carriersArr;
        }

        /**
         * Remove FDO old status
         */
        function en_unishippers_small_fdo_connection_status_refresh()
        {
            delete_option('en_fdo_coupon_data');
        }

        /**
         * Remove VA old status
         */
        function en_unishippers_small_va_connection_status_refresh()
        {
            delete_option('en_va_coupon_data');
        }

        /**
         * Update VA coupon data
         * @param array $request
         * @return array|void
         */
        public function en_va_coupon_data_update(WP_REST_Request $request)
        {
            $coupon_data = $request->get_body();
            $coupon_data_decoded = json_decode($coupon_data);
            if(!empty($coupon_data_decoded->promo)){
                update_option('en_va_coupon_data', $coupon_data);
            }

            return $this->get_active_discounted_carrier();
        }

        /**
         * FDO Apply coupon
         */
        function en_unishippers_small_fdo_apply_coupon()
        {
            $carriers = $this->get_active_discounted_carrier();
            if(count($carriers['install_carriers']) > 0){
                $carrierList = implode('|',$carriers['install_carriers']);
            }else{
                $carrierList = 'UNI_PL';
            }

            $data = array(
                'marketplace' => 'wp',
                'shop' => unishippers_small_get_domain(),
                'coupon' => isset($_POST['coupon']) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : 0,
                'carriers' => $carrierList
            );
        
            $url = UNISHIPPERS_FDO_COUPON_BASE_URL . "/apply_promo_code";
            $response = wp_remote_get($url,
                array(
                    'method' => 'GET',
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => $data,
                )
            );
            $response = wp_remote_retrieve_body($response);
            
            $response_deco = json_decode($response);
            if(isset($response_deco->status) && $response_deco->status && !empty($response_deco->promo)){
                update_option('en_fdo_coupon_data', $response);
                $resp = ['status' => 'success'];
            }else{
                $message = !empty($response_deco->message) ? $response_deco->message : 'An error occured while connecting to server, please try later';
                $resp = ['status' => 'error',
                        'message' => $message];
            }
            print_r(wp_json_encode($resp));
            exit();
        }

        /**
         * Validate addresses Apply coupon
         */
        function en_unishippers_small_va_apply_coupon()
        {
            $carriers = $this->get_active_discounted_carrier();
            if(count($carriers['install_carriers']) > 0){
                $carrierList = implode('|',$carriers['install_carriers']);
            }else{
                $carrierList = 'UNI_PL';
            }

            $data = array(
                'marketplace' => 'wp',
                'shop' => unishippers_small_get_domain(),
                'coupon' => isset($_POST['coupon']) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : 0,
                'carriers' => $carrierList
            );

            $encoded_data = http_build_query($data);
        
            $url = UNISHIPPERS_VA_COUPON_BASE_URL . "/apply_promo_code?".$encoded_data;

            $response = wp_remote_get($url,
                array(
                    'method' => 'GET',
                    'timeout' => 60,
                    'redirection' => 5,
                    'blocking' => true,
                    'body' => [],
                )
            );
            $response = wp_remote_retrieve_body($response);
            
            $response_deco = json_decode($response);
            if(isset($response_deco->status) && $response_deco->status && !empty($response_deco->promo)){
                update_option('en_va_coupon_data', $response);
                $resp = ['status' => 'success'];
            }else{
                $message = !empty($response_deco->message) ? $response_deco->message : 'An error occured while connecting to server, please try later';
                $resp = ['status' => 'error',
                        'message' => $message];
            }
            print_r(wp_json_encode($resp));
            exit();
        }
        // fdo va
        /**
         * Unishippers AJAX Request
         */
        function unishippers_s_fd_api()
        {
            $store_name = unishippers_small_get_domain();
            $company_id = isset($_POST['company_id']) ? sanitize_text_field( wp_unslash( $_POST['company_id'] ) ) : 0;
            $data = [
                'plateform'  => 'wp',
                'store_name' => $store_name,
                'company_id' => $company_id,
                'fd_section' => 'tab=unishipper_small&section=section-4',
            ];
            if (is_array($data) && count($data) > 0) {
                if(isset($_POST['disconnect']) && $_POST['disconnect'] != 'disconnect') {
                    $url =  'https://freightdesk.online/validate-company';
                }else {
                    $url = 'https://freightdesk.online/disconnect-woo-connection';
                }
                $response = wp_remote_post($url, [
                        'method' => 'POST',
                        'timeout' => 60,
                        'redirection' => 5,
                        'blocking' => true,
                        'body' => $data,
                    ]
                );
                $response = wp_remote_retrieve_body($response);
            }
            if(isset($_POST['disconnect']) && $_POST['disconnect'] == 'disconnect') {
                $result = json_decode($response);
                if ($result->status == 'SUCCESS') {
                    update_option('en_fdo_company_id_status', 0);
                }
            }
            echo wp_json_encode(json_decode($response));
            exit();
        }
        /**
         * update company id status
         */
        function en_rest_api_init_status_unishippers_s()
        {
            register_rest_route('fdo-company-id', '/update-status', array(
                'methods' => 'POST',
                'callback' => [$this, 'en_unishippers_s_fdo_data_status'],
                'permission_callback' => '__return_true'
            ));
        }

        /**
         * Update FDO coupon data
         * @param array $request
         * @return array|void
         */
        function en_unishippers_s_fdo_data_status(WP_REST_Request $request)
        {
            $status_data = $request->get_body();
            $status_data_decoded = json_decode($status_data);
            if (isset($status_data_decoded->connection_status)) {
                update_option('en_fdo_company_id_status', $status_data_decoded->connection_status);
                update_option('en_fdo_company_id', $status_data_decoded->fdo_company_id);
            }
            return true;
        }
    }
}

