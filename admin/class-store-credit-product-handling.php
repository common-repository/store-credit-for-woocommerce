<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Pi_store_credit_product_type_handler{

    protected static $instance = null;

    public static function get_instance( ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    

    protected function __construct( ){

        $this->meta_labels = [
            'pi_sc_emails' => __('Send to', 'store-credit-for-woocommerce'),
            'pi_sc_from' => __('Send from', 'store-credit-for-woocommerce'),
            'pi_sc_message' => __('Message', 'store-credit-for-woocommerce'),
            'pi_sc_expiry_days' => __('Days to expire', 'store-credit-for-woocommerce')
        ];

        $this->message_size = 400;

        add_filter( 'product_type_selector', [$this, 'addProductType'] );

        add_action( 'woocommerce_product_data_panels', [$this, 'dataPanel'] );
        add_filter( 'woocommerce_product_data_tabs', [$this, 'dataTab'] );

        add_action( 'woocommerce_process_product_meta', [$this, 'saveFields'] );

        add_action( 'woocommerce_single_product_summary', [$this, 'template'], 60 );

        add_filter( 'woocommerce_product_add_to_cart_text', [$this,'loopAddToCart'], 10, 2 );

        add_action('woocommerce_add_to_cart_handler_pi_store_credit_range', [$this, 'addToCartHandlerForRange']);

        add_action('woocommerce_add_to_cart_handler_pi_store_credit_option', [$this, 'addToCartHandlerForRange']);

        /**
         * this sets the price of the coupon
         */
        add_filter( 'woocommerce_get_cart_item_from_session', [ __CLASS__,'setPriceInCart'], PHP_INT_MAX, 3 );

        add_filter( 'woocommerce_get_item_data', [$this,'showExtraDate'], 10, 2 );

        add_action( 'woocommerce_new_order_item', array($this,'add_order_item_meta'), 10, 3 );

        add_filter( 'woocommerce_attribute_label', array( $this, 'woocommerce_attribute_label' ), 10, 3 );

        add_filter('woocommerce_hidden_order_itemmeta', array($this, 'hidden_order_item_meta'), 50);

        add_action( 'woocommerce_order_status_completed', array( $this, 'woocommerce_order_status_completed' ), 11, 2 );

        add_action( 'woocommerce_order_item_meta_end', [$this, 'showGeneratedCoupons'], 10, 2);
        add_action( 'woocommerce_after_order_itemmeta', [$this, 'showGeneratedCoupons'], 10, 2);

        add_filter( 'woocommerce_cart_item_quantity', [$this, 'disableQuantity'], 10, 3 );
    }

    function addProductType( $types ){
        $types[ 'pi_store_credit_range' ] = __( 'Custom amount store credit', 'store-credit-for-woocommerce' );
        $types[ 'pi_store_credit_option' ] = __( 'Predefined store credit', 'store-credit-for-woocommerce' );
        return $types;
    }

    function dataTab( $tabs ){
        $tabs['pi_store_credit_range'] = array(
            'label'	 => __( 'Store credit', 'store-credit-for-woocommerce' ),
            'target' => 'pi_store_credit_range',
            'class'  => array('show_if_pi_store_credit_range'),
            'priority'  => 5
        );
        $tabs['pi_store_credit_option'] = array(
            'label'	 => __( 'Store credit', 'store-credit-for-woocommerce' ),
            'target' => 'pi_store_credit_options',
            'class'  => array('show_if_pi_store_credit_option'),
            'priority'  => 5
        );

        $tabs['pi_store_credit_expiry'] = array(
            'label'	 => __( 'Coupon expiry days', 'store-credit-for-woocommerce' ),
            'target' => 'pi_store_credit_expiry',
            'class'  => array('show_if_pi_store_credit_option', 'show_if_pi_store_credit_range'),
            'priority'  => 6
        );
        return $tabs;
    }

    function dataPanel(){
        include_once 'partials/data-panel.php';
    }

    function saveFields( $post_id ){
        if ( isset( $_POST['_min_amount'] ) ) :
            update_post_meta( $post_id, '_min_amount', sanitize_text_field( $_POST['_min_amount'] ) );
        endif;

        if ( isset( $_POST['_max_amount'] ) ) :
            update_post_meta( $post_id, '_max_amount', sanitize_text_field( $_POST['_max_amount'] ) );
        endif;

        if ( isset( $_POST['_expiry_days'] ) ) :
            update_post_meta( $post_id, '_expiry_days', absint( $_POST['_expiry_days'] ) );
        endif;

        if ( isset( $_POST['_pi_options'] ) ) :
            $val = explode('|', $_POST['_pi_options']);
            $val = array_map('trim',$val);
            $val = array_map('sanitize_text_field',$val);
            update_post_meta( $post_id, '_pi_options', $val );
        endif;
    }

    function template(){
        global $product;
	    if ( 'pi_store_credit_range' == $product->get_type() ||  'pi_store_credit_option' == $product->get_type() ) {
            $template_path = plugin_dir_path( __FILE__ ) . 'templates/';

            wc_get_template( 'single-product/add-to-cart/add-to-cart.php',
			'',
			'',
			trailingslashit( $template_path ) );
        }
    }

    function loopAddToCart( $text, $product ) {
        if ( is_a( $product, 'WC_Product_Pi_Store_Credit_Option' ) || is_a( $product, 'WC_Product_Pi_Store_Credit_Range' )  ) {
            return  __( 'Select amount', 'store-credit-for-woocommerce' );
        } else {
            return $text;
        }
    }
    

    function addToCartHandlerForRange( $redirect_url ){
        

        $product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( wp_unslash( $_REQUEST['add-to-cart'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        $amount = empty( $_REQUEST['amount'] ) ? 0 :  wp_unslash( $_REQUEST['amount'] );

        
        $send_to_emails = empty( $_REQUEST['send_to'] ) ? '' :  $_REQUEST['send_to'] ;

        $send_to = !empty($send_to_emails) ? explode(',',  $send_to_emails) : [];

        $send_to = array_map('trim', $send_to);

        $quantity = is_array($send_to) && !empty($send_to) ? count($send_to) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        $send_from = empty( $_REQUEST['send_from'] ) ? '' :  sanitize_text_field( $_REQUEST['send_from'] );

        $send_message = empty( $_REQUEST['send_message'] ) ? '' :  sanitize_textarea_field( $_REQUEST['send_message'] );

        $validation = $this->validateRange($product_id, $amount, $send_to, $send_from, $send_message);

		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', $validation, $product_id, $quantity );

		if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, 0, [], ['store_credit_amount' => $amount, 'send_to' => $send_to, 'send_from' => $send_from, 'send_message' => $send_message] ) ) {
			wc_add_to_cart_message( array( $product_id => $quantity ), true );
			return true;
		}
		return false;
    }

    function validateRange($product_id, $amount, $send_to, $send_from, $send_message){
        $return = true;

        $product = wc_get_product( $product_id );

        if(empty($product) || !is_object($product)){
            wc_add_notice( __('Incorrect product id', 'store-credit-for-woocommerce'), 'error' );
            return false;
        }



        if(empty($amount)){
            wc_add_notice( __('Coupon amount cant be empty or Zero', 'store-credit-for-woocommerce'), 'error' );
            $return = false;
        }

        if( $product->is_type( 'pi_store_credit_range' ) ){
            $min_amount = get_post_meta( $product_id, '_min_amount', true);
            $max_amount = get_post_meta( $product_id, '_max_amount', true);

            if(!empty($min_amount) && $amount < $min_amount){
                wc_add_notice( sprintf(__('Coupon amount should be grater then or equal to %s', 'store-credit-for-woocommerce'), wc_price($min_amount)), 'error' );
                $return = false;
            }

            if(!empty($max_amount) && $amount > $max_amount){
                wc_add_notice( sprintf(__('Coupon amount should be less then or equal to %s', 'store-credit-for-woocommerce'), wc_price($max_amount)), 'error' );
                $return = false;
            }
        }elseif($product->is_type( 'pi_store_credit_option' )){
            $options = get_post_meta( $product_id, '_pi_options', true);
            if(is_array($options) && !in_array($amount, $options)){
                wc_add_notice( __('Please select the amount given in the product', 'store-credit-for-woocommerce'), 'error' );
                $return = false;
            }
        }

        if(!is_numeric($amount)){
            wc_add_notice( __('Coupon amount must be positive number', 'store-credit-for-woocommerce'), 'error' );
            $return = false;
        }

        if(is_numeric($amount) && $amount < 0){
            wc_add_notice( __('Coupon amount must be positive number grater then Zero', 'store-credit-for-woocommerce'), 'error' );
            $return = false;
        }

        if(!empty($send_to)){
            foreach($send_to as $email){
                if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                    wc_add_notice( sprintf(__('Invalid email id %s', 'store-credit-for-woocommerce'), esc_html($email)), 'error' );
                    $return = false;
                }
            }
        }else{
            wc_add_notice( sprintf(__('Send to email id cant be left empty', 'store-credit-for-woocommerce')), 'error' );
            $return = false;
        }

        return $return;
    }

    static function setPriceInCart( $cart_item, $values, $key ) {
        if ( $cart_item['data']->is_type( 'pi_store_credit_range' )  || $cart_item['data']->is_type('pi_store_credit_option')) {
            //Modify the price here
            $amount = $cart_item['store_credit_amount'];
            $cart_item['data']->set_price( $amount );
            $cart_item['data']->set_sale_price( $amount );
            $cart_item['data']->set_regular_price( $amount );

            /**
             * this is needed else user will change the cart quantity and you will loose the amount
             */
            $send_to = $cart_item['send_to'];

            $quantity = is_array($send_to) && !empty($send_to) ? count($send_to) : 1;

            $cart_item['quantity'] = $quantity;

            
        }
        return $cart_item;
    }

    function showExtraDate( $other_data, $cart_item ){
        if($cart_item['data']->is_type( 'pi_store_credit_range' )  || $cart_item['data']->is_type('pi_store_credit_option')){
            if(isset($cart_item['send_to'])){
                $emails = is_array($cart_item['send_to']) ? implode(', ', $cart_item['send_to']) : $cart_item['send_to'];

                $other_data[] = [
                    'name' => __('Send to', 'store-credit-for-woocommerce'),
                    'value' => $emails
                ];
            }

            if(isset($cart_item['send_from']) && !empty($cart_item['send_from'])){
                $other_data[] = [
                    'name' => __('Send from', 'store-credit-for-woocommerce'),
                    'value' => $cart_item['send_from']
                ];
            }

            if(isset($cart_item['send_message']) && !empty($cart_item['send_message'])){
                $other_data[] = [
                    'name' => __('Message', 'store-credit-for-woocommerce'),
                    'value' => $cart_item['send_message']
                ];
            }
        }
        return $other_data;
    }

    function add_order_item_meta($item_id, $item, $order_id){
        if(is_a($item, 'WC_Order_Item_Product')){
            $product = $item->get_product();

            if(!is_object($product)) return;

            if($product->is_type( 'pi_store_credit_range' ) || $product->is_type('pi_store_credit_option')){
                $amount = isset($item->legacy_values['store_credit_amount']) ? $item->legacy_values['store_credit_amount'] : '';

                $send_to = isset($item->legacy_values['send_to']) ? $item->legacy_values['send_to'] : '';

                $emails = is_array($send_to) ? implode(', ', $send_to) : $send_to;

                $send_from = isset($item->legacy_values['send_from']) ? $item->legacy_values['send_from'] : '';

                $send_message = isset($item->legacy_values['send_message']) ? $item->legacy_values['send_message'] : '';

                $expiry_days = $product->get_meta('_expiry_days');
                
                wc_add_order_item_meta( $item_id, '_pi_sc_amount', $amount );

                wc_add_order_item_meta( $item_id, 'pi_sc_emails', $emails );
                
                wc_add_order_item_meta( $item_id, 'pi_sc_from', $send_from );

                wc_add_order_item_meta( $item_id, 'pi_sc_message', $send_message  );

                wc_add_order_item_meta( $item_id, 'pi_sc_expiry_days', $expiry_days );
            }
        }
    }

    function woocommerce_attribute_label( $label, $name, $product ){
        if ( isset( $this->meta_labels[ $label ] ) ) {
            return $this->meta_labels[ $label ];
        }

        return $label;
    }

    function hidden_order_item_meta( $args ){
        $args[] = '_pi_sc_amount';
        return $args;
    }

    function woocommerce_order_status_completed( $order_id, $order ) {
        $this->addStoreCreditToOrder( $order_id, $order);
    }

    function addStoreCreditToOrder( $order_id, $order ){

        foreach ( $order->get_items() as $order_item_id => $order_item ) {

            $product = $order_item->get_product();

            if( ! ($product->is_type( 'pi_store_credit_range' ) || $product->is_type('pi_store_credit_option')) ) continue;


            $quantity = $order_item->get_quantity();

            $amount = $order_item->get_meta('_pi_sc_amount');

            $emails = explode(',', $order_item->get_meta('pi_sc_emails'));
            $emails = array_map('trim', $emails);

            $from = $order_item->get_meta('pi_sc_from');

            $message = $order_item->get_meta('pi_sc_message');

            $coupons = $order_item->get_meta('pi_sc_generated_coupons');

            $expiry_days = $order_item->get_meta('pi_sc_expiry_days');

            if(empty($coupons) || !is_array($coupons)){
                $coupons = [];
            }

            $count = 10;
            $gap_between_two_email = apply_filters('pi_store_credit_email_gap', 100);
            foreach($emails as $email){

                if(!isset($coupons[$email])){
                    $coupon = $this->createCoupon($email, $amount, $from, $message, $expiry_days);
                    $coupon_id = $coupon->get_id();
                    $coupons[$email] = $coupon_id;
                    $this->scheduleEmail($coupon_id, $email, $count);
                }
                $count += $gap_between_two_email;
            }

            wc_add_order_item_meta( $order_item->get_id(), 'pi_sc_generated_coupons', $coupons );
        }
    }

    function createCoupon($email, $amount, $from, $message, $expiry_days){
        $coupon = new WC_Coupon();
        $code = $this->randomCouponCode();

        $coupon->set_code( $code );
        $coupon->set_discount_type( 'pi_store_credit' );
        $coupon->set_amount( $amount );

        if(!empty($from)){
            $header = sprintf(__('You got a gift coupon of %s from %s', 'store-credit-for-woocommerce'), wc_price($amount), esc_html($from));
            $subject = sprintf(__('You got a gift coupon from %s', 'store-credit-for-woocommerce'), esc_html($from));
        }else{
            $header = sprintf(__('You got a gift coupon of %s', 'store-credit-for-woocommerce'), wc_price($amount), esc_html($from));
            $subject = sprintf(__('You got a gift coupon', 'store-credit-for-woocommerce'));
        }
        $coupon->add_meta_data( 'pi_sc_header' , $header, true);
        $coupon->add_meta_data( 'pi_sc_subject' , $subject, true);

        if(!empty($message)){
            $coupon->add_meta_data( 'pi_sc_top_description' , $message, true);
        }

        if(!empty($expiry_days)){
            $expiry_date = $this->expiryDate( $expiry_days );
            $coupon->set_date_expires( $expiry_date );
        }

        $coupon->set_email_restrictions( 
            array( 
                $email
            )
        );
        $coupon->save();

        return $coupon;
    }

    function randomCouponCode($length = 6){
        $prefix = 'sc-';

        $timestamp = current_time( 'timestamp' );

        $md5 = md5($timestamp);

        $final = substr( str_shuffle( $md5 ),  0, $length );

        return $prefix.$final;
    }

    function expiryDate( $expire_after_days ){
        $today = current_time( 'Y/m/d');
        $expiry = date('d-m-Y', strtotime("+{$expire_after_days} days", strtotime($today)));
        return $expiry;
    }

    function scheduleEmail( $coupon_id, $email, $count = 10 ){
        if ( ! wp_next_scheduled( Pi_store_credit_coupon::$event, [$coupon_id, $email] ) ) {
            wp_schedule_single_event( time() + $count, Pi_store_credit_coupon::$event, [$coupon_id, $email]);
        }
    }

    function showGeneratedCoupons($item_id, $item ){
        $coupons = $item->get_meta('pi_sc_generated_coupons');
        if(!empty($coupons) && is_array($coupons)){
            echo '<div style="margin:7px 0px">';
            echo '<label>'.__('Generated Coupons','store-credit-for-woocommerce').': </label>';
            foreach($coupons as $email => $coupon_id){
                $coupon = new WC_coupon($coupon_id);

                if(! $coupon ) continue;

                $code = $coupon->get_code();

                echo sprintf('<strong title="%s" style="padding:6px 10px; border:1px solid #ccc; font-size:15px; display:inline-block; margin-right:10px;">%s</strong>', $email, $code);
            }
            echo '</div>';
        }
    }

    function disableQuantity($product_quantity, $cart_item_key, $cart_item ){
        if( is_cart() && ($cart_item['data']->is_type( 'pi_store_credit_range' ) || $cart_item['data']->is_type('pi_store_credit_option'))){
            $product_quantity = sprintf( '%2$s <input type="hidden" name="cart[%1$s][qty]" value="%2$s" />', $cart_item_key, $cart_item['quantity'] );
        }

        return $product_quantity;
    }
}

Pi_store_credit_product_type_handler::get_instance();