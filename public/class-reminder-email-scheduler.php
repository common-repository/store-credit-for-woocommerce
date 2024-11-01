<?php 

class pi_store_credit_reminder_email_scheduler{

    static public $scheduler_event = 'pi_store_credit_expiry_scheduler';
    static public $scheduler_frequency = 'daily';

    static public $reminder_email_event = 'pi_store_credit_expiry_reminder_email';

    protected static $instance = null;

    public static function get_instance( ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    static function enableReminderEmail(){
        $val = get_option('pi_store_credit_enable_expiry_email', 1);
        return !empty($val) ? true : false;
    }

    protected function __construct(){

        if(!self::enableReminderEmail()) return;

        add_action('init', [$this, 'registerSchedulerCron']);

        add_action(self::$scheduler_event, [$this, 'expiryReminderEmailsScheduler']);

        add_action(self::$reminder_email_event, [$this, 'sendReminderEmail'], 10, 2);
    }

    function registerSchedulerCron(){
        if ( ! wp_next_scheduled( self::$scheduler_event ) ) {
            wp_schedule_event( time(), self::$scheduler_frequency, self::$scheduler_event );
        }
    }

    function expiryReminderEmailsScheduler(){
        $date = $this->sendReminderForCouponExpiringOn();
        if(empty($date)) return;

        $gap_between_email = apply_filters('pi_store_credit_expiry_reminder_email_gap', 100);

        $get_coupons_to_expire = $this->getCouponsToExpireIn( $date );
        //error_log('Coupons to expire: '.print_r($get_coupons_to_expire, true));
        $count = $gap_between_email;
        foreach( $get_coupons_to_expire as $coupon_id ){
            $this->expiryReminderEmailScheduler( $coupon_id, $count );
            $count += $gap_between_email;
        }
    }

    function expiryReminderEmailScheduler( $coupon_id, &$count ){
        $customer_emails = get_post_meta($coupon_id, 'customer_email', true);
        if(empty($customer_emails)) return;

        $gap_between_email = apply_filters('pi_store_credit_expiry_reminder_email_gap', 100);

        foreach($customer_emails as $email_id){
            if ( ! wp_next_scheduled( self::$reminder_email_event, [$coupon_id, $email_id] ) ) {
                wp_schedule_single_event( time() + $count, self::$reminder_email_event, [$coupon_id, $email_id]);
            }
            $count += $gap_between_email;
        }
    }

    function sendReminderEmail($coupon_id, $email_id){
        pi_store_credit_reminder_email::sendStoreCreditReminderEmail($coupon_id, $email_id);
    }

    function sendReminderForCouponExpiringOn(){
        $expiring_after_days = get_option('pi_store_credit_first_expiry_reminder_email', 7);

        if(empty($expiring_after_days)) return;

        $current_date = current_time('Y-m-d');

        $reminder_date = date('Y-m-d', strtotime("+ $expiring_after_days days", strtotime($current_date)));
        //error_log('Reminder date: '.$reminder_date);
        $date_obj = new WC_DateTime( $reminder_date );
        return $date_obj->getTimestamp();
    }

    function getCouponsToExpireIn( $expiring_on_date ){
        $results = get_posts([
            'numberposts' => -1, 
            'post_type' => 'shop_coupon',
            'fields' => 'ids',
            'meta_query' => array(
                'relation'=>'AND',
                array(
                    'key' => 'date_expires',
                    'value' => $expiring_on_date,
                    'compare' => '=',
                ),
                array(
                    'key' => 'discount_type',
                    'compare' => '=',
                    'value'   => 'pi_store_credit',
                )
            )
        ]);
        return $results;
    }
}

pi_store_credit_reminder_email_scheduler::get_instance( );