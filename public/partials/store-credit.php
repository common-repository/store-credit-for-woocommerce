<table class="store-credit-table woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
		<thead>
			<tr>
                <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr"><?php _e('Store credit code','store-credit-for-woocommerce'); ?></span></th>
                <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-date"><span class="nobr"><?php _e('Credit given','store-credit-for-woocommerce'); ?></span></th>
                <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr"><?php _e('Credit remaining','store-credit-for-woocommerce'); ?></span></th>
                <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-total"><span class="nobr"><?php _e('Expiry date','store-credit-for-woocommerce'); ?></span></th>
                <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-total"><span class="nobr"><?php _e('Apply credit','store-credit-for-woocommerce'); ?></span></th>
            </tr>
		</thead>
		<tbody>
            <?php if(!empty($coupons)) { ?>
            <?php foreach($coupons as $coupon_id): 
                $coupon = new WC_coupon( $coupon_id );
                $amount = get_post_meta($coupon->get_id(), 'coupon_amount', true);
                $code = $coupon->get_code();
                $available = Pi_store_credit_discount::getUserAvailableDiscountAmount($coupon, $email_id);
                $expiry_date = $this->getExpiryDate( $coupon->get_date_expires() );
                $description = $coupon->get_description();
                $expired = self::couponExpired( $coupon );
            ?>
			<tr class=" woocommerce-orders-table__row woocommerce-orders-table__row--status-processing order">
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" style="padding:20px 20px;" data-title="<?php _e('Store credit code','store-credit-for-woocommerce'); ?>">
                    <strong  class="pi-selectable"><?php echo $code; ?></strong> 
                    <?php if(!empty($description)): ?>
                    <span class="pisol-tooltip" tooltip="<?php echo esc_attr($description); ?>">?</span>
                    <?php endif; ?>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="<?php _e('Credit given','store-credit-for-woocommerce'); ?>">
                    <?php echo wc_price(get_post_meta($coupon->get_id(), 'coupon_amount', true)); ?>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number"  data-title="<?php _e('Credit remaining','store-credit-for-woocommerce'); ?>">
                    <?php echo wc_price($available); ?>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number"  data-title="<?php _e('Expiry date','store-credit-for-woocommerce'); ?>">
                    <?php echo $expiry_date ? $expiry_date : '-'; ?>
                </td>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number"  data-title="<?php _e('Expiry date','store-credit-for-woocommerce'); ?>">
                <?php if($available > 0 && !$expired){ ?>
                    <form class="apply-code-form">
                    <input type="hidden" name="billing_email" value="<?php echo esc_attr($email_id); ?>">
                    <input type="hidden" name="coupon_code" value="<?php echo esc_attr($code); ?>">
                    <input class="button" type="submit" value="<?php esc_attr_e('Apply Store Credit','store-credit-for-woocommerce'); ?>">
                    </form>
                <?php } else {
                    _e('Cant be used','store-credit-for-woocommerce');
                } ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php }else{ ?>
                <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-processing order">
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" style="padding:20px 20px; text-align:center;" data-title="<?php _e('You dont have any store credit','store-credit-for-woocommerce'); ?>" colspan="5">
                    <?php _e('You dont have any store credit','store-credit-for-woocommerce'); ?>
                </td>
                </tr>
            <?php } ?>
        </tbody>
	</table>