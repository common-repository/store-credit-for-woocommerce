(function ($) {
	'use strict';

	function handlingCouponAppliedEvent() {

		this.init = function () {
			this.detectCouponAddedSuccess();
			this.close();
			this.open();
			this.submit();
			this.applyCodeButton();
		}

		this.detectCouponAddedSuccess = function () {
			var parent = this;
			$(document).ajaxComplete(function (event, jqxhr, settings) {
				if (settings.url.includes("?wc-ajax=apply_coupon")) {
					if (jqxhr.responseText && jqxhr.responseText.includes("pi-trigger-email-form")) {
						parent.insertEmailPopup();
					}
				}
			});
		}

		this.insertEmailPopup = function () {
			jQuery("#pi-store-credit-email-form-container").fadeIn();
		}

		this.close = function () {
			jQuery(document).on('click', '.pi-close', function () {
				jQuery("#pi-store-credit-email-form-container").fadeOut();
			});
		}

		this.open = function () {
			jQuery(document).on('click', '.pi-open', function () {
				jQuery("#pi-store-credit-email-form-container").fadeIn();
			});
		}

		this.submit = function () {
			var parent = this;
			jQuery(document).on('submit', '#pi-store-credit-email-form', function (e) {
				e.preventDefault();
				parent.formSubmit()
			});
		}

		this.formSubmit = function () {
			var parent = this;
			var t = { coupon_code: '', billing_email: '', security: '' };
			t.coupon_code = jQuery("#sc_coupon").val();
			t.billing_email = jQuery("#sc_billing_email").val();
			t.security = pi_sc_variables.apply_coupon_nonce;


			jQuery("#pi-store-credit-email-form").block({
				message: null,
				overlayCSS: {
					background: "#fff",
					opacity: .6
				}
			});
			jQuery.ajax({
				type: "POST",
				url: pi_sc_variables.wc_ajax_url.toString().replace("%%endpoint%%", "apply_coupon"),
				data: t,
				success: function (e) {
					jQuery(".woocommerce-error, .woocommerce-message").remove();
					jQuery(".woocommerce-form-coupon").before(e);
					jQuery(".woocommerce-cart-form").before(e);
					jQuery("#pi-store-credit-email-form").unblock();
					jQuery("#pi-store-credit-email-form-container").fadeOut();
					jQuery(document.body).trigger("applied_coupon", [t.coupon_code]);
					jQuery(document.body).trigger("applied_coupon_in_checkout", [t.coupon_code]),
						parent.updateCart();
					jQuery(document.body).trigger("update_checkout", {
						update_shipping_method: !1
					});
				},
				dataType: "html"
			});
		}

		this.updateCart = function () {
			jQuery('[name=\'update_cart\']').prop('disabled', false).trigger('click');
		}

		this.applyCodeButton = function () {
			jQuery(document).on('submit', '.apply-code-form', function (e) {
				e.preventDefault();
				var button = jQuery("input[type='submit']", this);
				button.prop('disabled', true).css('opacity', 0.5);
				var t = { coupon_code: '', billing_email: '', security: '' };
				t.coupon_code = jQuery("input[name='coupon_code']", this).val();
				t.billing_email = jQuery("input[name='billing_email']", this).val();
				t.security = pi_sc_variables.apply_coupon_nonce;
				jQuery.ajax({
					type: "POST",
					url: pi_sc_variables.wc_ajax_url.toString().replace("%%endpoint%%", "apply_coupon"),
					data: t,
					success: function (e) {
						jQuery(".woocommerce-error, .woocommerce-message").remove();
						jQuery(".store-credit-table").before(e);
					},
					dataType: "html"
				}).always(function () {
					button.prop('disabled', false).css('opacity', 1);
				});
			})
		}
	}

	jQuery(function ($) {
		var handlingCouponAppliedEventObj = new handlingCouponAppliedEvent();
		handlingCouponAppliedEventObj.init();

		var addToCartProductObj = new addToCartProduct();
		addToCartProductObj.init();
	});

	function addToCartProduct() {
		this.init = function () {
			this.messageCount();
		}

		this.messageCount = function () {
			var parent = this;
			jQuery('#send_message').on('input propertychange', function () {
				parent.message_characters_remaining();
			});
			this.message_characters_remaining();
		}

		this.message_characters_remaining = function () {
			var field = jQuery("#sc-characters-remaining");
			var charsRemaining = jQuery('#send_message').attr('maxlength');

			var messageElement = jQuery('#send_message').val();
			if (messageElement) {
				charsRemaining -= messageElement.length;
			}

			field.text(charsRemaining);
		}
	}

})(jQuery);
