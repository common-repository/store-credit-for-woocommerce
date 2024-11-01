<div id="pi-store-credit-email-form-container" style="display:none">
    <div id="pi-store-credit-email-form-main">
        <div id="pi-store-credit-email-form-header">
        <strong><?php _e('Insert the email id for which the coupon was issued','store-credit-for-woocommerce'); ?></strong>
        <button class="pi-close">&times;</button>
        </div>
        <div class="pi-msg"></div>
        <form id="pi-store-credit-email-form">
            <input id="sc_coupon" name="coupon_code" type="text" placeholder="Coupon code" >
            <input id="sc_billing_email" name="billing_email" type="email" placeholder="Email associated with coupon" >
            <input type="submit" class="button" value="<?php _e('Apply coupon','store-credit-for-woocommerce'); ?>" >
        </form>
    </div>
</div>