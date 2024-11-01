<?php

class pi_store_credit_report{

    public $plugin_name;

    private $setting = array();

    private $active_tab;

    private $this_tab = 'report';

    private $setting_key = 'pi_store_credit_main_report';


    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;

        $this->tab_name = __("Report",'store-credit-for-woocommerce');

        $this->tab = sanitize_text_field(filter_input( INPUT_GET, 'tab'));

        $this->active_tab = $this->tab != "" ? $this->tab : 'default';

        $this->settings = array(
              
        );

        if($this->this_tab == $this->active_tab){
            add_action($this->plugin_name.'_tab_content', array($this,'tab_content'));
        }

        add_action($this->plugin_name.'_tab', array($this,'tab'),1);

        add_action('wp_ajax_pi_download_report', array($this,'download_report'));

        add_action('pi_delete_report_file_event', array($this,'pi_delete_report_file'), 10, 1);

        $this->register_settings();
        
    }

    function register_settings(){   

        foreach($this->settings as $setting){
            register_setting( $this->setting_key, $setting['field']);
        }
    
    }

    function tab(){
        ?>
        <a class=" pi-side-menu  <?php echo ($this->active_tab == $this->this_tab ? 'bg-primary' : 'bg-secondary'); ?>" href="<?php echo admin_url( 'admin.php?page='.sanitize_text_field($_GET['page']).'&tab='.$this->this_tab ); ?>">
        <span class="dashicons dashicons-filter"></span> <?php echo $this->tab_name; ?> 
        </a>
        <?php
    }

    function tab_content(){
       $page = isset($_GET['page_no']) ? absint($_GET['page_no']) : 1;
       $per_page = abs(apply_filters('psiol_scfw_report_per_page', 50)); 
       $coupon = '';
       if(isset($_GET['coupon']) && !empty($_GET['coupon'])){
           $coupon = sanitize_text_field($_GET['coupon']);
       }

       $email_id = '';
       if(isset($_GET['email_id']) && !empty($_GET['email_id'])){
          $email_id = sanitize_text_field($_GET['email_id']);
       }   

       $filter_by = isset($_GET['filter_by']) ? sanitize_text_field($_GET['filter_by']) : 'creation-date';

        $start_date = '';
        if(isset($_GET['start_date']) && !empty($_GET['start_date'])){
            $start_date = sanitize_text_field($_GET['start_date']);
        }

        $end_date = '';

        if(isset($_GET['end_date']) && !empty($_GET['end_date'])){
            $end_date = sanitize_text_field($_GET['end_date']);
        }


       $all_coupons_ids = $this->get_all_store_credit($page, $per_page, $coupon, $email_id, $filter_by, $start_date, $end_date);

       echo '<div class="col-12">';
       echo '<form method="get" action="'.admin_url('admin.php').'">';
       echo '<input type="hidden" name="page" value="pisol-store-credit-setting">';
         echo '<input type="hidden" name="tab" value="report">';
        echo '<div class="row mt-3">';
            echo '<div class="col-md-2">';
            echo '<label for="coupon">'.__('Coupon','store-credit-for-woocommerce').'</label>';
            echo '<input type="text" name="coupon" id="coupon" placeholder="Coupon" class="form-control" value="'.(isset($_GET['coupon']) ? sanitize_text_field($_GET['coupon']) : '').'">';
            echo '</div>';
            echo '<div class="col-md-2">';
            echo '<label for="email_id">'.__('Email id','store-credit-for-woocommerce').'</label>';
            echo '<input type="text" name="email_id" id="email_id" placeholder="Email id" class="form-control" value="'.(isset($_GET['email_id']) ? sanitize_text_field($_GET['email_id']) : '').'">';
            echo '</div>';
            echo '<div class="col-md-2">';
                echo '<div class="form-group">';
                    echo '<label for="filter_by">'.__('Filter By','store-credit-for-woocommerce').'</label>';
                    echo '<select name="filter_by" id="filter_by" class="form-control">';
                        echo '<option value="creation-date" '.selected('creation-date', isset($_GET['filter_by']) ? sanitize_text_field($_GET['filter_by']) : 'creation-date', false).'>'.__('Creation Date','store-credit-for-woocommerce').'</option>';
                        echo '<option value="expiry-date" '.selected('expiry-date', isset($_GET['filter_by']) ? sanitize_text_field($_GET['filter_by']) : 'creation-date', false).'>'.__('Expiry Date','store-credit-for-woocommerce').'</option>';
                    echo '</select>';
                echo '</div>';
            echo '</div>';
            echo '<div class="col-md-2">';
                echo '<div class="form-group">';
                    echo '<label for="start_date">'.__('Start Date','store-credit-for-woocommerce').'</label>';
                    echo '<input type="date" name="start_date" id="start_date" class="form-control" value="'.(isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '').'">';
                echo '</div>';
            echo '</div>';
            echo '<div class="col-md-2">';
                echo '<div class="form-group">';
                    echo '<label for="end_date">'.__('End Date','store-credit-for-woocommerce').'</label>';
                    echo '<input type="date" name="end_date" id="end_date" class="form-control" value="'.(isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '').'">';
                echo '</div>';
            echo '</div>';
            echo '<div class="col-md-1">';
                echo '<div class="form-group">';
                    echo '<label for="submit">&nbsp;</label>';
                    echo '<input type="submit" name="submit" id="submit" class="btn btn-primary form-control" value="'.__('Filter','store-credit-for-woocommerce').'">';
                echo '</div>';
            echo '</div>';
            echo '<div class="col-md-1">';
                echo '<div class="form-group">';
                    echo '<label for="submit">&nbsp;</label>';
                    echo '<button  class="btn btn-primary form-control" id="pi-download-report">'.__('Download','store-credit-for-woocommerce').'</button>';
                echo '</div>';
            echo '</div>';
        echo '</div>';
       echo '</form>';
       echo '</div>';
       echo '<table class="table table-striped mt-3" id="pisol-credit-report">';
       echo $this->table_heading();
       echo '<tbody  class="text-center">';
       if(isset($_GET['email_id']) && !empty($_GET['email_id'])){
            $match_email = sanitize_text_field( $_GET['email_id'] );
       }else{
            $match_email = '';
       }

       foreach($all_coupons_ids as $coupon_id){
           $coupon = new WC_Coupon($coupon_id);
           echo $this->table_row($coupon, $match_email);
        }
       echo '</tbody>';
       echo '</table>';
       echo '<nav aria-label="Page navigation example">';
       echo '<ul class="pagination">';
        if ($page > 1) {
            $previous = admin_url('admin.php?page=pisol-store-credit-setting&tab=report&page_no='.($page - 1));

            if(isset($_GET['coupon'])){
                $previous = add_query_arg(['coupon' => $_GET['coupon']], $previous);
            }

            if(isset($_GET['email_id'])){
                $previous = add_query_arg(['email_id' => $_GET['email_id']], $previous);
            }

            if(isset($_GET['filter_by'])){
                $previous = add_query_arg(['filter_by' => $_GET['filter_by']], $previous);
            }

            if(isset($_GET['start_date'])){
                $previous = add_query_arg(['start_date' => $_GET['start_date']], $previous);
            }

            if(isset($_GET['end_date'])){
                $previous = add_query_arg(['end_date' => $_GET['end_date']], $previous);
            }

            echo '<li class="page-item"><a  class="page-link" href="'.$previous.'">Previous</a></li>';
        }

        if (count($all_coupons_ids) != 0) {
            $next = admin_url('admin.php?page=pisol-store-credit-setting&tab=report&page_no='.($page + 1));

            if(isset($_GET['coupon'])){
                $next = add_query_arg(['coupon' => $_GET['coupon']], $next);
            }

            if(isset($_GET['email_id'])){
                $next = add_query_arg(['email_id' => $_GET['email_id']], $next);
            }

            if(isset($_GET['filter_by'])){
                $next = add_query_arg(['filter_by' => $_GET['filter_by']], $next);
            }

            if(isset($_GET['start_date'])){
                $next = add_query_arg(['start_date' => $_GET['start_date']], $next);
            }

            if(isset($_GET['end_date'])){
                $next = add_query_arg(['end_date' => $_GET['end_date']], $next);
            }

            echo '<li class="page-item"><a  class="page-link"  href="'.$next.'">Next</a></li>';
        }
        echo '</ul>';
        echo '</nav>';
    }

    function get_all_store_credit($page = 1, $per_page = 10, $coupon ='', $email_id = '', $filter_by =  'creation-date', $start_date = '', $end_date = ''){
        

        $args = array(
            'post_type'      => 'shop_coupon', 
            'posts_per_page' => -1, 
            'posts_per_page' => $per_page, 
            'paged'          => $page,
            'fields'         => 'ids',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'   => 'discount_type', 
                    'value' => 'pi_store_credit'
                ),
            ),
            'orderby'        => 'ID',
            'order'          => 'DESC'
        );

        if(!empty($coupon)){
            $args['s'] = $coupon;
        }

        if(!empty($email_id)){
            $args['meta_query'][] = [
                'key' => 'customer_email',
                'value' => $email_id,
                'compare' => 'LIKE'
            ];
        }

        if(!empty($start_date) && !empty($end_date)){
            //make sure date are in correct format
            $start_date = date('Y-m-d', strtotime($start_date));
            $end_date = date('Y-m-d', strtotime($end_date));

            if($filter_by == 'creation-date'){
                $args['date_query'] = [
                    'after' => $start_date,
                    'before' => $end_date,
                    'inclusive' => true,
                ];
            }

            if($filter_by == 'expiry-date'){
                $start_date_timestamp = strtotime($start_date);
                $end_date_timestamp = strtotime($end_date);
                $args['meta_query'][] = [
                    'key' => 'date_expires',
                    'value' => array( $start_date_timestamp, $end_date_timestamp ),
                    'compare' => 'BETWEEN',
                    'type' => 'NUMERIC'
                ];
            }
        }

        

        $coupons = get_posts($args);
        return $coupons;
    }

    function couponUsageDetail( $coupon, $strip_tag = false, $match_email = ''){
        
        $emails = Pi_store_credit_discount::getAllEmailId( $coupon->get_id() );

        if(!empty($match_email)){
            $emails = array_filter($emails, function($email) use ($match_email){
                return strpos($email, $match_email) !== false;
            });
        }
        
        $usage_data = [];

        $date_format = get_option( 'date_format', 'Y-m-d' );

        if(!empty($emails)){
            foreach($emails as $email){
                    $total_credit = get_post_meta($coupon->get_id(), 'coupon_amount', true);
                    $credit_available = Pi_store_credit_discount::getUserAvailableDiscountAmount($coupon, $email);
                    $credit_spend = $total_credit - $credit_available;
                    $orders = Pi_store_credit_discount::get_orders( $coupon->get_id(), $email );
                    $creation_date = $coupon->get_date_created();

                    
                    if($creation_date !== null && $creation_date instanceof WC_DateTime){
                        $creation_date = $creation_date->date_i18n($date_format);
                    }

                    $expiry_date = $coupon->get_date_expires();
                    if($expiry_date !== null && $expiry_date instanceof WC_DateTime){
                        $expiry_date = $expiry_date->date_i18n($date_format);
                    }else{
                        $expiry_date = __('Never','store-credit-for-woocommerce');
                    }

                    $usage_data[$email] = apply_filters('psiol_scfw_report_columns_values', [
                        'creation_date' => $creation_date,
                        'coupon_code' => $coupon->get_code(), 
                        'email' => $email,
                        'total_credit' => $total_credit,
                        'credit_available' => $credit_available,
                        'credit_spend' => $credit_spend,
                        'orders' => self::order_links($orders, $strip_tag),
                        'expiry_date' => $expiry_date,
                        'desc' => $coupon->get_description()
                    ], $coupon, $email);
            }
        }

        return $usage_data;
    }

    static function order_links($orders, $strip_tag = false){
        $links = [];
        foreach($orders as $order){
            if($strip_tag){
                $links[] = '#'.$order;
            }else{
                $links[] = '<a href="'.admin_url('post.php?post='.$order.'&action=edit').'" target="_blank">#'.$order.'</a>';
            }
        }
        return implode(', ',$links);
    }

    function table_columns(){
        $columns = [
            'creation_date' => __('Creation Date','store-credit-for-woocommerce'),
            'coupon_code' => __('Coupon Code','store-credit-for-woocommerce'),
            'email' => __('Email','store-credit-for-woocommerce'),
            'total_credit' => __('Total Credit','store-credit-for-woocommerce'),
            'credit_available' => __('Credit Available','store-credit-for-woocommerce'),
            'credit_spend' => __('Credit Spend','store-credit-for-woocommerce'),
            'orders' => __('Credit used in Orders','store-credit-for-woocommerce'),
            'expiry_date' => __('Expiry Date','store-credit-for-woocommerce'),
            'desc' => __('Description','store-credit-for-woocommerce'), 
        ];
        return apply_filters('psiol_scfw_report_columns', $columns);
    }

    function table_heading(){
        $columns = $this->table_columns();
        $heading = '<thead class="text-center">';
        $heading .= '<tr>';
        foreach($columns as $key => $column){
            $heading .= '<th  class="'.esc_attr($key).'">'.$column.'</th>';
        }
        $heading .= '</tr>';
        $heading .= '</thead>';
        return $heading;
    }

    function table_row($coupon, $match_email = ''){
        $columns = $this->table_columns();
        $body = '';
        $usage_data = $this->couponUsageDetail($coupon, false, $match_email);
        foreach($usage_data as $data){
            $body .= '<tr>';
            foreach($columns as $key => $column){
                if($key == 'coupon_code'){
                    $body .= '<td class="'.esc_attr($key).'"><a href="'.admin_url('post.php?post='.$coupon->get_id().'&action=edit').'" target="_blank">'.$data[$key].'</a></td>';
                    continue;
                }else{
                    $body .= '<td class="'.esc_attr($key).'">'.$data[$key].'</td>';
                }
            }
            $body .= '</tr>';
        }
        return $body;
    }

    function download_report(){
        if(!current_user_can( 'manage_options' )){
            wp_send_json_error(array('message' => __('Invalid Request','store-credit-for-woocommerce')));
        }

       $coupon = '';
       if(isset($_POST['coupon']) && !empty($_POST['coupon'])){
           $coupon = sanitize_text_field($_POST['coupon']);
       }

       $email_id = '';
       if(isset($_POST['email_id']) && !empty($_POST['email_id'])){
          $email_id = sanitize_text_field($_POST['email_id']);
       }   

       $filter_by = isset($_POST['filter_by']) ? sanitize_text_field($_POST['filter_by']) : 'creation-date';

        $start_date = '';
        if(isset($_POST['start_date']) && !empty($_POST['start_date'])){
            $start_date = sanitize_text_field($_POST['start_date']);
        }

        $end_date = '';

        if(isset($_POST['end_date']) && !empty($_POST['end_date'])){
            $end_date = sanitize_text_field($_POST['end_date']);
        }

        $per_page = apply_filters('pi_store_credit_download_record_per_page', 200);

        if(isset($_POST['pi_coupon_report_page']) && !empty($_POST['pi_coupon_report_page'])){
            $page = absint($_POST['pi_coupon_report_page']);
        }else{
            $page = 1;
        }

        $count = $page * $per_page;     
         
        $all_coupons_ids = $this->get_all_store_credit($page, $per_page, $coupon, $email_id, $filter_by, $start_date, $end_date);

        $finished = false;
        if(empty($all_coupons_ids)){
            $finished = true;
        }

        $usage_data = [];
        foreach($all_coupons_ids as $coupon_id){
            $coupon = new WC_Coupon($coupon_id);
            $all_internal_coupons = $this->couponUsageDetail($coupon, true, $email_id);
            if(!empty($all_internal_coupons) && is_array($all_internal_coupons)){
                $usage_data = array_merge($usage_data, array_values($all_internal_coupons));
            }
        }

        $file_name = isset($_POST['file_name']) && !empty($_POST['file_name']) ? sanitize_text_field($_POST['file_name']) : 'coupon-export-' . time() . '.csv';

        $file_path = WP_CONTENT_DIR . '/uploads/'.$file_name;

        if (!wp_next_scheduled('pi_delete_report_file_event', array($file_path))) {
            wp_schedule_single_event(time() + 900, 'pi_delete_report_file_event', array($file_path));
        }

        $file = fopen($file_path, $page === 1 ? 'w' : 'a');  // 'w' mode for first page, 'a' for appending later
        if ($page === 1) {
            $columns = $this->table_columns();
            fputcsv($file, $columns); // Add headers only on the first page
        }

        foreach($usage_data as $data){
            fputcsv($file, $data);
        }

        fclose($file);
        
        wp_send_json_success([
            'next_page'    => $finished ? 0 : $page + 1, 
            'complete' => $finished, 
            'file_url' => home_url('/wp-content/uploads/'.$file_name),
            'file_name' => $file_name,
            'extracted' => $count
        ]);
    
    }

    function pi_wrap_with_quotes($value) {
        return '"' . str_replace('"', '""', $value) . '"';
    }

    function pi_delete_report_file($file_path) {
        if(file_exists($file_path)) {
            unlink($file_path); // Delete the file
        }
    }

}

new pi_store_credit_report( $this->plugin_name );
