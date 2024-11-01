<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Automattic\WooCommerce\Utilities\NumberUtil;

class Pi_store_credit_discount{

    protected static $instance = null;

    public static function get_instance( ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    protected function __construct( ){
        //add_filter('woocommerce_coupon_get_discount_amount', [ __CLASS__,'discountAmount' ], 10, 5 );
        add_filter('woocommerce_coupon_get_amount', [ __CLASS__,'getAmount' ], 10, 2 );

        add_filter('woocommerce_coupon_get_discount_type', [ __CLASS__,'couponDiscountType' ], 10, 2 );

        add_filter( 'woocommerce_cart_coupon_types', [ __CLASS__, 'cartSupportDiscountType'], 10);
    }

    static function couponDiscountType( $discount_type, $coupon ){
        if( !self::isCreditCoupon( $coupon->get_id() ) ) return $discount_type;

        if(function_exists('get_current_screen')){
            $current_screen = get_current_screen();
            if( !empty($current_screen) && is_object($current_screen) &&  in_array($current_screen->id, ['edit-shop_coupon', 'shop_coupon']) ){
                return $discount_type;
            }
        }

        return 'fixed_cart';
    }

    static function getAmount( $amt, $coupon ){
        if( !self::isCreditCoupon( $coupon->get_id() ) ) return $amt;

        if(function_exists('get_current_screen')){
            $current_screen = get_current_screen();
            if( !empty($current_screen) && is_object($current_screen) &&  in_array($current_screen->id, ['edit-shop_coupon', 'shop_coupon']) ){
                return $amt;
            }
        }

        if(isset($_POST['action']) && $_POST['action'] == 'woocommerce_add_coupon_discount'){
            $order_id = isset($_POST['order_id']) ? $_POST['order_id'] : '';
            $order = wc_get_order($order_id);

            if(!is_object($order)) return 0;

            $email = $order->get_billing_email();
            $cart_subtotal = $order->get_subtotal(); 

            $total_amount_available = self::getUserAvailableDiscountAmount( $coupon, $email, $order_id );

            $amount_that_can_be_used = self::amountThatCanBeUsedInOneOrder( $coupon, $total_amount_available,  $cart_subtotal);

            return $amount_that_can_be_used;
        }

        if(!function_exists('WC') || !is_object(WC()->cart)) return 0;

        $cart_subtotal = WC()->cart->subtotal;
        $total_amount_available = self::getUserAvailableDiscountAmount( $coupon, '' );
        $amount_that_can_be_used = self::amountThatCanBeUsedInOneOrder( $coupon, $total_amount_available,  $cart_subtotal);

        return $amount_that_can_be_used;
    }

    /**
     * old implementation to be deleted
     */
    static function discountAmount($discount, $discounting_amount, $cart_item, $single, $coupon ){

        if( ! self::isCreditCoupon( $coupon->get_id() ) ) return $discount;

        /**
         * This part is form WooCommerce \includes\class-wc-coupon.php fixed_cart discount type
         */
        if(is_array($cart_item)){
            $cart_item_qty = is_null( $cart_item ) ? 1 : $cart_item['quantity'];

            if ( wc_prices_include_tax() ) {
                $cart_subtotal = WC()->cart->subtotal;
                $discount_percent = ( wc_get_price_including_tax( $cart_item['data'] ) * $cart_item_qty ) / WC()->cart->subtotal;
            } else {
                $cart_subtotal = WC()->cart->subtotal_ex_tax;
                $discount_percent = ( wc_get_price_excluding_tax( $cart_item['data'] ) * $cart_item_qty ) / WC()->cart->subtotal_ex_tax;
            }

            $email = '';

            $total_amount_available = self::getUserAvailableDiscountAmount( $coupon, $email );
        }else{
            $cart_item_qty = is_null( $cart_item ) ? 1 : $cart_item['quantity'];
            $order_id = $cart_item['order_id'];
            $order = wc_get_order($order_id);
            $cart_subtotal =   $order->get_subtotal(); 
            $cost_without_tax = $cart_item['subtotal'] - $cart_item['subtotal_tax'];
            $discount_percent = ( $cost_without_tax ) / $cart_subtotal;  

            $email = $order->get_billing_email();

            $total_amount_available = self::getUserAvailableDiscountAmount( $coupon, $email, $order_id );
        }

        $amount_that_can_be_used = self::amountThatCanBeUsedInOneOrder( $coupon, $total_amount_available,  $cart_subtotal);
       

        $discount = ( (float) $amount_that_can_be_used * $discount_percent ) / $cart_item_qty;

        //error_log('Discount percent '.$discount_percent);
        //error_log('Qty '.$cart_item_qty);
        
        return NumberUtil::round( min( $discount, $discounting_amount ), wc_get_rounding_precision() );
    }

    static function isCreditCoupon( $id ){
        $type = get_post_meta($id, 'discount_type', true);

        if($type == 'pi_store_credit') return true;

        return false;
    }

    static function cartSupportDiscountType( $discount_types ){
        $discount_types[] = 'pi_store_credit';
        return $discount_types;
    }

    static function getUserAvailableDiscountAmount( $coupon, $email_id = '', $excluded_order_id = ''){
        if(empty($email_id)){
            $email_id = self::getEmailId( $coupon );
        }

        if(empty($email_id)) return 0;

        $email_id = strtolower($email_id);

        $orders = get_post_meta($coupon->get_id(), $email_id);
        //error_log(print_r($orders, true));
        $orders = is_array($orders) ? array_unique($orders) : $orders;

        /**
         * this is needed as when applying the coupon from backend 
         * the order on which the coupon is applied will apply the coupon and also count its own applied amount as well and give wrong result
         */
        if(!empty($excluded_order_id)){
            $orders = array_diff($orders, [$excluded_order_id]);
        }

        /**
         * cant use get_amount function as it will lead to infinite loop
         */
        //$amount = $coupon->get_amount(); 

        $amount = get_post_meta($coupon->get_id(), 'coupon_amount', true);
        
        if(empty( $orders )) return $amount;

        $total_discount_given = self::getTotalDiscountAmount( $orders,  $coupon->get_id());

        return $amount >= $total_discount_given ? ($amount - $total_discount_given) : 0;

    }

    static function get_orders( $coupon_id, $email_id ){
        $orders = get_post_meta($coupon_id, $email_id);
        $orders = is_array($orders) ? array_unique($orders) : [];
        return $orders;
    }

    static function amountThatCanBeUsedInOneOrder( $coupon, $total_amount_available, $cart_sub_total ){

        $restriction_type = get_post_meta( $coupon->get_id(), 'restrict_credit_amount_by', true);

        $restricted_amount = get_post_meta( $coupon->get_id(), 'restricted_credit_amount', true);

        if(empty($restricted_amount)) $restricted_amount = 0;

        if(empty($restriction_type)) return $total_amount_available;

        
        if($restriction_type == 'fixed-amount'){
            if($total_amount_available >= $restricted_amount){
                return $restricted_amount;
            }else{
                return $total_amount_available;
            }
        }

        if($restriction_type == 'percent-of-cart-subtotal'){
            $percent_of_subtotal = (float)($restricted_amount * $cart_sub_total / 100);
            if($total_amount_available >= $percent_of_subtotal){
                return $cart_sub_total >= $percent_of_subtotal ? $percent_of_subtotal : $cart_sub_total;
            }else{
                return $total_amount_available;
            }
        }
    }

    static function getTotalDiscountAmount( $orders_id, $coupon_id ){
        $total_discount = 0;
        foreach( $orders_id as $order_id){
            $order = wc_get_order( $order_id );

            if(! $order ) continue;

            $status = $order->get_status();
            if(in_array($status, ['trash', 'refunded', 'cancelled', 'failed'])) continue;

            $coupons = $order->get_items( 'coupon' );

            if(empty( $coupons )) continue;

            foreach ( $coupons as $item_id => $item ) :
                $stored_coupon_data = $item->get_meta('coupon_data');
                
                if(!isset($stored_coupon_data['id'])){
                    $code = $item->get_code();
                    $applied_coupon_id = wc_get_coupon_id_by_code($code);

                    if(empty($applied_coupon_id)) continue;

                }else{
                    $applied_coupon_id = $stored_coupon_data['id'];
                }

                if($applied_coupon_id == $coupon_id){
                    $discount_given = $item->get_discount();
                    $total_discount += $discount_given;
                }
            endforeach;
        }
        return $total_discount;
    }

    static function getEmailId($coupon){
        if(isset($_POST['billing_email']) && !apply_filters('pisol_scfw_force_registered_email_id', false)) {
            $billing_email_id = sanitize_text_field($_POST['billing_email']);
        }elseif(function_exists('WC') && is_object(WC()->session)){
            $billing_email_id = WC()->session->get('billing_email', '');
        }

        if(empty($billing_email_id)) return ;

        $billing_email_id = strtolower($billing_email_id);

        $coupon_email_id = get_post_meta($coupon->get_id(), 'customer_email', true);

        if(is_array($coupon_email_id)){
            $coupon_email_id = array_map('strtolower', $coupon_email_id);
        }

        if(in_array($billing_email_id, $coupon_email_id)) return $billing_email_id;

        return ;
    }

    static function getAllEmailId( $coupon_id ){
        $all_meta_data = get_post_meta( $coupon_id );
        $direct_email = get_post_meta( $coupon_id, 'customer_email', true );
        $emails = [];
        foreach($all_meta_data as $key => $data){
            if( is_email( $key ) ){
                $emails[] = $key;
            }
        }
        $final_list = is_array($direct_email) ? array_merge($emails, $direct_email) : $emails;

        if(is_array($final_list)) $final_list = array_map('strtolower', $final_list);

        return array_unique( $final_list );
    }
}

Pi_store_credit_discount::get_instance();