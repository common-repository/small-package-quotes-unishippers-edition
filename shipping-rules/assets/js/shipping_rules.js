jQuery(document).ready(function () {
	const form = jQuery('#en_unishippers_small_add_shipping_rule');
	if (form) {
		form.on('submit', enUnishippersSmallSaveShippingRule);
	}

	enUnishippersSmalltoggleRuleTypeFields();

	jQuery('#en_unishippers_small_add_shipping_rule_btn').on('click', function () {
		form[0]?.reset();
		jQuery('.en_sr_err').html('');
		setDataAttribute(
			'en_sr_weight_from',
			'en_sr_price_from',
			'en_sr_quantity_from',
			'en_sr_product_tags_list',
			'en_sr_service_name',
			'en_sr_service_rate',
			'en_sr_max_items',
			'en_sr_max_weight_per_package'
		);
		jQuery('#en_sr_product_tags_list').val('');
		jQuery('.select2-selection__choice').hide();
		jQuery('#edit_sr_form_id').val('');
		jQuery('.content').animate({ scrollTop: 0 }, 0);
		enUnishippersSmalltoggleRuleTypeFields();
	});

	jQuery('#rule_type').on('change', function (e) {
		enUnishippersSmalltoggleRuleTypeFields();
	});

	jQuery(
		'#en_sr_weight_from, #en_sr_weight_to, #en_sr_price_from, #en_sr_price_to, #en_sr_service_rate, #en_sr_max_items, #en_sr_max_weight_per_package'
	).keypress(function (e) {
		if (!String.fromCharCode(e.keyCode).match(/^[0-9.]+$/)) return false;
	});
	jQuery('#en_sr_quantity_from, #en_sr_quantity_to').keypress(function (e) {
		if (!String.fromCharCode(e.keyCode).match(/^[0-9]+$/)) return false;
	});

	jQuery(
		'#filter_by_weight, #en_sr_filter_price, #filter_by_quantity, #filter_by_vendor, #filter_by_product_tag'
	).on('change', function () {
		const id = this.id;

		if (this.checked) {
			if (id === 'filter_by_weight') removeDataAttribute('en_sr_weight_from');
			else if (id === 'en_sr_filter_price') removeDataAttribute('en_sr_price_from');
			else if (id === 'filter_by_quantity') removeDataAttribute('en_sr_quantity_from');
			else if (id === 'filter_by_vendor') removeDataAttribute('en_vendor_filter_value');
			else if (id === 'filter_by_product_tag') removeDataAttribute('en_sr_product_tags_list');
		} else {
			if (id === 'filter_by_weight') setDataAttribute('en_sr_weight_from');
			else if (id === 'en_sr_filter_price') setDataAttribute('en_sr_price_from');
			else if (id === 'filter_by_quantity') setDataAttribute('en_sr_quantity_from');
			else if (id === 'filter_by_vendor') setDataAttribute('en_vendor_filter_value');
			else if (id === 'filter_by_product_tag') setDataAttribute('en_sr_product_tags_list');
		}
	});

	jQuery(document).on('click', '.en_unishippers_small_sr_edit_link', function () {
		const id = jQuery(this).attr('data-id');
		enUnishippersSmallEditShippingRule(id, this);
	});
	jQuery(document).on('click', '.en_unishippers_small_sr_delete_link', function () {
		const id = jQuery(this).attr('data-id');
		enUnishippersSmallDeleteShippingRule(id, this);
	});
	jQuery(document).on('click', '.en_unishippers_small_sr_status_link', function (e) {
		enUnishippersSmallUpdateShippingRuleStatus(this);
	});

	function setDataAttribute(...attr_ids) {
		for (let id of attr_ids) {
			jQuery('#' + id).attr('data-optional', '1');
		}
	}

	function removeDataAttribute(...attr_ids) {
		for (let id of attr_ids) {
			jQuery('#' + id).removeAttr('data-optional');
		}
	}

	function enUnishippersSmallSRValidateInput(form_id = '') {
		let is_valid = true;
		let input = (response = errorText = optional = errorElement = to = '');

		jQuery('#en_sr_service_name').parent().find('.en_sr_err').html('');

		jQuery(form_id + " input[type='text']").each(function () {
			input = jQuery(this).val();
			response = validateString(input);
			errorText = jQuery(this).attr('title');
			optional = jQuery(this).attr('data-optional');
			errorElement = jQuery(this).parent().find('.en_sr_err');

			if (this?.className?.includes('select2-search__field')) {
				const id = '#en_sr_product_tags_list';
				input = jQuery(id).val();
				response = input?.length > 0 ? true : 'empty';
				errorText = jQuery(id).attr('title');
				optional = jQuery(id).attr('data-optional');
				errorElement = jQuery(id).parent().find('.en_sr_err');
			}

			jQuery(errorElement).html('');

			optional = optional === undefined ? 0 : 1;
			errorText = errorText != undefined ? errorText : '';

			if (optional == 0 && (response == false || response == 'empty')) {
				errorText = response == 'empty' ? errorText + ' is required.' : 'Invalid input.';
				jQuery(errorElement).html(errorText);
			} else {
				errorText = '';
			}

			is_valid = response != true && optional == 0 ? false : is_valid;

			if (!errorText && input) {
				if (
					['en_sr_weight_from', 'en_sr_price_from', 'en_sr_quantity_from'].includes(
						this.id
					)
				) {
					const title =
						this.id === 'en_sr_weight_from'
							? 'weight'
							: this.id === 'en_sr_price_from'
							? 'price'
							: 'quantity';
					to =
						title === 'weight'
							? '#en_sr_weight_to'
							: title === 'price'
							? '#en_sr_price_to'
							: '#en_sr_quantity_to';
					to = jQuery(to).val();

					if (to && +input >= +to) {
						errorText = `From ${title} cannot be greater than or equal to To ${title}.`;
						jQuery(errorElement).html(errorText);
						is_valid = false;
					}
				}
			}
		});

		// validate service name select field
		if (jQuery('#rule_type').val() === 'Override Rates') {
			const serviceName = jQuery('#en_sr_service_name').val();
			if (!serviceName) {
				is_valid = false;
				jQuery('#en_sr_service_name')
					.parent()
					.find('.en_sr_err')
					.html('Service is required.');
			}
		}

		return is_valid;
	}

	function validateString(string) {
		return string == '' ? 'empty' : true;
	}

	//#region Save Shipping Rule
	function enUnishippersSmallSaveShippingRule(e) {
		e.preventDefault();

		let is_valid = enUnishippersSmallSRValidateInput('#en_unishippers_small_add_shipping_rule');
		is_valid = is_valid && validateLargeCartSettingsRule();
		if (!is_valid) {
			jQuery('.content').delay(200).animate({ scrollTop: 0 }, 300);
			return false;
		}

		// Submit the form to save settings
		const postData = {
			action: 'en_unishippers_small_save_shipping_rule',
			rule_id: jQuery('#edit_sr_form_id').val(),
			name: jQuery('#en_sr_rule_name').val(),
			type: jQuery('#rule_type').val(),
			is_active: Number(jQuery('#en_sr_avialable').prop('checked')),
			settings: {
				filter_name: jQuery('#en_sr_filter_name').val(),
				apply_to: jQuery('input[name="apply_to"]:checked').val(),
				// Filter by weight
				filter_by_weight: jQuery('#filter_by_weight').prop('checked'),
				filter_by_weight_from: jQuery('#en_sr_weight_from').val(),
				filter_by_weight_to: jQuery('#en_sr_weight_to').val(),
				// Filter by price
				filter_by_price: jQuery('#en_sr_filter_price').prop('checked'),
				filter_by_price_from: jQuery('#en_sr_price_from').val(),
				filter_by_price_to: jQuery('#en_sr_price_to').val(),
				// Filter by quantity
				filter_by_quantity: jQuery('#filter_by_quantity').prop('checked'),
				filter_by_quantity_from: jQuery('#en_sr_quantity_from').val(),
				filter_by_quantity_to: jQuery('#en_sr_quantity_to').val(),
				// Filter by product tag
				filter_by_product_tag: jQuery('#filter_by_product_tag').prop('checked'),
				filter_by_product_tag_value: jQuery('#en_sr_product_tags_list').val(),
				// Service info
				service: jQuery('#en_sr_service_name').val(),
				service_rate: jQuery('#en_sr_service_rate').val(),
				// Large cart settings
				max_items: jQuery('#en_sr_max_items').val(),
				max_weight_per_package: jQuery('#en_sr_max_weight_per_package').val(),
			},
		};

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: postData,
			dataType: 'json',
			beforeSend: function () {
				jQuery('.save_shipping_rule_form').addClass('spinner_disable').val('Loading...');
			},
			success: function (data) {
				jQuery('.save_shipping_rule_form').removeClass('spinner_disable').val('Save');
				const rowMarkup = enUnishippersSmallgetRowMarkup({ ...data, ...postData });
				jQuery('.en_unishippers_small_empty_sr_row').hide();

				if (data.insert_qry == 1) {
					const newRowMarkup = `<tr id='sr_row_${data.id}' class="en_unishippers_small_sr_row">${rowMarkup}</tr>`;
					jQuery('#en_unishippers_small_shipping_rules_list tbody').append(newRowMarkup);
					window.location.href = jQuery('.close').attr('href');
					jQuery('.unishippers_small_sr_created').show('slow').delay(3000).hide('slow');
				} else if (data.update_qry == 1) {
					jQuery(`#sr_row_${data.id}`).html(rowMarkup);
					window.location.href = jQuery('.close').attr('href');
					jQuery('.unishippers_small_sr_updated').show('slow').delay(3000).hide('slow');
				} else if (data.error) {
					jQuery('.sr_already_exist').show('slow');
					jQuery('.content').delay(200).animate({ scrollTop: 0 }, 300);
					setTimeout(function () {
						jQuery('.sr_already_exist').hide('slow');
					}, 3000);
				}
			},
			error: function (error) {
				jQuery('.save_shipping_rule_form').removeClass('spinner_disable').val('Save');
			},
		});

		return false;
	}

	/**
	 * Edit Shipping Rule
	 * @param event
	 * @returns {Boolean}
	 */
	//#region Edit Shipping Rule
	if (typeof enUnishippersSmallEditShippingRule != 'function') {
		function enUnishippersSmallEditShippingRule(rule_id, elem) {
			form[0]?.reset();
			jQuery('.en_sr_err').html('');
			setDataAttribute(
				'en_sr_weight_from',
				'en_sr_price_from',
				'en_sr_quantity_from',
				'en_sr_product_tags_list'
			);
			jQuery('#en_sr_product_tags_list').val('');
			jQuery('.select2-selection__choice').hide();

			const postForm = {
				action: 'en_unishippers_small_edit_shipping_rule',
				edit_id: rule_id,
			};

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: postForm,
				dataType: 'json',
				beforeSend: function () {
					jQuery(elem).closest('a').addClass('spinner_disable');
					jQuery(elem).css(
						'background',
						`rgba(255, 255, 255, 1) url("${unishippers_en_small_admin_script.plugins_url}/small-package-quotes-unishippers-edition/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%`
					);
				},
				success: function (data) {
					jQuery(elem).closest('a').removeClass('spinner_disable');
					jQuery(elem).css('background', 'none');

					if (data) {
						jQuery('#edit_sr_form_id').val(data?.rule_data.id);
						jQuery('#en_sr_rule_name').val(data?.rule_data?.name);
						jQuery('#rule_type').val(data?.rule_data?.type);
						jQuery('#en_sr_avialable').prop(
							'checked',
							Number(data?.rule_data?.is_active)
						);

						enUnishippersSmalltoggleRuleTypeFields();

						const settings = JSON.parse(data?.rule_data?.settings) ?? {};

						jQuery('input[name="apply_to"]').val([settings?.apply_to]);
						jQuery('#en_sr_max_items').val(settings?.max_items);
						jQuery('#en_sr_max_weight_per_package').val(
							settings?.max_weight_per_package
						);
						jQuery('#en_sr_filter_name').val(settings?.filter_name);
						jQuery('#en_sr_service_name').val(settings?.service);
						jQuery('#en_sr_service_rate').val(settings?.service_rate);
						jQuery('#filter_by_weight').prop(
							'checked',
							settings?.filter_by_weight === 'true'
						);
						settings?.filter_by_weight === 'true' &&
							removeDataAttribute('en_sr_weight_from');
						jQuery('#en_sr_weight_from').val(settings?.filter_by_weight_from);
						jQuery('#en_sr_weight_to').val(settings?.filter_by_weight_to);
						jQuery('#en_sr_filter_price').prop(
							'checked',
							settings?.filter_by_price === 'true'
						);
						settings?.filter_by_price === 'true' &&
							removeDataAttribute('en_sr_price_from');
						jQuery('#en_sr_price_from').val(settings?.filter_by_price_from);
						jQuery('#en_sr_price_to').val(settings?.filter_by_price_to);
						jQuery('#filter_by_quantity').prop(
							'checked',
							settings?.filter_by_quantity === 'true'
						);
						settings?.filter_by_quantity === 'true' &&
							removeDataAttribute('en_sr_quantity_from');
						jQuery('#en_sr_quantity_from').val(settings?.filter_by_quantity_from);
						jQuery('#en_sr_quantity_to').val(settings?.filter_by_quantity_to);
						jQuery('#filter_by_product_tag').prop(
							'checked',
							settings?.filter_by_product_tag === 'true'
						);
						settings?.filter_by_product_tag === 'true' &&
							removeDataAttribute('en_sr_product_tags_list');
						jQuery('#en_sr_product_tags_list')
							.html(data?.product_tags_markup)
							.trigger('change');
						jQuery('.content').animate({ scrollTop: 0 }, 0);

						// Show popup modal
						window.location.href = jQuery('.en_unishippers_small_add_sr_btn').attr('href');
						setTimeout(function () {
							if (jQuery('.en_wd_add_warehouse_popup').is(':visible')) {
								jQuery('.en_wd_add_warehouse_input > input').eq(0).focus();
							}
						}, 100);
					}
				},
				error: function (error) {
					jQuery(elem).closest('a').removeClass('spinner_disable');
					jQuery(elem).css('background', 'none');
				},
			});

			return false;
		}
	}

	/**
	 * Delete Shipping Rule
	 * @param event
	 * @returns {Boolean}
	 */
	//#region Delete Shipping Rule
	if (typeof enUnishippersSmallDeleteShippingRule != 'function') {
		function enUnishippersSmallDeleteShippingRule(id, elem) {
			const postForm = {
				action: 'en_unishippers_small_delete_shipping_rule',
				delete_id: id,
			};

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: postForm,
				dataType: 'json',
				beforeSend: function () {
					jQuery(elem).closest('a').addClass('spinner_disable');
					jQuery(elem).css(
						'background',
						`rgba(255, 255, 255, 1) url("${unishippers_en_small_admin_script.plugins_url}/small-package-quotes-unishippers-edition/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%`
					);
				},
				success: function (data) {
					jQuery('#sr_row_' + id).remove();
					jQuery('.unishippers_small_sr_deleted').show('slow').delay(3000).hide('slow');
					if (jQuery('.en_unishippers_small_sr_row')?.length) return false;
					
					if (jQuery('.en_unishippers_small_empty_sr_row')?.length) {
						jQuery('.en_unishippers_small_empty_sr_row').show();
					} else {
						const newRowMarkup = `<tr class="new_warehouse_add en_unishippers_small_empty_sr_row" data-id=0><td class="en_wd_warehouse_list_data" colspan="5" style="text-align: center;">No data found!</td></tr>`;
						jQuery('#en_unishippers_small_shipping_rules_list tbody').append(newRowMarkup);
					}
				},
				error: function (error) {},
			});

			return false;
		}
	}

	// #region Update Shipping Rule Status
	function enUnishippersSmallUpdateShippingRuleStatus(elem) {
		const rule_id = jQuery(elem).attr('data-id');
		const is_active = jQuery(elem).attr('data-status');

		// Submit the form to save settings
		const postData = {
			action: 'en_unishippers_small_update_shipping_rule_status',
			rule_id,
			is_active: Number(!Number(is_active)),
		};

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: postData,
			dataType: 'json',
			beforeSend: function () {
				jQuery(elem).addClass('spinner_disable');
				jQuery(elem).css(
					'background',
					`rgba(255, 255, 255, 1) url("${unishippers_en_small_admin_script.plugins_url}/small-package-quotes-unishippers-edition/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%`
				);
			},
			success: function (data) {
				jQuery(elem).removeClass('spinner_disable');
				jQuery(elem).css('background', 'none');

				if (data.update_qry == 1) {
					jQuery(elem).attr('data-status', data?.is_active);
					jQuery(elem).text(Number(data?.is_active) ? 'Yes' : 'No');
					jQuery('.unishippers_small_sr_updated').show('slow').delay(3000).hide('slow');
				}
			},
			error: function (error) {
				jQuery(elem).removeClass('spinner_disable');
				jQuery(elem).css('background', 'none');
			},
		});

		return false;
	}

	// #region Get new row markup
	function enUnishippersSmallgetRowMarkup(rule) {
		const rowMarkup = `
				<td class="en_wd_warehouse_list_data">${rule?.name}</td>
				<td class="en_wd_warehouse_list_data">${rule?.type}</td>
				<td class="en_wd_warehouse_list_data">${rule?.settings?.filter_name}</td>
				<td class="en_wd_warehouse_list_data"><a href="#" class='en_unishippers_small_sr_status_link' data-id='${
					rule?.id
				}' data-status='${rule?.is_active}'>${rule?.is_active ? 'Yes' : 'No'}</a></td>
				<td class="en_wd_warehouse_list_data">
					<!-- Edit rule link -->
					<a href="#" class="en_unishippers_small_sr_edit_link" data-id="${rule?.id}">
						<img src="${
							unishippers_en_small_admin_script.plugins_url
						}/small-package-quotes-unishippers-edition/warehouse-dropship/wild/assets/images/edit.png" title="Edit">
					</a>
					<!-- Delete rule link -->
					<a href="#" class="en_unishippers_small_sr_delete_link" data-id="${rule?.id}">
						<img src="${
							unishippers_en_small_admin_script.plugins_url
						}/small-package-quotes-unishippers-edition/warehouse-dropship/wild/assets/images/delete.png" title="Delete">
					</a>
				</td>
		`;

		return rowMarkup;
	}

	// #region Toggle Rule Type Fields
	function enUnishippersSmalltoggleRuleTypeFields() {
		const type = jQuery('#rule_type').val();

		if (type === 'Override Rates') {
			jQuery('#en_sr_apply_to_cart').hide();
			jQuery('.en_sr_large_cart_settings').hide();
			jQuery('.en_sr_apply_to').show();
			jQuery('.en_sr_override_rates').show();
			jQuery('.en_sr_filters_section').show();
			jQuery('input[name="apply_to"]').val(['shipment']);

			setDataAttribute('en_sr_max_items', 'en_sr_max_weight_per_package');
			removeDataAttribute('en_sr_service_name', 'en_sr_service_rate');
		} else if (type === 'Hide Methods') {
			jQuery('.en_sr_override_rates').hide();
			jQuery('.en_sr_large_cart_settings').hide();
			jQuery('.en_sr_apply_to').show();
			jQuery('#en_sr_apply_to_cart').show();
			jQuery('.en_sr_filters_section').show();
			jQuery('input[name="apply_to"]').val(['cart']);

			setDataAttribute(
				'en_sr_service_name',
				'en_sr_service_rate',
				'en_sr_max_items',
				'en_sr_max_weight_per_package'
			);
		} else if (type === 'Large Cart Settings') {
			jQuery('.en_sr_override_rates').hide();
			jQuery('.en_sr_apply_to').hide();
			jQuery('.en_sr_filters_section').hide();
			jQuery('.en_sr_large_cart_settings').show();
			jQuery('input[name="apply_to"]').val(['cart']);

			setDataAttribute('en_sr_service_name', 'en_sr_service_rate');
			removeDataAttribute('en_sr_max_items', 'en_sr_max_weight_per_package');
		}
	}

	function validateLargeCartSettingsRule() {
		if (jQuery('#rule_type').val() !== 'Large Cart Settings') return true;

		let is_valid = true;
		if (jQuery('#en_sr_max_items').val() <= 0) {
			jQuery('#en_sr_max_items').parent().find('.en_sr_err').html('Max items cannot be 0.');
			is_valid = false;
		}

		if (jQuery('#en_sr_max_weight_per_package').val() <= 0) {
			jQuery('#en_sr_max_weight_per_package').parent().find('.en_sr_err').html(
				'Max weight per package cannot be 0.'
			);
			is_valid = false;
		}

		// Check if max weight is greater than 150 for large cart settings shipping rule
		if (jQuery('#en_sr_max_weight_per_package').val() > 150) {
			jQuery('#en_sr_max_weight_per_package').parent().find('.en_sr_err').html(
				'Max weight per package cannot be greater than 150 lbs.'
			);
			is_valid = false;
		}

		return is_valid;
	}
});
