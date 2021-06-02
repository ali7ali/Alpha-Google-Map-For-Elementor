<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

final class Alpha_Google_Map_For_Elementor {
    const MINIMUM_ELEMENTOR_VERSION = '2.5.0';
    const MINIMUM_PHP_VERSION = '7.0';
    private static $_instance = null;
    
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
        add_action('wp_enqueue_scripts', [ $this,'ALPHAMAP_theme_assets'] );
    }

    public function i18n() {
        load_plugin_textdomain( 'alpha-google-map-for-elementor' );

    }

    public function on_plugins_loaded() {
        if ( $this->is_compatible() ) {
			add_action( 'elementor/init', [ $this, 'init' ] );
		}
    }

    public function is_compatible() {

		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return false;
		}

        // Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return false;
		}

        // Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return false;
		}
        
        return true;

	}

    public function init() {

        $this->i18n();
  
        // Add Plugin actions
        add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );

        $this->add_assets();
    }

    public function add_assets() {
        $upload_dir = wp_upload_dir(NULL,TRUE);
        $dir = $upload_dir[ 'basedir' ];
        if ( ! empty($dir ) ) {
                wp_mkdir_p( $dir . '/alpha-map' );
                copy(ALPHAMAP_PL_ASSETS . 'img/alpha-pin-black.png', $dir . '/alpha-map/alpha-pin-black.png');
                copy(ALPHAMAP_PL_ASSETS . 'img/alpha-pin-red.png', $dir . '/alpha-map/alpha-pin-red.png');
            }
//var_dump($upload_dir['baseurl'].'/alpha-map/alpha-pin-black.png');
    }

    /*
    * Check Plugins is Installed or not
    */
    public function is_plugins_active( $pl_file_path = NULL ){
        $installed_plugins_list = get_plugins();
        return isset( $installed_plugins_list[$pl_file_path] );
    }

    /**
     * Admin notice.
     * For missing elementor.
     */
    public function admin_notice_missing_main_plugin() {
        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );
        $elementor = 'elementor/elementor.php';
        if( $this->is_plugins_active( $elementor ) ) {
            if( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }
            $activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $elementor . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $elementor );
            /* translators: 1: Just text decoration 2: Just text decoration */
            $message = sprintf( __( '%1$sAlpha Google Map For Elementor%2$s requires %1$s"Elementor"%2$s plugin to be active. Please activate Elementor to continue.', 'alpha-google-map-for-elementor' ), '<strong>', '</strong>' );
            $button_text = esc_html__( 'Activate Elementor', 'alpha-google-map-for-elementor' );
        } else {
            if( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }
            $activation_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
            /* translators: 1: Just text decoration 2: Just text decoration */
            $message = sprintf( __( '%1$sAlpha Google Map For Elementor%2$s requires %1$s"Elementor"%2$s plugin to be installed and activated. Please install Elementor to continue.', 'alpha-google-map-for-elementor' ), '<strong>', '</strong>' );
            $button_text = esc_html__( 'Install Elementor', 'alpha-google-map-for-elementor' );
        }
        $button = '<p><a href="' . $activation_url . '" class="button-primary">' . $button_text . '</a></p>';
        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p>%2$s</div>', $message, $button );
    }


    public function admin_notice_minimum_elementor_version() {

        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'alpha-google-map-for-elementor' ),
            '<strong>' . esc_html__( 'Alpha Google Map For Elementor', 'alpha-google-map-for-elementor' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'alpha-google-map-for-elementor' ) . '</strong>',
             self::MINIMUM_ELEMENTOR_VERSION
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

    }

    public function admin_notice_minimum_php_version() {

        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

        $message = sprintf(
            /* translators: 1: Plugin name 2: Required PHP version */
            esc_html__( '"%1$s" requires PHP version %3$s or greater.', 'alpha-google-map-for-elementor' ),
            '<strong>' . esc_html__( 'Alpha Google Map For Elementor', 'alpha-google-map-for-elementor' ) . '</strong>',
             self::MINIMUM_PHP_VERSION
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

    }


    public function ALPHAMAP_theme_assets(){
        self::plugin_css();
        self::plugin_js();
    }

    public function plugin_css(){
        wp_enqueue_style('alphamap-widget', ALPHAMAP_PL_ASSETS . 'css/alpha-map-widget.css', '', ALPHAMAP_VERSION );
    }
    public function plugin_js(){
        // Script register
        wp_enqueue_script( 'alpha-maps-finder', ALPHAMAP_PL_ASSETS . 'js/pa-maps-finder.js', array('jquery'), ALPHAMAP_VERSION, TRUE );
        wp_enqueue_script( 'alphamap', ALPHAMAP_PL_ASSETS . 'js/alpha-map.js', array( 'jquery', 'alpha-api-js' ), ALPHAMAP_VERSION, TRUE );

        // get an option
        $key = get_option('alpha_google_api_key');

        $api = sprintf( 'https://maps.googleapis.com/maps/api/js?key=%1$s&language=en', $key,  );
        wp_enqueue_script(
            'alpha-api-js',
            $api,
            array(),
            '1.0.0',
            false
        );
    }

    public function init_widgets() {
        // Include Widget files
        include( ALPHAMAP_PL_INCLUDE.'/Alpha_Google_Map.php' );
        // Register widget
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\Alpha_Google_Map() );

    }
}
Alpha_Google_Map_For_Elementor::instance();
