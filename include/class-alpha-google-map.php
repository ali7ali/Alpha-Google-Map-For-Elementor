<?php
/**
 * Alpha Google Map.
 *
 * @package    AlphaGoogleMap
 *  */

namespace AlphaGoogleMap;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Alpha_Google_Map class.
 *
 * The main class that initiates and runs the addon.
 *
 * @since 1.0.0
 */
final class Alpha_Google_Map {

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.0.0
	 * @var   string Minimum Elementor version required to run the addon.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '3.21.0';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.0.0
	 * @var   string Minimum PHP version required to run the addon.
	 */
	const MINIMUM_PHP_VERSION = '7.4';

	/**
	 * Instance
	 *
	 * @since  1.0.0
	 * @access private
	 * @static
	 * @var    \Elementor_Alpha_Google_Map_Addon\Alpha_Google_Map The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @access public
	 * @static
	 * @return \Elementor_Alpha_Google_Map_Addon\Alpha_Google_Map An instance of the class.
	 */
	public static function instance(): self {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * Perform some compatibility checks to make sure basic requirements are meet.
	 * If all compatibility checks pass, initialize the functionality.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function __construct() {
		if ( $this->is_compatible() ) {
			add_action( 'elementor/init', array( $this, 'init' ) );
		}
	}

	/**
	 * Compatibility Checks
	 *
	 * Checks whether the site meets the addon requirement.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function is_compatible(): bool {

		// Check if Elementor installed and activated.
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_missing_main_plugin' ) );
			return false;
		}

		// Check for required Elementor version.
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_minimum_elementor_version' ) );
			return false;
		}

		// Check for required PHP version.
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_minimum_php_version' ) );
			return false;
		}

		return true;
	}

	/**
	 * Initialize the plugin.
	 */
	public function init(): void {
		add_action( 'elementor/frontend/after_register_styles', array( $this, 'register_frontend_styles' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_frontend_scripts' ) );

		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	/**
	 * Register plugin styles for Elementor widgets.
	 */
	public function register_frontend_styles(): void {
		wp_register_style(
			'alphamap-widget',
			ALPHAMAP_PL_ASSETS . 'css/alpha-map-widget.css',
			array(),
			ALPHAMAP_VERSION
		);
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function admin_notice_missing_main_plugin(): void {
		$message = sprintf(
		/* translators: 1: Plugin name 2: Elementor */
			__( '"%1$s" requires "%2$s" to be installed and activated.', 'alpha-google-map-for-elementor' ),
			'<strong>' . __( 'Alpha Google Map For Elementor', 'alpha-google-map-for-elementor' ) . '</strong>',
			'<strong>' . __( 'Elementor', 'alpha-google-map-for-elementor' ) . '</strong>'
		);

		$elementor     = 'elementor/elementor.php';
		$pathpluginurl = \WP_PLUGIN_DIR . '/' . $elementor;
		$isinstalled   = file_exists( $pathpluginurl );
		// If installed but didn't load.
		if ( $isinstalled && ! did_action( 'elementor/loaded' ) ) {
			$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $elementor . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $elementor );
			$button_text    = esc_html__( 'Activate Elementor', 'alpha-google-map-for-elementor' );
		} else {
			$activation_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
			$button_text    = esc_html__( 'Install Elementor', 'alpha-google-map-for-elementor' );
		}
		$button = '<p><a href="' . esc_url( $activation_url ) . '" class="button-primary">' . esc_html( $button_text ) . '</a></p>';

		$allowed_html = array(
			'strong' => array(),
			'a'      => array(
				'href'  => array(),
				'class' => array(),
			),
			'p'      => array(),
			'div'    => array(
				'class' => array(),
			),
		);

		printf(
			'<div class="notice notice-warning is-dismissible">%s</div>',
			wp_kses( '<p>' . $message . '</p>' . $button, $allowed_html )
		);
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version(): void {
		$message = sprintf(
		/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			__( '"%1$s" requires "%2$s" version %3$s or greater', 'alpha-google-map-for-elementor' ),
			'<strong>' . __( 'Alpha Google Map For Elementor', 'alpha-google-map-for-elementor' ) . '</strong>',
			'<strong>' . __( 'Elementor', 'alpha-google-map-for-elementor' ) . '</strong>',
			self::MINIMUM_ELEMENTOR_VERSION
		);

		$allowed_html = array(
			'strong' => array(),
			'p'      => array(),
			'div'    => array(
				'class' => array(),
			),
		);

		printf(
			'<div class="notice notice-warning is-dismissible">%s</div>',
			wp_kses( '<p>' . $message . '</p>', $allowed_html )
		);
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function admin_notice_minimum_php_version(): void {
		$message = sprintf(
		/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			__( '"%1$s" requires "%2$s" version %3$s or greater.', 'alpha-google-map-for-elementor' ),
			'<strong>' . __( 'Alpha Google Map For Elementor', 'alpha-google-map-for-elementor' ) . '</strong>',
			'<strong>' . __( 'PHP', 'alpha-google-map-for-elementor' ) . '</strong>',
			self::MINIMUM_PHP_VERSION
		);

		$allowed_html = array(
			'strong' => array(),
			'p'      => array(),
			'div'    => array(
				'class' => array(),
			),
		);

		printf(
			'<div class="notice notice-warning is-dismissible">%s</div>',
			wp_kses( '<p>' . $message . '</p>', $allowed_html )
		);
	}

	/**
	 * Register plugin scripts for Elementor widgets.
	 */
	public function register_frontend_scripts(): void {
		$api_key     = trim( (string) get_option( 'elementor_google_maps_api_key' ) );
		$maps_handle = 'alpha-google-maps-api';
		$script_deps = array( 'jquery' );

		if ( ! empty( $api_key ) ) {
			$maps_api_url = $this->build_maps_api_url( $api_key );

			if ( ! wp_script_is( $maps_handle, 'registered' ) ) {
				wp_register_script(
					$maps_handle,
					$maps_api_url,
					array(),
					ALPHAMAP_VERSION,
					array(
						'in_footer' => true,
						'strategy'  => 'defer',
					)
				);
			}
			$script_deps[] = $maps_handle;
		}

		wp_register_script(
			'alphamap',
			ALPHAMAP_PL_ASSETS . 'js/alpha-map.js',
			$script_deps,
			ALPHAMAP_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		$locale   = determine_locale();
		$language = $this->get_language_from_locale( $locale );
		$region   = $this->get_region_from_locale( $locale );

		$config = array(
			'hasApiKey'            => ! empty( $api_key ),
			'missingApiKeyMessage' => __( 'Google Maps API key is missing. Set it under Elementor > Settings > Integrations.', 'alpha-google-map-for-elementor' ),
			'language'             => $language,
			'region'               => $region,
		);

		wp_localize_script( 'alphamap', 'AlphaMapConfig', $config );
	}

	/**
	 * Build the Google Maps API URL using locale-aware params.
	 *
	 * @param string $api_key API key.
	 */
	private function build_maps_api_url( string $api_key ): string {
		$locale   = determine_locale();
		$language = $this->get_language_from_locale( $locale );
		$region   = $this->get_region_from_locale( $locale );

		$params = array(
			'key'     => $api_key,
			'v'       => 'weekly',
			'loading' => 'async',
		);

		if ( $language ) {
			$params['language'] = $language;
		}

		if ( $region ) {
			$params['region'] = $region;
		}

		$params = apply_filters( 'alphamap_google_maps_params', $params );

		return add_query_arg( $params, 'https://maps.googleapis.com/maps/api/js' );
	}

	/**
	 * Extract language code from locale.
	 *
	 * @param string $locale WordPress locale.
	 */
	private function get_language_from_locale( string $locale ): string {
		if ( preg_match( '/^[a-zA-Z]{2}/', $locale, $matches ) ) {
			return strtolower( $matches[0] );
		}
		return '';
	}

	/**
	 * Extract region code from locale.
	 *
	 * @param string $locale WordPress locale.
	 */
	private function get_region_from_locale( string $locale ): string {
		if ( preg_match( '/_[a-zA-Z]{2}$/', $locale, $matches ) ) {
			return strtoupper( str_replace( '_', '', $matches[0] ) );
		}
		return '';
	}

	/**
	 * Plugin activation tasks.
	 */
	public static function activate(): void {
		self::copy_pin_assets();
	}

	/**
	 * Copy default pin assets to the uploads folder for backward compatibility.
	 */
	private static function copy_pin_assets(): void {
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			include_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			return;
		}

		$upload_dir = wp_upload_dir( null, true );

		if ( empty( $upload_dir['basedir'] ) ) {
			return;
		}

		$destination_dir = trailingslashit( $upload_dir['basedir'] ) . 'alpha-map/';

		if ( ! $wp_filesystem->is_dir( $destination_dir ) ) {
			$wp_filesystem->mkdir( $destination_dir );
		}

		$source_dir = trailingslashit( ALPHAMAP_PL_PATH . 'assets/img' );

		$files = array(
			'alpha-pin.png',
			'alpha-pin-hover.png',
		);

		foreach ( $files as $file ) {
			$source      = $source_dir . $file;
			$destination = $destination_dir . $file;

			if ( $wp_filesystem->exists( $destination ) || ! $wp_filesystem->exists( $source ) ) {
				continue;
			}

			$contents = $wp_filesystem->get_contents( $source );

			if ( ! empty( $contents ) ) {
				$wp_filesystem->put_contents( $destination, $contents, FS_CHMOD_FILE );
			}
		}
	}

	/**
	 * Register Widgets
	 *
	 * Load widgets files and register new Elementor widgets.
	 *
	 * Fired by `elementor/widgets/register` action hook.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets( $widgets_manager ): void {
		// Include Widget files.
		include_once ALPHAMAP_PL_INCLUDE . '/class-alpha-google-map-widget.php';
		// Register widget.
		$widgets_manager->register( new \AlphaGoogleMap\Alpha_Google_Map_Widget() );
	}
}
