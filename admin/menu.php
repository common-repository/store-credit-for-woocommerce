<?php

class pi_store_credit_admin_menu{

    public $plugin_name;

    public $menu;

    protected static $instance = null;

    public static function get_instance( $plugin_name , $version ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $plugin_name , $version );
		}
		return self::$instance;
	}

    
    protected function __construct($plugin_name , $version){
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_action( 'admin_menu', array($this,'plugin_menu') );
        add_action($this->plugin_name.'_promotion', array($this,'promotion'));
    }

    function plugin_menu(){
        
        $this->menu = add_submenu_page(
            'woocommerce',
            __( 'Store credit', 'store-credit-for-woocommerce'),
            __( 'Store credit', 'store-credit-for-woocommerce'),
            'manage_options',
            'pisol-store-credit-setting',
            array($this, 'menu_option_page')
        );

        add_action("load-".$this->menu, array($this,"bootstrap_style"));
    }

    public function bootstrap_style() {

		wp_enqueue_style( $this->plugin_name."_bootstrap", plugin_dir_url( __FILE__ ) . 'css/bootstrap.css', array(), $this->version, 'all' );

        wp_enqueue_script( $this->plugin_name.'-data-table-core', plugin_dir_url( __FILE__ ) . 'js/datatables.min.js', array( 'jquery' ), $this->version, false );

        wp_enqueue_script( $this->plugin_name.'-table-script', plugin_dir_url( __FILE__ ) . 'js/table-script.js', array( 'jquery', $this->plugin_name.'-data-table-core' ), $this->version, false );

		wp_enqueue_style( $this->plugin_name.'-data-table-core', plugin_dir_url( __FILE__ ) . 'css/datatables.min.css', array(), $this->version, 'all' );
    }

    function menu_option_page(){
        if(function_exists('settings_errors')){
            settings_errors();
        }
        ?>
        <div class="bootstrap-wrapper clear">
        <div class="container mt-2">
            <div class="row">
                    <div class="col-12">
                        <div class='bg-dark'>
                        <div class="row">
                            <div class="col-12 col-sm-2 py-2">
                                    <a href="https://www.piwebsolution.com/" target="_blank"><img class="img-fluid ml-2" src="<?php echo plugin_dir_url( __FILE__ ); ?>img/pi-web-solution.svg"></a>
                            </div>
                            <div class="col-12 col-sm-10 d-flex text-center small pisol-top-menu">
                                
                            </div>
                        </div>
                        </div>
                    </div>
            </div>
            <div class="row">
                <div class="col-12">
                <div class="bg-light border pl-3 pr-3 pb-3 pt-0">
                    <div class="row">
                        <div class="col-12 col-md-2 px-0 border-right">
                            <?php do_action($this->plugin_name.'_tab'); ?>
                        </div>
                        <div class="col">
                        <?php do_action($this->plugin_name.'_tab_content'); ?>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
        </div>
        <?php
    }

   
}

pi_store_credit_admin_menu::get_instance( $this->plugin_name, $this->version );