<?php
/**
 * Unishippers Small JS
 *
 * @package     Unishipper Small Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Connection Section Fields Validation
 *
 * Quote Setting Section Fields Validation
 *
 * Test Connection AJAX
 */
add_action('admin_footer', 'unishippers_small_connection_setting');

/**
 * JS
 */
function unishippers_small_connection_setting()
{
    ?>
    <script>

        // Update plan
        if (typeof en_update_plan != 'function') {
            function en_update_plan(input) {
                let action = jQuery(input).attr('data-action');
                jQuery.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {action: action},
                    success: function (data_response) {
                        window.location.reload(true);
                    }
                });
            }
        }

        jQuery.fn.enNumbersOnly = function () {
            jQuery(this).keydown(function (e) {
                var key = e.which || e.keyCode;

                var get_id = jQuery(this).attr('id');

                var negtv_char_restric = get_id == 'restrict_days_transit_package_unishepper_small' && key == '189' ? true : false;

                if (key >= 65 && key <= 90 || key >= 186 && key <= 188 || key >= 191 && key <= 222 || negtv_char_restric) {
                    return false;
                } else {
                    if (!e.shiftKey || key == 53) {
                        return true;
                    } else {
                        return false;
                    }
                }
            });
        }

        jQuery(document).ready(function () {

            // Start Cut Off Time & Ship Date Offset
            jQuery("input[name=unishippers_small_delivery_estimates]").closest('tr').addClass("unishippers_small_delivery_estimates_tr");
            jQuery("#service_unishippers_small_estimates_title").closest('tr').addClass("service_unishippers_small_estimates_title_tr");
            jQuery(".unishippers_small_shipment_day").closest('tr').addClass("unishippers_small_shipment_day_tr");
            jQuery("#all_shipment_days_unishippers_small").closest('tr').addClass("all_shipment_days_unishippers_small_tr");
            jQuery("#unishippers_small_cutOffTime_shipDateOffset").closest('tr').addClass("unishippers_small_cutOffTime_shipDateOffset_required_label");
            jQuery("#unishippers_small_orderCutoffTime").closest('tr').addClass("unishippers_small_cutOffTime_shipDateOffset");
            jQuery("#unishippers_small_shipmentOffsetDays").closest('tr').addClass("unishippers_small_cutOffTime_shipDateOffset");
            jQuery("#unishippers_small_timeformate").closest('tr').addClass("unishippers_small_timeformate");


            // estimated delivery options js
            jQuery("input[name=unishippers_small_delivery_estimates]").change(function () {
                var delivery_estimate_val = jQuery('input[name=unishippers_small_delivery_estimates]:checked').val();
                if (delivery_estimate_val == 'dont_show_estimates') {
                    jQuery("#unishippers_small_orderCutoffTime").prop('disabled', true);
                    jQuery("#unishippers_small_shipmentOffsetDays").prop('disabled', true);
                } else {
                    jQuery("#unishippers_small_orderCutoffTime").prop('disabled', false);
                    jQuery("#unishippers_small_shipmentOffsetDays").prop('disabled', false);
                }
            });

            var delivery_estimate = jQuery('input[name=unishippers_small_delivery_estimates]:checked').val();
            if (delivery_estimate == undefined) {
                jQuery('.unishippers_small_dont_show_estimate_option').prop("checked", true);
            }

            var delivery_estimate_val = jQuery('input[name=unishippers_small_delivery_estimates]:checked').val();
            if (delivery_estimate_val == 'dont_show_estimates') {
                jQuery("#unishippers_small_orderCutoffTime").prop('disabled', true);
                jQuery("#unishippers_small_shipmentOffsetDays").prop('disabled', true);
            } else {
                jQuery("#unishippers_small_orderCutoffTime").prop('disabled', false);
                jQuery("#unishippers_small_shipmentOffsetDays").prop('disabled', false);
            }

            jQuery('#unishippers_small_shipmentOffsetDays').attr('min', 1);
            var unishippersSmallCurrentTime = '<?php echo esc_attr( get_option("unishippers_small_orderCutoffTime") ); ?>';
            if (unishippersSmallCurrentTime != '') {
                jQuery('#unishippers_small_orderCutoffTime').wickedpicker({
                    now: unishippersSmallCurrentTime,
                    title: 'Cut Off Time'
                });
            } else {
                jQuery('#unishippers_small_orderCutoffTime').wickedpicker({
                    now: '',
                    title: 'Cut Off Time'
                });
            }

            /*
             * Uncheck Week days Select All Checkbox
             */
            jQuery(".unishippers_small_shipment_day").on('change load', function () {
                console.log();
                var checkboxes = jQuery('.unishippers_small_shipment_day:checked').size();
                var un_checkboxes = jQuery('.unishippers_small_shipment_day').size();
                if (checkboxes === un_checkboxes) {
                    jQuery('.all_shipment_days_unishippers_small').attr('checked', true);
                } else {
                    jQuery('.all_shipment_days_unishippers_small').attr('checked', false);
                }
            });

            /*
             * Select All Shipment Week days
             */
            var all_int_checkboxes = jQuery('.all_shipment_days_unishippers_small');
            if (all_int_checkboxes.length === all_int_checkboxes.filter(":checked").length) {
                jQuery('.all_shipment_days_unishippers_small').prop('checked', true);
            }

            jQuery(".all_shipment_days_unishippers_small").change(function () {
                if (this.checked) {
                    jQuery(".unishippers_small_shipment_day").each(function () {
                        this.checked = true;
                    });
                } else {
                    jQuery(".unishippers_small_shipment_day").each(function () {
                        this.checked = false;
                    });
                }
            });

            //** Start: Validat Shipment Offset Days
            jQuery("#unishippers_small_shipmentOffsetDays").keydown(function (e) {
                if (e.keyCode == 8)
                    return;

                var val = jQuery("#unishippers_small_shipmentOffsetDays").val();
                if (val.length > 1 || e.keyCode == 190) {
                    e.preventDefault();
                }
                // Allow: backspace, delete, tab, escape, enter and .
                if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53, 189]) !== -1 ||
                    // Allow: Ctrl+A, Command+A
                    (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                    // Allow: home, end, left, right, down, up
                    (e.keyCode >= 35 && e.keyCode <= 40)) {
                    // let it happen, don't do anything
                    return;
                }
                // Ensure that it is a number and stop the keypress
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }

            });


            // Allow: only positive numbers
            jQuery("#unishippers_small_shipmentOffsetDays").keyup(function (e) {
                if (e.keyCode == 189) {
                    e.preventDefault();
                    jQuery("#unishippers_small_shipmentOffsetDays").val('');
                }
            });

            // End Cut Off Time & Ship Date Offset


            jQuery("#en_unishippers_ground_hazardous_material_fee , #en_unishippers_air_hazardous_material_fee , #unishipper_small_hand_fee_mark_up  , #restrict_days_transit_package_unishepper_small").focus(function (e) {
                jQuery("#" + this.id).css({'border-color': '#ddd'});
            });


            jQuery('#restrict_days_transit_package_unishepper_small').enNumbersOnly();
            jQuery('#restrict_days_transit_package_unishepper_small').attr("onkeypress", "return !(event.charCode == 46)");
            jQuery('#en_unishippers_ground_hazardous_material_fee').enNumbersOnly();
            jQuery('#en_unishippers_air_hazardous_material_fee').enNumbersOnly();
            jQuery('#unishipper_small_hand_fee_mark_up').enNumbersOnly();
            jQuery("#unishipper_small_hand_fee_mark_up").attr('maxlength', 7);

            jQuery("#unishepper_sm_ground_transit_label").closest('tr').addClass("unishepper_sm_ground_transit_label");
            jQuery("#unishepper_small_hazardous_fee").closest('tr').addClass("unishepper_small_hazardous_fee");
            jQuery("#restrict_days_transit_package_unishepper_small").closest('tr').addClass("restrict_days_transit_package_unishepper_small");
            jQuery("input[name*='restrict_radio_btn_transit_unishepper_small']").closest('tr').addClass('restrict_radio_btn_transit_unishepper_small');
            jQuery("input[name*='unishepper_small_hazardous_materials_shipments']").closest('tr').addClass('unishepper_small_hazardous_materials_shipments');
            jQuery("input[name*='en_unishippers_ground_hazardous_material_fee']").closest('tr').addClass('en_unishippers_ground_hazardous_material_fee');
            jQuery("input[name*='en_unishippers_air_hazardous_material_fee']").closest('tr').addClass('en_unishippers_air_hazardous_material_fee');

            jQuery("#unishepper_small_quote_as_residential_delivery").closest('tr').addClass("unishepper_small_quote_as_residential_delivery");
            jQuery("#avaibility_auto_residential").closest('tr').addClass("avaibility_auto_residential");

            jQuery("#order_shipping_line_items .shipping .display_meta").css('display', 'none');

            jQuery('.unishepper_small_connection_section').before('<div class="warning-msg unishippers-warning-msg"><p> <b>Note!</b> You must have a Unishippers (unishippers.com) account to use this application. If you don’t have one, contact Unishippers at 1-800-999-8721 and ask to be contacted by a sales person from the office serving your area or <a target="_blank" href="https://www.unishippers.com/request-account/">click here</a> to access the online new account request form.</a></p>');

            jQuery(".unishipper_small_quotes_markup_left_label").closest('tr').addClass('unishipper_small_quotes_left_label');
            jQuery(".unishipper_small_quotes_markup_right_label").closest('tr').addClass('unishipper_small_quotes_right_label');
            jQuery(".unishipper_small_quotes_markup_left_markup").closest('tr').addClass('unishipper_small_quotes_left_markup');
            jQuery(".unishipper_small_quotes_markup_right_markup").closest('tr').addClass('unishipper_small_quotes_right_markup');
            jQuery(".unshipper_hidden_markup").closest('tr').addClass('unshipper_hidden_markup_tr');
            jQuery(".unshipper_hidden_label").closest('tr').addClass('unshipper_hidden_label_tr');
            jQuery(".unishepper_small_all_services").closest('tr').addClass('unishepper_small_all_services_tr');
            jQuery(".unishepper_small_all_int_services").closest('tr').addClass('unishepper_small_all_int_services_tr');
            jQuery(".worldwide_international").closest('tr').addClass('worldwide_international_tr');
            jQuery(".ups_next_day_saver").closest('tr').addClass('ups_next_day_saver_tr');

            jQuery('.unishippers-warning-msg:last').show();

            var url = getUrlVarsUnishipperSmall()["tab"];
            if (url === 'unishepper_small') {
                jQuery('#footer-left').attr('id', 'wc-footer-left');
            }

            /*
             * Add Title To Connection Setting Fields
             */
            jQuery('#unishepper_small_auth_key').attr('title', 'Request Key');
            jQuery('#unishepper_ups_account_number').attr('title', 'UPS Account Number');
            jQuery('#unishepper_small_customer_account_number').attr('title', 'Unishippers Customer Number');
            jQuery('#unishepper_username').attr('title', 'Username');
            jQuery('#unishepper_password').attr('title', 'Password');
            jQuery('#unishepper_small_licence_key').attr('title', 'Eniture API Key');
            jQuery('#unishepper_small_hazardous_fee').attr('title', 'Hazardous Material Fee');
            jQuery('#unishipper_small_hand_fee_mark_up').attr('title', 'Handling Fee / Markup');

            // Request key field is optional 
            jQuery('#unishepper_small_auth_key').attr('data-optional', '1');

            /*
             * Add CSS Class To Quote Services
             */
            jQuery('.bold-text').closest('tr').addClass('unishepper_small_quotes_services_tr');
            jQuery('.unishepper_small_quotes_services').closest('tr').addClass('unishepper_small_quotes_services_tr');
            jQuery('.unishepper_small_quotes_services').closest('td').addClass('unishepper_small_quotes_services_td');

            jQuery('.unishepper_small_one_rate_quotes_services').closest('tr').addClass('unishepper_small_one_rate_quotes_services_tr');
            jQuery('.unishepper_small_one_rate_quotes_services').closest('td').addClass('unishepper_small_one_rate_quotes_services_td');

            jQuery('.unishepper_small_int_quotes_services').closest('tr').addClass('unishepper_small_quotes_services_tr');
            jQuery('.unishepper_small_int_quotes_services').closest('td').addClass('unishepper_small_quotes_services_td');

            /*
             * Uncheck Select All Checkbox
             */
            jQuery(".unishepper_small_quotes_services").on('change load', function () {
                var checkboxes = jQuery('.unishepper_small_quotes_services:checked').size();
                var un_checkboxes = jQuery('.unishepper_small_quotes_services').size();
                if (checkboxes === un_checkboxes) {
                    jQuery('.unishepper_small_all_services').attr('checked', true);
                } else {
                    jQuery('.unishepper_small_all_services').attr('checked', false);
                }
            });

            /*
             * Uncheck One Rate Services Select All Checkbox
             */
            jQuery(".one_rate_checkbox").on('change load', function () {
                var int_checkboxes = jQuery('.one_rate_checkbox:checked').size();
                var int_un_checkboxes = jQuery('.one_rate_checkbox').size();
                if (int_checkboxes === int_un_checkboxes) {
                    jQuery('.unishepper_small_one_rate_all_services').attr('checked', true);
                } else {
                    jQuery('.unishepper_small_one_rate_all_services').attr('checked', false);
                }
            });

            /*
             * Uncheck International Services Select All Checkbox
             */
            jQuery(".unishepper_small_int_quotes_services").on('change load', function () {
                var int_checkboxes = jQuery('.unishepper_small_int_quotes_services:checked').size();
                var int_un_checkboxes = jQuery('.unishepper_small_int_quotes_services').size();
                if (int_checkboxes === int_un_checkboxes) {
                    jQuery('.unishepper_small_all_int_services').attr('checked', true);
                } else {
                    jQuery('.unishepper_small_all_int_services').attr('checked', false);
                }
            });

            /*
             * Save Changes Action
             */
            jQuery('.unishipper_small_quote_section .button-primary, .unishipper_small_quote_section .is-primary').on('click', function (e) {
                jQuery('.error').remove();
                jQuery('.updated').remove();

                if (!handlingFeeValidation()) {
                    return false;
                } else if (!airHazardousMaterialFeeValidation()) {
                    return false;
                } else if (!groundHazardousMaterialFeeValidation()) {
                    return false;
                } else if (!groundTransitValidation()) {
                    return false;
                }


                let unishipper_small_quotes_markup_left_markup = jQuery('.unishipper_small_quotes_markup_left_markup');
                jQuery(unishipper_small_quotes_markup_left_markup).each(function () {

                    if (jQuery('#' + this.id).val() != '' && !domestic_markup_service(this.id)) {
                        e.preventDefault();
                        return false;
                    }
                });


                let unishipper_small_quotes_markup_right_markup = jQuery('.unishipper_small_quotes_markup_right_markup');
                jQuery(unishipper_small_quotes_markup_right_markup).each(function () {

                    if (jQuery('#' + this.id).val() != '' && !international_markup_service(this.id)) {
                        e.preventDefault();
                        return false;
                    }
                });

                var num_of_checkboxes = jQuery('.unishepper_small_quotes_services:checked').size();
                var num_of_int_checkboxes = jQuery('.unishepper_small_int_quotes_services:checked').size();
                var num_of_one_rate_checkboxes = jQuery('.unishepper_small_one_rate_quotes_services:checked').size();
                var handling_fee = jQuery('#unishipper_small_hand_fee_mark_up').val();
                var ground_hazardous_material = jQuery('#en_unishippers_ground_hazardous_material_fee').val();
                var air_hazardous_material = jQuery('#en_unishippers_air_hazardous_material_fee').val();
                var restrict_days_transit = jQuery('#restrict_days_transit_package_unishepper_small').val();

                var restrict_days_transit_find = jQuery('#restrict_days_transit_package_unishepper_small').closest('.disabled_me').length;
                var en_unishippers_ground_hazardous_material_fee_find = jQuery('#en_unishippers_ground_hazardous_material_fee').closest('.disabled_me').length;
                var en_unishippers_air_hazardous_material_fee_find = jQuery('#en_unishippers_air_hazardous_material_fee').closest('.disabled_me').length;

                // Restrict Days
                if ((restrict_days_transit.length > 0 || restrict_days_transit == 0) && restrict_days_transit_find == 0 && restrict_days_transit.length != 0) {
                    if (!jQuery.isNumeric(restrict_days_transit) || !parseFloat(restrict_days_transit) > 0) {
                        jQuery("#mainform .unishipper_small_quote_section").prepend('<div id="message" class="error inline unishepper_small_restrict_days_error"><p><strong>Error! </strong>Ground transit time restriction format should be integer and Value should be greater than 0.</p></div>');
                        jQuery('html, body').animate({
                            'scrollTop': jQuery('.unishepper_small_restrict_days_error').position().top
                        });
                        return false;
                    }
                }

                /*
                 * Check Number of Selected Services
                 */
                if (num_of_checkboxes < 1 && num_of_int_checkboxes < 1 && num_of_one_rate_checkboxes < 1) {
                    no_service_selected_unishepper_small(num_of_checkboxes);
                    return false;
                }

                function handlingFeeValidation() {
                    var handling_fee = jQuery('#unishipper_small_hand_fee_mark_up').val();
                    var handling_fee_regex = /^(-?[0-9]{1,4}%?)$|(\.[0-9]{1,2})%?$/;
                    if (handling_fee != '' && !handling_fee_regex.test(handling_fee) || handling_fee.split('.').length - 1 > 1) {
                        jQuery("#mainform .unishipper_small_quote_section").prepend('<div id="message" class="error inline unishipper_small_handlng_fee_error"><p><strong>Error! </strong>Handling fee format should be 100.20 or 10%.</p></div>');
                        jQuery('html, body').animate({
                            'scrollTop': jQuery('.unishipper_small_handlng_fee_error').position().top
                        });
                        jQuery("#unishipper_small_hand_fee_mark_up").css({'border-color': '#e81123'});
                        return false;
                    } else {
                        return true;
                    }
                }

                function airHazardousMaterialFeeValidation() {

                    var air_hazardous_fee = jQuery('#en_unishippers_air_hazardous_material_fee').val();
                    var air_hazardous_fee_regex = /^([0-9]{1,4}%?)$|(\.[0-9]{1,2})%?$/;
                    if (air_hazardous_fee != '' && !air_hazardous_fee_regex.test(air_hazardous_fee) || air_hazardous_fee.split('.').length - 1 > 1) {
                        jQuery("#mainform .unishipper_small_quote_section").prepend('<div id="message" class="error inline unishipper_small_air_hazardous_fee_error"><p><strong>Error! </strong>Air hazardous material fee format should be 100.20 or 10%.</p></div>');
                        jQuery('html, body').animate({
                            'scrollTop': jQuery('.unishipper_small_air_hazardous_fee_error').position().top
                        });
                        jQuery("#en_unishippers_air_hazardous_material_fee").css({'border-color': '#e81123'});
                        return false;
                    } else {
                        return true;
                    }
                }

                function domestic_markup_service(id) {

                    var domestic_markup_service = jQuery('#' + id).val();
                    var domestic_markup_service_regex = /^(-?[0-9]{1,4}%?)$|(\.[0-9]{1,2})%?$/;

                    if (!domestic_markup_service_regex.test(domestic_markup_service)) {
                        jQuery("#mainform .unishipper_small_quote_section").prepend('<div id="message" class="error inline unishipper_small_dom_markup_service_error"><p><strong>Error! </strong>Service Level Markup fee format should be 100.20 or 10%.</p></div>');
                        jQuery('html, body').animate({
                            'scrollTop': jQuery('.unishipper_small_dom_markup_service_error').position().top
                        });
                        jQuery("#" + id).css({'border-color': '#e81123'});
                        return false;
                    } else {
                        return true;
                    }
                }

                function international_markup_service(id) {

                    var international_markup_service = jQuery('#' + id).val();
                    var international_markup_service_regex = /^(-?[0-9]{1,4}%?)$|(\.[0-9]{1,2})%?$/;

                    if (!international_markup_service_regex.test(international_markup_service)) {
                        jQuery("#mainform .unishipper_small_quote_section").prepend('<div id="message" class="error inline unishipper_small_int_markup_service_error"><p><strong>Error! </strong>Service Level Markup fee format should be 100.20 or 10%.</p></div>');
                        jQuery('html, body').animate({
                            'scrollTop': jQuery('.unishipper_small_int_markup_service_error').position().top
                        });
                        jQuery("#" + id).css({'border-color': '#e81123'});
                        return false;
                    } else {
                        return true;
                    }
                }

                function groundTransitValidation() {
                    var ground_transit_value = jQuery('#restrict_days_transit_package_unishepper_small').val();
                    var ground_transit_regex = /^[0-9]{1,2}$/;
                    if (ground_transit_value != '' && !ground_transit_regex.test(ground_transit_value)) {
                        jQuery("#mainform .unishipper_small_quote_section").prepend('<div id="message" class="error inline unishipper_ground_transit_error"><p><strong>Error! </strong>Maximum 2 numeric characters are allowed for transit day field.</p></div>');
                        jQuery('html, body').animate({
                            'scrollTop': jQuery('.unishipper_ground_transit_error').position().top
                        });
                        jQuery("#restrict_days_transit_package_unishepper_small").css({'border-color': '#e81123'});
                        return false;
                    } else {
                        return true;
                    }
                }

                function groundHazardousMaterialFeeValidation() {

                    var ground_hazardous_fee = jQuery('#en_unishippers_ground_hazardous_material_fee').val();
                    var ground_hazardous_regex = /^([0-9]{1,4}%?)$|(\.[0-9]{1,2})%?$/;
                    if (ground_hazardous_fee != '' && !ground_hazardous_regex.test(ground_hazardous_fee) || ground_hazardous_fee.split('.').length - 1 > 1) {
                        jQuery("#mainform .unishipper_small_quote_section").prepend('<div id="message" class="error inline unishipper_small_ground_hazardous_fee_error"><p><strong>Error! </strong>Ground  hazardous material  fee format should be 100.20 or 10%.</p></div>');
                        jQuery('html, body').animate({
                            'scrollTop': jQuery('.unishipper_small_ground_hazardous_fee_error').position().top
                        });
                        jQuery("#en_unishippers_ground_hazardous_material_fee").css({'border-color': '#e81123'});
                        return false;
                    } else {
                        return true;
                    }
                }

                /*Custom Error Message Validation*/
                var checkedValCustomMsg = jQuery("input[name='wc_pervent_proceed_checkout_eniture']:checked").val();
                var allow_proceed_checkout_eniture = jQuery("textarea[name=allow_proceed_checkout_eniture]").val();
                var prevent_proceed_checkout_eniture = jQuery("textarea[name=prevent_proceed_checkout_eniture]").val();

                if (checkedValCustomMsg == 'allow' && allow_proceed_checkout_eniture == '') {
                    jQuery("#mainform .unishipper_small_quote_section").prepend('<div id="message" class="error inline unishipper_small_custom_error_message"><p><strong>Error! </strong>Custom message field is empty.</p></div>');
                    jQuery('html, body').animate({
                        'scrollTop': jQuery('.unishipper_small_custom_error_message').position().top
                    });
                    return false;
                } else if (checkedValCustomMsg == 'prevent' && prevent_proceed_checkout_eniture == '') {
                    jQuery("#mainform .unishipper_small_quote_section").prepend('<div id="message" class="error inline unishipper_small_custom_error_message"><p><strong>Error! </strong>Custom message field is empty.</p></div>');
                    jQuery('html, body').animate({
                        'scrollTop': jQuery('.unishipper_small_custom_error_message').position().top
                    });
                    return false;
                }
            });

            /*
             * Select All Services
             */
            var sm_all_checkboxes = jQuery('.unishepper_small_quotes_services');
            if (sm_all_checkboxes.length === sm_all_checkboxes.filter(":checked").length) {
                jQuery('.unishepper_small_all_services').prop('checked', true);
            }

            jQuery(".unishepper_small_all_services").change(function () {
                if (this.checked) {
                    jQuery(".unishepper_small_quotes_services").each(function () {
                        this.checked = true;
                    })
                } else {
                    jQuery(".unishepper_small_quotes_services").each(function () {
                        this.checked = false;
                    })
                }
            });

            /*
             * Select One Rate All Services
             */
            var sm_all_checkboxes = jQuery('.unishepper_small_one_rate_quotes_services');
            if (sm_all_checkboxes.length === sm_all_checkboxes.filter(":checked").length) {
                jQuery('.unishepper_small_one_rate_all_services').prop('checked', true);
            }

            jQuery(".unishepper_small_one_rate_all_services").change(function () {
                if (this.checked) {
                    jQuery(".unishepper_small_one_rate_quotes_services").each(function () {
                        this.checked = true;
                    })
                } else {
                    jQuery(".unishepper_small_one_rate_quotes_services").each(function () {
                        this.checked = false;
                    })
                }
            });

            /* One Rate checkbox */
            if (jQuery(".one_rate_error").length > 0) {
                jQuery(".one_rate_click").on('change', function () {

                    var one_rate_checkbox = false;
                    jQuery(".one_rate_click").each(function () {

                        this.checked ? one_rate_checkbox = true : "";

                    });

                    var display = one_rate_checkbox == true ? "block" : "none";
                    jQuery(".one_rate_error").css("display", display);

                });
            }

            if (jQuery(".one_rate_error").length > 1) {
                jQuery(".one_rate_error :first").remove();
            }

        });

        /*
         * Select All Services International
         */
        var all_int_checkboxes = jQuery('.unishepper_small_int_quotes_services');
        if (all_int_checkboxes.length === all_int_checkboxes.filter(":checked").length) {
            jQuery('.unishepper_small_all_int_services').prop('checked', true);
        }

        jQuery(".unishepper_small_all_int_services").change(function () {
            if (this.checked) {
                jQuery(".unishepper_small_int_quotes_services").each(function () {
                    this.checked = true;
                })
            } else {
                jQuery(".unishepper_small_int_quotes_services").each(function () {
                    this.checked = false;
                })
            }
        });

        /**
         * Read a page's GET URL variables and return them as an associative array.
         */
        function getUrlVarsUnishipperSmall() {
            var vars = [], hash;
            var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
            for (var i = 0; i < hashes.length; i++) {
                hash = hashes[i].split('=');
                vars.push(hash[0]);
                vars[hash[0]] = hash[1];
            }
            return vars;
        }

        /*
         * Validate Selecting Services 
         */
        function no_service_selected_unishepper_small(num_of_checkboxes) {
            jQuery(".updated").hide();
            jQuery("#mainform .unishipper_small_quote_section").before('<div id="message" class="error inline no_srvc_select"><p><strong>Error! </strong>Please select at least one quote service.</p></div>');
            jQuery('html, body').animate({
                'scrollTop': jQuery('.no_srvc_select').position().top
            });
            return false;
        }

        /*
         * Validate Input If Empty or Invalid
         */
        function validateInput(form_id) {
            var has_err = true;
            jQuery(form_id + " input[type='text']").each(function () {
                var input = jQuery(this).val();
                var response = validateString(input);
                if (jQuery(this).parent().find('.err').length < 1) {
                    jQuery(this).after('<span class="err"></span>');
                }
                var errorElement = jQuery(this).parent().find('.err');
                jQuery(errorElement).html('');
                var errorText = jQuery(this).attr('title');
                var optional = jQuery(this).data('optional');
                optional = (optional === undefined) ? 0 : 1;
                errorText = (errorText != undefined) ? errorText : '';
                if ((optional == 0) && (response == false || response == 'empty')) {
                    errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
                    jQuery(errorElement).html(errorText);
                }
                has_err = (response != true && optional == 0) ? false : has_err;
            });
            return has_err;
        }

        /*
         * Check Input Value Is Not String
         */
        function isValidNumber(value, noNegative) {
            if (typeof (noNegative) === 'undefined')
                noNegative = false;
            var isValidNumber = false;
            var validNumber = (noNegative == true) ? parseFloat(value) >= 0 : true;
            if ((value == parseInt(value) || value == parseFloat(value)) && (validNumber)) {
                if (value.indexOf(".") >= 0) {
                    var n = value.split(".");
                    if (n[n.length - 1].length <= 4) {
                        isValidNumber = true;
                    } else {
                        isValidNumber = 'decimal_point_err';
                    }
                } else {
                    isValidNumber = true;
                }
            }
            return isValidNumber;
        }

        /*
         * Validate Input String 
         */
        function validateString(string) {
            if (string == '') {
                return 'empty';
            } else {
                return true;
            }
        }

        /*
         * Connection Settings Input Validation On Save 
         */
        jQuery(".unishepper_small_connection_section .button-primary, .unishepper_small_connection_section .is-primary").click(function () {
            var input = validateInput('.unishepper_small_connection_section');
            if (input === false) {
                return false;
            }
        });

        /*
         * Test Connection 
         */
        jQuery(".unishepper_small_connection_section .woocommerce-save-button").before('<a href="javascript:void(0)" class="button-primary unishipper_small_test_connection">Test Connection</a>');
        jQuery('.unishipper_small_test_connection').click(function (e) {
            var input = validateInput('.unishepper_small_connection_section');
            if (input === false) {
                return false;
            }
            var postForm = {
                'action': 'unishipper_small_test_connection',
                'unishepper_small_auth': jQuery('#unishepper_small_auth_key').val(),
                'unishepper_ups_account_number': jQuery('#unishepper_ups_account_number').val(),
                'unishepper_small_customer_acc_number': jQuery('#unishepper_small_customer_account_number').val(),
                'unishepper_username': jQuery('#unishepper_username').val(),
                'unishepper_password': jQuery('#unishepper_password').val(),
                'unishepper_small_license': jQuery('#unishepper_small_licence_key').val(),
            };
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: postForm,
                dataType: 'json',
                beforeSend: function () {
                    jQuery('#unishepper_small_auth_key').css('background', 'rgba(255, 255, 255, 1) url("<?php echo esc_url_raw( plugins_url() ); ?>/small-package-quotes-unishippers-edition/asset/processing.gif") no-repeat scroll 50% 50%');
                    jQuery('#unishepper_ups_account_number').css('background', 'rgba(255, 255, 255, 1) url("<?php echo esc_url_raw( plugins_url() ); ?>/small-package-quotes-unishippers-edition/asset/processing.gif") no-repeat scroll 50% 50%');
                    jQuery('#unishepper_small_customer_account_number').css('background', 'rgba(255, 255, 255, 1) url("<?php echo esc_url_raw( plugins_url() ); ?>/small-package-quotes-unishippers-edition/asset/processing.gif") no-repeat scroll 50% 50%');
                    jQuery('#unishepper_username').css('background', 'rgba(255, 255, 255, 1) url("<?php echo esc_url_raw( plugins_url() ); ?>/small-package-quotes-unishippers-edition/asset/processing.gif") no-repeat scroll 50% 50%');
                    jQuery('#unishepper_password').css('background', 'rgba(255, 255, 255, 1) url("<?php echo esc_url_raw( plugins_url() ); ?>/small-package-quotes-unishippers-edition/asset/processing.gif") no-repeat scroll 50% 50%');
                    jQuery('#unishepper_small_licence_key').css('background', 'rgba(255, 255, 255, 1) url("<?php echo esc_url_raw( plugins_url() ); ?>/small-package-quotes-unishippers-edition/asset/processing.gif") no-repeat scroll 50% 50%');
                },
                success: function (data) {
                    if (typeof data.severity != 'undefined' && data.severity == 'SUCCESS') {
                        jQuery(".updated").hide();
                        jQuery('#unishepper_small_auth_key').css('background', '#fff');
                        jQuery('#unishepper_ups_account_number').css('background', '#fff');
                        jQuery('#unishepper_small_customer_account_number').css('background', '#fff');
                        jQuery('#unishepper_username').css('background', '#fff');
                        jQuery('#unishepper_password').css('background', '#fff');
                        jQuery('#unishepper_small_licence_key').css('background', '#fff');
                        jQuery(".unishepper_small_success_message").remove();
                        jQuery(".unishepper_small_error_message").remove();
                        jQuery('.warning-msg').before('<div class="notice notice-success unishepper_small_success_message"><p><strong>Success! </strong>The test resulted in a successful connection.</p></div>');
                    } else {
                        jQuery(".updated").hide();
                        jQuery(".unishepper_small_error_message").remove();
                        jQuery('#unishepper_small_auth_key').css('background', '#fff');
                        jQuery('#unishepper_ups_account_number').css('background', '#fff');
                        jQuery('#unishepper_small_customer_account_number').css('background', '#fff');
                        jQuery('#unishepper_username').css('background', '#fff');
                        jQuery('#unishepper_password').css('background', '#fff');
                        jQuery('#unishepper_small_licence_key').css('background', '#fff');
                        jQuery(".unishepper_small_success_message").remove();

                        if (typeof data.error != 'undefined' && typeof data.error_desc != 'undefined' ) {
                            jQuery('.warning-msg').before('<div class="notice notice-error unishepper_small_error_message"><p><strong>Error!</strong> ' + data.error_desc + '</p></div>');
                        }else if (typeof data.error != 'undefined') {
                            jQuery('.warning-msg').before('<div class="notice notice-error unishepper_small_error_message"><p><strong>Error!</strong> ' + data.error + '</p></div>');
                        } else if (typeof data.Message != 'undefined') {
                            jQuery('.warning-msg').before('<div class="notice notice-error unishepper_small_error_message"><p><strong>Error!</strong> ' + data.Message + '</p></div>');
                        } else {
                            jQuery('.warning-msg').before('<div class="notice notice-error unishepper_small_error_message"><p><strong>Error!</strong> Please verify credentials and try again.</p></div>');
                        }
                    }
                }
            });
            e.preventDefault();
        });

        jQuery(document).ready(function () {

            var prevent_text_box = jQuery('.prevent_text_box').length;
            if (!prevent_text_box > 0) {
                jQuery("input[name*='wc_pervent_proceed_checkout_eniture']").closest('tr').addClass('wc_pervent_proceed_checkout_eniture');
                jQuery(".wc_pervent_proceed_checkout_eniture input[value*='allow']").after('Allow user to continue to check out and display this message <br><br><textarea  name="allow_proceed_checkout_eniture" class="prevent_text_box" title="Message" maxlength="250"><?php echo esc_attr(get_option("allow_proceed_checkout_eniture")); ?></textarea><span class="en_custom_error_description description"> Enter a maximum of 250 characters.</span>');
                jQuery(".wc_pervent_proceed_checkout_eniture input[value*='prevent']").after('Prevent user from checking out and display this message <br><br><textarea name="prevent_proceed_checkout_eniture" class="prevent_text_box" title="Message" maxlength="250"><?php echo esc_attr(get_option("prevent_proceed_checkout_eniture")); ?></textarea><span class="en_custom_error_description description"> Enter a maximum of 250 characters.</span>');
            }

            jQuery(".unishipper_small_quotes_markup_left_markup").keydown(function (e) {
                // Allow: backspace, delete, tab, escape, enter and .
                if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53, 189]) !== -1 ||
                    // Allow: Ctrl+A, Command+A
                    (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                    // Allow: home, end, left, right, down, up
                    (e.keyCode >= 35 && e.keyCode <= 40)) {
                    // let it happen, don't do anything
                    return;
                }
                // Ensure that it is a number and stop the keypress
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }

                if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 2)) {
                    if (e.keyCode !== 8 && e.keyCode !== 46) { //exception
                        e.preventDefault();
                    }
                }

            });

            jQuery(".unishipper_small_quotes_markup_left_markup").keyup(function (e) {

                var selected_domestic_id = jQuery(this).attr("id");
                jQuery("#" + selected_domestic_id).css({"border": "1px solid #ddd"});

                var val = jQuery("#" + selected_domestic_id).val();
                if (val.split('.').length - 1 > 1) {
                    var newval = val.substring(0, val.length - 1);
                    var countDots = newval.substring(newval.indexOf('.') + 1).length;
                    newval = newval.substring(0, val.length - countDots - 1);
                    jQuery("#" + selected_domestic_id).val(newval);

                }

                if (val.split('%').length - 1 > 1) {
                    var newval = val.substring(0, val.length - 1);
                    var countPercentages = newval.substring(newval.indexOf('%') + 1).length;
                    newval = newval.substring(0, val.length - countPercentages - 1);
                    jQuery("#" + selected_domestic_id).val(newval);
                }
                if (val.split('>').length - 1 > 0) {
                    var newval = val.substring(0, val.length - 1);
                    var countGreaterThan = newval.substring(newval.indexOf('>') + 1).length;
                    newval = newval.substring(newval, newval.length - countGreaterThan - 1);
                    jQuery("#" + selected_domestic_id).val(newval);
                }
                if (val.split('_').length - 1 > 0) {
                    var newval = val.substring(0, val.length - 1);
                    var countUnderScore = newval.substring(newval.indexOf('_') + 1).length;
                    newval = newval.substring(newval, newval.length - countUnderScore - 1);
                    jQuery("#" + selected_domestic_id).val(newval);
                }
                if (val.split('-').length - 1 > 1) {
                    var newval = val.substring(0, val.length - 1);
                    var countPercentages = newval.substring(newval.indexOf('-') + 1).length;
                    newval = newval.substring(0, val.length - countPercentages - 1);
                    jQuery("#" + selected_domestic_id).val(newval);
                }
            });

            jQuery(".unishipper_small_quotes_markup_right_markup").keydown(function (e) {
                // Allow: backspace, delete, tab, escape, enter and .
                if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53, 189]) !== -1 ||
                    // Allow: Ctrl+A, Command+A
                    (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                    // Allow: home, end, left, right, down, up
                    (e.keyCode >= 35 && e.keyCode <= 40)) {
                    // let it happen, don't do anything
                    return;
                }
                // Ensure that it is a number and stop the keypress
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }

                if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 2)) {
                    if (e.keyCode !== 8 && e.keyCode !== 46) { //exception
                        e.preventDefault();
                    }
                }

            });

            jQuery(".unishipper_small_quotes_markup_right_markup").keyup(function (e) {

                var selected_domestic_id = jQuery(this).attr("id");
                jQuery("#" + selected_domestic_id).css({"border": "1px solid #ddd"});

                var val = jQuery("#" + selected_domestic_id).val();
                if (val.split('.').length - 1 > 1) {
                    var newval = val.substring(0, val.length - 1);
                    var countDots = newval.substring(newval.indexOf('.') + 1).length;
                    newval = newval.substring(0, val.length - countDots - 1);
                    jQuery("#" + selected_domestic_id).val(newval);

                }

                if (val.split('%').length - 1 > 1) {
                    var newval = val.substring(0, val.length - 1);
                    var countPercentages = newval.substring(newval.indexOf('%') + 1).length;
                    newval = newval.substring(0, val.length - countPercentages - 1);
                    jQuery("#" + selected_domestic_id).val(newval);
                }
                if (val.split('>').length - 1 > 0) {
                    var newval = val.substring(0, val.length - 1);
                    var countGreaterThan = newval.substring(newval.indexOf('>') + 1).length;
                    newval = newval.substring(newval, newval.length - countGreaterThan - 1);
                    jQuery("#" + selected_domestic_id).val(newval);
                }
                if (val.split('_').length - 1 > 0) {
                    var newval = val.substring(0, val.length - 1);
                    var countUnderScore = newval.substring(newval.indexOf('_') + 1).length;
                    newval = newval.substring(newval, newval.length - countUnderScore - 1);
                    jQuery("#" + selected_domestic_id).val(newval);
                }
                if (val.split('-').length - 1 > 1) {
                    var newval = val.substring(0, val.length - 1);
                    var countPercentages = newval.substring(newval.indexOf('-') + 1).length;
                    newval = newval.substring(0, val.length - countPercentages - 1);
                    jQuery("#" + selected_domestic_id).val(newval);
                }
            });
        });

    </script>
    <?php
}
