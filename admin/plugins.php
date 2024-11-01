<?php

class pi_store_credit_other_plugins{

    public $plugin_name;

    private $setting = array();

    private $active_tab;

    private $this_tab = 'other_plugins';

    private $tab_name = "Related Plugins";

    private $setting_key = 'pi_store_credit_other_plugins';


    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;

        
        
        $this->tab = sanitize_text_field(filter_input( INPUT_GET, 'tab'));
        $this->active_tab = $this->tab != "" ? $this->tab : 'default';

        $this->settings = array(
            
            
        );

        if($this->this_tab == $this->active_tab){
            add_action($this->plugin_name.'_tab_content', array($this,'tab_content'));
        }

        add_action($this->plugin_name.'_tab', array($this,'tab'),PHP_INT_MAX);

        $this->register_settings();
        
    }

    function register_settings(){   

        foreach($this->settings as $setting){
            register_setting( $this->setting_key, $setting['field']);
        }
    
    }

    function tab(){
        $this->tab_name = __("Related Plugins",'store-credit-for-woocommerce');
        ?>
        <a class=" pi-side-menu  <?php echo ($this->active_tab == $this->this_tab ? 'bg-primary' : 'bg-secondary'); ?>" href="<?php echo admin_url( 'admin.php?page='.sanitize_text_field($_GET['page']).'&tab='.$this->this_tab ); ?>">
        <span class="dashicons dashicons-admin-plugins"></span> <?php echo  $this->tab_name; ?> 
        </a>
        <a class=" pi-side-menu bg-secondary" target="_blank" href="https://wordpress.org/support/plugin/store-credit-for-woocommerce/reviews/#bbp_topic_content">
        <span class="dashicons dashicons-editor-help"></span> <?php _e("GIVE SUGGESTIONS",'store-credit-for-woocommerce'); ?>
        </a>
        <?php
    }

    function tab_content(){

        require_once ABSPATH . 'wp-admin/includes/class-wp-plugin-install-list-table.php';
        
        add_filter('install_plugins_nonmenu_tabs', function ($tabs) {
            $tabs[] = 'rajeshsingh520';
            return $tabs;
        });
        add_filter('install_plugins_table_api_args_rajeshsingh520', function ($args) {
            global $paged;
            return [
                'page' => $paged,
                'per_page' => 20,
                'locale' => get_user_locale(),
                'author' => 'rajeshsingh520',
            ];
        });

        $_POST['tab'] = 'rajeshsingh520';
        $table = new WP_Plugin_Install_List_Table();
        $table->prepare_items();

        wp_enqueue_script('plugin-install');
        add_thickbox();
        wp_enqueue_script('updates');
        echo '<div id="plugin-filter">';
        echo $table->display();
        echo '</div>';
        
    }

}


new pi_store_credit_other_plugins( $this->plugin_name );