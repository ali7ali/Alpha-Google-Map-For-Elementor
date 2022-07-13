<?php
/**
 * Main plugin class
 *
 * @package alpha-google-map-for-elementor
 *  */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Alpha_Google_Map
 */
final class Alpha_Google_Map {
	const MINIMUM_ELEMENTOR_VERSION = '2.5.0';
	const MINIMUM_PHP_VERSION       = '5.6';
	/**
	 * Self instance.
	 *
	 * @var null Self instance
	 */
	private static $_instance = null;

	/**
	 * Return self instance.
	 *
	 * @return Alpha_Google_Map|null
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alpha_Google_Map constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
		add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'plugin_css' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'plugin_js' ) );
	}

	/**
	 * Load the plugin text domain.
	 */
	public function i18n() {
		load_plugin_textdomain( 'alpha-google-map-for-elementor' );
	}

	/**
	 * On plugins load check for compatibility.
	 */
	public function on_plugins_loaded() {
		if ( $this->is_compatible() ) {
			add_action( 'elementor/init', array( $this, 'init' ) );
		}
	}

	/**
	 * Check if is compatible.
	 *
	 * @return bool
	 */
	public function is_compatible() {

		// Check if Elementor installed and activated.
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_missing_main_plugin' ) );
			return false;
		}

		// Check for required PHP version.
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_minimum_php_version' ) );
			return false;
		}

		$elementor     = 'elementor/elementor.php';
		$pathpluginurl = WP_PLUGIN_DIR . '/' . $elementor;
		$isinstalled   = file_exists( $pathpluginurl );

		// Check for required Elementor version.
		if ( ! defined( 'ELEMENTOR_VERSION' ) || ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_minimum_elementor_version' ) );
			return false;
		} elseif ( $isinstalled && $this->is_elementor_active() ) {
			return true;
		} else {
			add_action( 'admin_notices', array( $this, 'admin_notice_missing_main_plugin' ) );
			return false;
		}
		return true;
	}

	/**
	 * Initialize the plugin.
	 */
	public function init() {
		$this->i18n();

		// Add Plugin actions.
		add_action( 'elementor/widgets/register', array( $this, 'init_widgets' ) );

		$this->add_assets();
	}

	/**
	 * Loading plugin media assets.
	 */
	public function add_assets() {
		$upload_dir = wp_upload_dir( null, true );
		$dir        = $upload_dir['basedir'];
		if ( ! empty( $dir ) ) {
				wp_mkdir_p( $dir . '/alpha-map' );
				copy( ALPHAMAP_PL_ASSETS . 'img/alpha-pin.png', $dir . '/alpha-map/alpha-pin.png' );
				copy( ALPHAMAP_PL_ASSETS . 'img/alpha-pin-hover.png', $dir . '/alpha-map/alpha-pin-hover.png' );
		}
	}

	/**
	 * Check if Elementor is active or not.
	 */
	public function is_elementor_active() {
		if ( function_exists( 'elementor_load_plugin_textdomain' ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Admin notice.
	 * For missing elementor.
	 */
	public function admin_notice_missing_main_plugin() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
		$elementor     = 'elementor/elementor.php';
		$pathpluginurl = WP_PLUGIN_DIR . '/' . $elementor;
		$isinstalled   = file_exists( $pathpluginurl );
		if ( $isinstalled && $this->is_elementor_active() ) {
				return;
		} elseif ( $isinstalled && ! $this->is_elementor_active() ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $elementor . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $elementor );
			/* translators: 1: Just text decoration 2: Just text decoration */
			$message     = sprintf( __( '%1$sAlpha Google Map For Elementor%2$s requires %1$s"Elementor"%2$s plugin to be active. Please activate Elementor to continue.', 'alpha-google-map-for-elementor' ), '<strong>', '</strong>' );
			$button_text = esc_html__( 'Activate Elementor', 'alpha-google-map-for-elementor' );
		} else {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			$activation_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
			/* translators: 1: Just text decoration 2: Just text decoration */
			$message     = sprintf( __( '%1$sAlpha Google Map For Elementor%2$s requires %1$s"Elementor"%2$s plugin to be installed and activated. Please install Elementor to continue.', 'alpha-google-map-for-elementor' ), '<strong>', '</strong>' );
			$button_text = esc_html__( 'Install Elementor', 'alpha-google-map-for-elementor' );
		}
		$button = '<p><a href="' . $activation_url . '" class="button-primary">' . $button_text . '</a></p>';
		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p>%2$s</div>', $message, $button );
	}

	/**
	 * Admin notice.
	 * For minimum Elementor version required.
	 */
	public function admin_notice_minimum_elementor_version() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'alpha-google-map-for-elementor' ),
			'<strong>' . esc_html__( 'Alpha Google Map For Elementor', 'alpha-google-map-for-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'alpha-google-map-for-elementor' ) . '</strong>',
			self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * Admin notice.
	 * For minimum PHP version required.
	 */
	public function admin_notice_minimum_php_version() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: Required PHP version */
			esc_html__( '"%1$s" requires PHP version %3$s or greater.', 'alpha-google-map-for-elementor' ),
			'<strong>' . esc_html__( 'Alpha Google Map For Elementor', 'alpha-google-map-for-elementor' ) . '</strong>',
			self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * Loading plugin css.
	 */
	public function plugin_css() {
		wp_enqueue_style( 'alphamap-widget', ALPHAMAP_PL_ASSETS . 'css/alpha-map-widget.css', '', ALPHAMAP_VERSION );
	}

	/**
	 * Loading plugin JavaScript.
	 */
	public function plugin_js() {
		// Script register.
		wp_enqueue_script( 'alpha-maps-finder', ALPHAMAP_PL_ASSETS . 'js/pa-maps-finder.js', array( 'jquery' ), ALPHAMAP_VERSION, true );
		wp_enqueue_script( 'alphamap', ALPHAMAP_PL_ASSETS . 'js/alpha-map.js', array( 'jquery', 'alpha-api-js' ), ALPHAMAP_VERSION, true );

		// get an option.
		$key = get_option( 'alpha_google_api_key' );

		$api = sprintf( 'https://maps.googleapis.com/maps/api/js?key=%1$s&language=en', $key );
		wp_enqueue_script(
			'alpha-api-js',
			$api,
			array(),
			'1.0.0',
			false
		);
	}

	/**
	 * Register the plugin widget.
	 *
	 * @param object $widgets_manager Elementor widgets object.
	 *
	 * @throws Exception File.
	 */
	public function init_widgets( $widgets_manager ) {
		// Include Widget files.
		require_once ALPHAMAP_PL_INCLUDE . '/class-alpha-google-map-widget.php';
		// Register widget.
		$widgets_manager->register( new \Elementor\Alpha_Google_Map_Widget() );
	}
}
Alpha_Google_Map::instance();
