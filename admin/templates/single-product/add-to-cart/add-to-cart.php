<?php
/**
 * Simple custom product
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

$type = $product->get_type();

$send_to = !empty($_REQUEST['send_to']) ? $_REQUEST['send_to'] : '';
$send_from = !empty($_REQUEST['send_from']) ? $_REQUEST['send_from'] : '';
$send_message = !empty($_REQUEST['send_message']) ? $_REQUEST['send_message'] : '';

$message_size = apply_filters('pi_store_credit_message_size', 400);
?>
<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>
<form class="pi_store_credit" method="post" enctype='multipart/form-data'>
    <?php if($type == 'pi_store_credit_range') { 
        include_once 'range.php';
    }elseif($type == 'pi_store_credit_option'){
        include_once 'option.php';
    }
    ?>
	<div class="store-credit-form-container" id="sc-detail-form">
		<div class="sc-form-field">
			<label for="send_to"><?php _e('Send to', 'store-credit-for-woocommerce'); ?></label>
			<input type="text" name="send_to" id="send_to" value="<?php echo esc_attr($send_to); ?>" required placeholder="<?php echo esc_attr(__('Recipients email address','store-credit-for-woocommerce')); ?>">
			<div class="sc-subtitle"><?php _e( 'Separate multiple email addresses by comma', 'store-credit-for-woocommerce' ); ?></div>
		</div>
		<div class="sc-form-field">
			<label for="send_from"><?php _e('From', 'store-credit-for-woocommerce'); ?></label>
			<input type="text" name="send_from" id="send_from" value="<?php echo esc_attr($send_from); ?>" placeholder="<?php echo esc_attr(__('Your name','store-credit-for-woocommerce')); ?>" required>
		</div>
		<div class="sc-form-field">
			<label for="send_message"><?php _e('Message (optional)', 'store-credit-for-woocommerce'); ?></label>
			<textarea name="send_message" id="send_message" maxlength="<?php echo esc_attr($message_size); ?>"><?php echo esc_attr($send_message); ?></textarea>
			<div class="sc-subtitle"><span id="sc-characters-remaining"><?php echo $message_size; ?></span> <?php _e( 'characters remaining', 'store-credit-for-woocommerce' ); ?></div>
		</div>
	</div>
	<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
</form>
<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
