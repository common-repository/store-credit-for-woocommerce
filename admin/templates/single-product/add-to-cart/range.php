<?php
/**
 * Simple custom product
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

$min_amount = $product->get_min_amount();
$max_amount = $product->get_max_amount();
$amount = !empty($_REQUEST['amount']) ? $_REQUEST['amount'] : '';
?>
<div class="store-credit-form-container" id="sc-price-container">
		<div class="sc-form-field">
			<label for="send_to"><?php _e('Coupon Amount', 'store-credit-for-woocommerce'); ?></label>
			<input type='number' name="amount" min="<?php echo esc_attr($min_amount); ?>"  max="<?php echo esc_attr($max_amount); ?>" value="<?php echo esc_attr($amount); ?>">
		</div>
</div>