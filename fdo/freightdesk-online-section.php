<?php
/**
 * Unishippers Freightdesk online Template
 *
 * @package     Unishippers Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}


require_once('en-fdo.php');
$fdo_obj = new \Unishippers_EnUnishippersSmallFdo();

$fdo_coupon_data = $fdo_obj->get_fdo_coupon_data();

?>

<div class="user_guide_fdo">
    <h2>Connect to FreightDesk Online.</h2>
    <p>
    FreightDesk Online (
        <a href="https://freightdesk.online/" target="_blank">freightdesk.online</a>
        ) is a cloud-based, multi-carrier shipping platform that allows its users to create and manage postal, parcel, and LTL freight shipments. 
    Connect your store to FreightDesk Online and virtually eliminate the need for data entry when shipping orders. (
        <a href="https://freightdesk.online/" target="_blank">Learn more</a>
        )
    </p>

    <?php

    if(empty($fdo_coupon_data)){
        ?>
            <div id="message" class="en-coupon-code-div woocommerce-message en-coupon-error">
                <p>
                    Sorry! we are unable to get a discounted coupon from freightdesk.online.
                    Please try later.
                </p>
            </div>
        <?php
    }else{
        if(!$fdo_coupon_data['fdo_user']){

            ?>
                <div id="message" class="en-coupon-code-div woocommerce-message en-coupon-notice">
                    <p>
                        <strong>Note!</strong> 
                        To establish a connection, you must have a freightdesk.online account. 
                        If you don’t have one, get freightdesk.online free for two months by using promo code <strong>[<?php echo esc_attr( $fdo_coupon_data['coupon'] ) ?>]</strong>. 
                        
                    </p>
                    <p>
                        Click <a href="<?php echo esc_attr( $fdo_coupon_data['register_url'] ) ?>" target="_blank">here</a> to register for freightdesk.online using the promo code now.
                        If you already connected then please click 
                        <a href="javascript:void(0)" onclick="en_unishippers_small_fdo_connection_status_refresh(this);">this link</a> 
                        to refresh the status.

                    </p>

                </div>
            <?php

        } else if ($fdo_coupon_data['status'] == 1) {
            ?>
            <div id="message" class="en-coupon-code-div woocommerce-message en-coupon-success">
                <p>
                    <strong>Congratulations!</strong> You have activated your Promo Code
                    <strong>[<?php echo esc_attr( $fdo_coupon_data['coupon'] ) ?>
                        ]</strong><?php echo esc_attr( $fdo_coupon_data['fdo_company_text'] ) ?>. Now you can enjoy free shipments
                    with FreightDesk Online for two months.
                </p>
            </div>
            <?php
        } else if ($fdo_coupon_data['status'] == 2) {
            ?>
            <div id="message" class="en-coupon-code-div woocommerce-message en-coupon-success">
                <p>
                    <strong>Note!</strong> Your promo code <strong>[<?php echo esc_attr( $fdo_coupon_data['coupon'] ) ?>]</strong>
                    has expired.
                </p>
            </div>
            <?php

        }else{
            ?>
            <div id="message" class="en-coupon-code-div woocommerce-message en-coupon-notice">
                <p>
                    Note! Get FreightDesk Online free for two months by using promo code <strong>[<?php echo esc_attr( $fdo_coupon_data['coupon'] ) ?>]</strong>.
                </p>
                <div class='en-coupon-btn-div'>
                    <button class="en_fdo_unishippers_small_apply_promo_btn button" data-coupon="<?php echo esc_attr( $fdo_coupon_data['coupon'] ) ?>"><?php esc_html_e( 'Apply Promo Code', 'eniture-unishippers-small-quotes' ); ?></button>
                </div>
            </div>
            
        <?php
        }
        
    }
    // fdo va
    $f_desk_online_id = '<a href="https://support.eniture.com/what-is-my-freightdesk-online-id" target="_blank">[ ? ]</a>';
    $company_id = get_option('en_fdo_company_id');

    if(get_option('en_fdo_company_id_status') != 1) {
        echo '<div class="parent-column">
                    <div class="half-column">
                        <h2>FreightDesk Online ID <a href="https://support.eniture.com/what-is-my-freightdesk-online-id" target="_blank">[ ? ]</a> <span style="color:red">*</span></h2>
                    </div>
                    <div class="half-column">
                    <div style="padding: 15px 0">
                            <input type="text" name="freightdesk_online_id" id="freightdesk_online_id" value="" maxlength="10" placeholder="FreightDesk Online ID">
                         </div> 
                         <span id="con_dis"> <a href="javascript:void(0)" id="fd_online_id_unishippers_s" class="button-primary">Connect</a></span>
                         <p>While connecting, Woocommerce may prompt you to authorize access to:</p>
                        <ul style="list-style-type: disc;margin-left: 16px">
                            <li>Create webhooks</li>
                            <li>View and manage coupons</li>
                            <li>View and manage customers</li>
                            <li>View and manage orders and sales </li>
                            <li>Sales reports</li>
                            <li>View and manage products</li>
                        </ul>
                        </div>';

    }else {
        if(get_option('en_fdo_company_id_status') == 1) {
            echo '<div class="parent-column">
                    <div class="half-column dn">
                        <h2>FreightDesk Online ID <a href="https://support.eniture.com/what-is-my-freightdesk-online-id" target="_blank">[ ? ]</a> <span style="color:red">*</span></h2>
                    </div>
                    <div class="half-column dnblock">
                    <div style="padding: 15px 0 15px 0">
                            Connected to FreightDesk Online using FreightDesk Online Account ID <strong> ' . esc_attr($company_id) . ' <strong> ' . esc_attr($f_desk_online_id) . '</div> 
                         <span id="con_dis"><a href="javascript:void(0)" data="disconnect" id="fd_online_id_unishippers_s" class="button-primary">Disconnect</a></span>
                         
                        </div>
                    ';

        }
    }


    ?>
