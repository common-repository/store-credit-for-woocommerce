(function ($) {
	'use strict';

	function fieldsControl() {
		this.init = function () {
			this.fields = ['discount_type_field', 'coupon_amount_field', 'customer_email_field', 'expiry_date_field', 'minimum_amount_field', 'maximum_amount_field', 'individual_use_field', 'restricted_credit_amount_field'];
			this.hideForOtherTypes = ['.store_credit_usage_tab', '.restrict_credit_amount_by_field', '#pi-coupon-restricted-amount'];
			this.detectType();
			this.sendEmails();
			this.sendEmail();
			this.restrictAmountTypeChange();
		}

		this.detectType = function () {
			var parent = this;
			jQuery("#discount_type").on('change', function () {
				var type = jQuery(this).val();
				if (type == 'pi_store_credit') {
					parent.hideFields();
					parent.dataOnlyForOurType(true);
				} else {
					parent.showFields();
					parent.dataOnlyForOurType(false);
				}
				jQuery("#restrict_credit_amount_by").trigger('change');
			});
			jQuery("#discount_type").trigger('change');
		}

		this.hideFields = function () {
			var parent = this;
			jQuery("#coupon_options .form-field").each(function () {
				var element_class = jQuery(this).attr('class');
				var element = this;
				if (!parent.classPresent(element_class)) {
					jQuery(element).fadeOut();
				}
			});
		}

		this.showFields = function () {
			jQuery("#coupon_options .form-field").fadeIn();
		}

		this.dataOnlyForOurType = function (show = true) {

			jQuery.each(this.hideForOtherTypes, function (index, val) {
				if (show) {
					jQuery(val).fadeIn();
				} else {
					jQuery(val).fadeOut();
				}
			})
		}

		this.classPresent = function (classname) {
			var present = false;
			jQuery.each(this.fields, function (index, val) {
				if (classname.includes(val)) {
					present = true;
				}
			});
			return present;
		}

		this.sendEmails = function () {
			var parent = this;
			jQuery(document).on('click', '#send-store-credit-emails', function () {
				var url = jQuery(this).data('href');
				var old_html = jQuery(this).html();
				var button = jQuery(this);
				button.prop('disabled', true).html('Sending Email....');
				parent.sendData(url).always(function () {
					button.html(old_html).prop('disabled', false);
				});
			});
		}

		this.sendEmail = function () {
			var parent = this;
			jQuery(document).on('click', '#send-store-credit-email', function () {
				var url = jQuery(this).data('href');
				var old_html = jQuery(this).html();
				var button = jQuery(this);
				button.prop('disabled', true).html('Sending Email....');
				parent.sendData(url).always(function () {
					button.html(old_html).prop('disabled', false);
				});
			});
		}

		this.sendData = function (url, data = '') {
			return jQuery.ajax({
				url: url,
				method: 'GET',
				data: data
			});
		}

		this.restrictAmountTypeChange = function () {
			var parent = this;
			jQuery("#restrict_credit_amount_by").on('change', function () {
				var type = jQuery(this).val();
				if (type == '') {
					jQuery(".restricted_credit_amount_field").fadeOut();
				} else {
					jQuery(".restricted_credit_amount_field").fadeIn();
					var label = jQuery(this).find(":selected").text();
					jQuery('label[for="restricted_credit_amount"]').html(label);
				}
			});
			jQuery("#restrict_credit_amount_by").trigger('change');
		}
	}

	jQuery(function ($) {
		var fieldsControlObj = new fieldsControl();
		fieldsControlObj.init();
	});

})(jQuery);
