<?php

class pi_store_credit_reminder_email{
    function __construct( $coupon_id, $email_id ){
        $this->coupon_id = $coupon_id;
        $this->email_id = $email_id;
        $this->coupon_data = $this->get_coupon_data();
    }

    static function sendStoreCreditReminderEmail( $coupon_id, $email_id ){
        $obj = new self( $coupon_id, $email_id );

        return $obj->sendEmail();
    }   

    function sendEmail(){

        if( $this->coupon_data === false) return false;
        
        $headers = array('Content-Type: text/html; charset=UTF-8');

        $email = $this->get_email();

        $subject = $this->get_subject();

        $message = $this->get_message();
         
        if(wp_mail($email, $subject, $message, $headers)){
           return true;
        }

        return false;
    }

    function get_coupon_data(){
        $coupon = new WC_Coupon( $this->coupon_id );
        if( ! $coupon ) return false;

        $coupons_data['amount'] = wc_price( get_post_meta($coupon->get_id(), 'coupon_amount', true) );
        $coupons_data['code'] = $coupon->get_code();
        $coupons_data['expiry_date'] = $this->getExpiryDate( $coupon->get_date_expires());
        $coupons_data['description'] = $coupon->get_description();

        return $coupons_data;
    }

    function get_email(){
        return $this->email_id;
    }

    function get_subject(){
        $subject = get_option('pi_store_credit_expiry_email_subject', __('Your store credit will expire on [expiry_date]', 'store-credit-for-woocommerce'));
        $subject = strip_tags($this->replaceDetails( $subject ));
        return $subject;
    }

    function get_message(){
        $header = $this->header();
        $content = $this->content();
        $footer = $this->footer();

        $message = $header.$content.$footer;

        $message = $this->replaceDetails( $message );
        return $message;
    }

    function getExpiryDate($expiry_obj){
        if(empty($expiry_obj)) return;

        $format = apply_filters('pi_store_credit_expiry_date_format', 'M d, Y');
        return $expiry_obj->date( $format );
    }

    function replaceDetails( $message ){
        $search_replace = [];
        foreach($this->coupon_data as $key => $val){
            $search_replace['['.$key.']'] = $val;
        }

        $message = str_replace(array_keys($search_replace), array_values($search_replace), $message);

        $message = $this->wc_replace_placeholders( $message );
        return $message;
    }

    function header(){
        $header = get_option('pi_store_credit_expiry_email_header', __('Store credit of [amount] will expire on [expiry_date]', 'store-credit-for-woocommerce'));

        ob_start();  
        include('partials/email/email-header.php');
        $header = ob_get_contents();  
        ob_end_clean();
        return $header;
    }

    function footer(){
        ob_start();  
        include('partials/email/email-footer.php');
        $footer = ob_get_contents();  
        ob_end_clean();
        return $footer;
    }

    function content(){
        $top_desc = get_option('pi_store_credit_expiry_email_top_desc', __('To redeem your store credit use the following code during checkout', 'store-credit-for-woocommerce'));

        $bottom_desc = get_option('pi_store_credit_expiry_email_bottom_desc', __('This credit can be used multiple times till its balance finishes or it expires', 'store-credit-for-woocommerce'));

        $read_more_url = get_option('pi_store_credit_expiry_email_read_more_url', '');

        $read_more = get_option('pi_store_credit_expiry_email_read_more', __('Read more', 'store-credit-for-woocommerce'));

        $expiry_date = isset($this->coupon_data['expiry_date']) ? $this->coupon_data['expiry_date'] : '';

        $expiry_msg = get_option('pi_store_credit_expiry_email_expiry_msg', __('The coupon will expire on [expiry_date]', 'store-credit-for-woocommerce'));

        ob_start();  
        include('partials/email/email-content.php');
        $footer = ob_get_contents();  
        ob_end_clean();
        return $footer;
    }

    public function wc_replace_placeholders( $string ) {
		$domain = wp_parse_url( home_url(), PHP_URL_HOST );

		return str_replace(
			array(
				'{site_title}',
				'{site_address}',
				'{site_url}',
				'{woocommerce}',
				'{WooCommerce}',
			),
			array(
				$this->get_blogname(),
				$domain,
				$domain,
				'<a href="https://woocommerce.com">WooCommerce</a>',
				'<a href="https://woocommerce.com">WooCommerce</a>',
			),
			$string
		);
	}

    private function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

}

add_action('wp_footer', function(){
    //pi_store_credit_email::sendStoreCreditEmail(104, 'rajeshsingh520@gmail.com');
});