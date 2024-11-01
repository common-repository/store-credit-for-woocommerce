<?php
/**
 * Simple custom product
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

$options = $product->get_amount_options();

$amount = !empty($_REQUEST['amount']) ? $_REQUEST['amount'] : '';
?>
<div class="store-credit-form-container" id="sc-price-container">
		<div class="sc-form-field">
			<label for="send_to"><?php _e('Coupon Amount', 'store-credit-for-woocommerce'); ?></label>
			<?php
            echo '<select name="amount">';
            foreach($options as $option){
                echo sprintf('<option value="%s" %s >%s</option>', $option, selected($amount, $option, false ), wc_price($option));
            }
            echo '</select>';
            ?>
		</div>
</div>