<?php

namespace Elementor_Alpha_Google_Map_Addon;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Alpha_Google_Map class.
 *
 * The main class that initiates and runs the addon.
 *
 * @since 1.0.0
 */
final class Alpha_Google_Map
{
    /**
     * Minimum Elementor Version
     *
     * @since 1.0.0
     * @var string Minimum Elementor version required to run the addon.
     */
    const MINIMUM_ELEMENTOR_VERSION = '3.21.0';

    /**
     * Minimum PHP Version
     *
     * @since 1.0.0
     * @var string Minimum PHP version required to run the addon.
     */
    const MINIMUM_PHP_VERSION = '7.4';

    /**
     * Instance
     *
     * @since 1.0.0
     * @access private
     * @static
     * @var \Elementor_Alpha_Google_Map_Addon\Alpha_Google_Map The single instance of the class.
     */
    private static $_instance = null;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @since 1.0.0
     * @access public
     * @static
     * @return \Elementor_Alpha_Google_Map_Addon\Alpha_Google_Map An instance of the class.
     */
    public static function instance()
    {

        if (is_null(self::$_instance)) {
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
     * @since 1.0.0
     * @access public
     */
    public function __construct()
    {
        if ($this->is_compatible()) {
            add_action('elementor/init', [$this, 'init']);
        }
    }

    /**
     * Load the plugin text domain.
     */
    public function i18n()
    {
        load_plugin_textdomain('alpha-google-map-for-elementor', false, ALPHAMAP_PL_LANGUAGES);
    }

    /**
     * Compatibility Checks
     *
     * Checks whether the site meets the addon requirement.
     *
     * @since 1.0.0
     * @access public
     */
    public function is_compatible()
    {

        // Check if Elementor installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
            return false;
        }

        // Check for required Elementor version
        if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return false;
        }

        // Check for required PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return false;
        }

        return true;
    }

    /**
     * Initialize the plugin.
     */
    public function init()
    {
        $this->i18n();

        $this->add_assets();
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'frontend_styles']);
        add_action('elementor/frontend/after_register_scripts', [$this, 'frontend_scripts']);

        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    /**
     * Loading plugin media assets.
     */
    public function add_assets()
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
        $upload_dir = wp_upload_dir(null, true);
        $dir        = $upload_dir['basedir'];
        if (!empty($dir)) {
            wp_mkdir_p($dir . '/alpha-map');
            $wp_file_sys = new \WP_Filesystem_Direct('direct');
            if (!$wp_file_sys->exists($dir . '/alpha-map/alpha-pin.png')) {
                $wp_file_sys->put_contents($dir . '/alpha-map/alpha-pin.png', $wp_file_sys->get_contents(ALPHAMAP_PL_ASSETS . 'img/alpha-pin.png'));
            }
            if (!$wp_file_sys->exists($dir . '/alpha-map/alpha-pin-hover.png')) {
                $wp_file_sys->put_contents($dir . '/alpha-map/alpha-pin-hover.png', $wp_file_sys->get_contents(ALPHAMAP_PL_ASSETS . 'img/alpha-pin-hover.png'));
            }
        }
    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have Elementor installed or activated.
     *
     * @since 1.0.0
     * @access public
     */
    public function admin_notice_missing_main_plugin()
    {
        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'alpha-google-map-for-elementor'),
            '<strong>' . esc_html__('Alpha Google Map For Elementor', 'alpha-google-map-for-elementor') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'alpha-google-map-for-elementor') . '</strong>'
        );

        $elementor     = 'elementor/elementor.php';
        $pathpluginurl = \WP_PLUGIN_DIR . '/' . $elementor;
        $isinstalled   = file_exists($pathpluginurl);
        // If installed but didn't load
        if ($isinstalled && !did_action('elementor/loaded')) {
            $activation_url = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $elementor . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $elementor);
            $button_text = esc_html__('Activate Elementor', 'alpha-google-map-for-elementor');
        } else {
            $activation_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=elementor'), 'install-plugin_elementor');
            $button_text = esc_html__('Install Elementor', 'alpha-google-map-for-elementor');
        }
        $button = '<p><a href="' . $activation_url . '" class="button-primary">' . $button_text . '</a></p>';
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p>%2$s</div>', $message, $button);
    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required Elementor version.
     *
     * @since 1.0.0
     * @access public
     */
    public function admin_notice_minimum_elementor_version()
    {

        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'alpha-google-map-for-elementor'),
            '<strong>' . esc_html__('Alpha Google Map For Elementor', 'alpha-google-map-for-elementor') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'alpha-google-map-for-elementor') . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required PHP version.
     *
     * @since 1.0.0
     * @access public
     */
    public function admin_notice_minimum_php_version()
    {

        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'alpha-google-map-for-elementor'),
            '<strong>' . esc_html__('Alpha Google Map For Elementor', 'alpha-google-map-for-elementor') . '</strong>',
            '<strong>' . esc_html__('PHP', 'alpha-google-map-for-elementor') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Loading plugin css.
     */
    public function frontend_styles()
    {
        wp_enqueue_style('alphamap-widget', ALPHAMAP_PL_ASSETS . 'css/alpha-map-widget.css', '', ALPHAMAP_VERSION);
    }

    /**
     * Loading plugin JavaScript.
     */
    public function frontend_scripts()
    {
        // Script register.
        wp_enqueue_script('alphamap', ALPHAMAP_PL_ASSETS . 'js/alpha-map.js', array('jquery', 'alpha-api-js'), ALPHAMAP_VERSION, array(
            'in_footer' => true,
            'strategy'  => 'defer',
        ));

        // get an option.
        $api_key = get_option('elementor_google_maps_api_key');

        $api = sprintf('https://maps.googleapis.com/maps/api/js?key=%1$s&language=en&callback=blur', $api_key);
        wp_enqueue_script(
            'alpha-api-js',
            $api,
            array(),
            '1.0.0',
            array(
                'in_footer' => true,
                'strategy'  => 'defer',
            )
        );
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
    public function register_widgets($widgets_manager)
    {
        // Include Widget files.
        require_once ALPHAMAP_PL_INCLUDE . '/class-alpha-google-map-widget.php';
        // Register widget.
        $widgets_manager->register(new \Elementor_Alpha_Google_Map_Addon\Alpha_Google_Map_Widget());
    }
}
