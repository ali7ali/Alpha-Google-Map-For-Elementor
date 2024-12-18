<?php

namespace AlphaGoogleMap;

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
    public static function instance(): self
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
     * @since  1.0.0
     * @access private
     */
    private function __construct()
    {
        if ($this->is_compatible()) {
            add_action('elementor/init', [$this, 'init']);
        }
    }

    /**
     * Load the plugin text domain.
     */
    public function i18n(): void
    {
        load_plugin_textdomain('alpha-google-map-for-elementor', false, ALPHAMAP_PL_LANGUAGES);
    }

    /**
     * Compatibility Checks
     *
     * Checks whether the site meets the addon requirement.
     *
     * @since  1.0.0
     * @access public
     */
    public function is_compatible(): bool
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
    public function init(): void
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
    function add_assets(): void
    {
        if (! function_exists('WP_Filesystem')) {
            include_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();
        global $wp_filesystem;

        $upload_dir = wp_upload_dir(null, true);
        $dir        = trailingslashit($upload_dir['basedir']) . 'alpha-map/';

        if (! $wp_filesystem->is_dir($dir)) {
            $wp_filesystem->mkdir($dir);
        }


        $plugin_assets_dir = ALPHAMAP_PL_ASSETS . 'img/';

        $files = [
            'alpha-pin.png',
            'alpha-pin-hover.png',
        ];

        foreach ($files as $file) {
            $destination = $dir . $file;
            if (! $wp_filesystem->exists($destination)) {
                $source = $plugin_assets_dir . $file;

                if ($wp_filesystem->exists($source)) {
                    $contents = $wp_filesystem->get_contents($source);
                    if (! empty($contents)) {
                        $wp_filesystem->put_contents($destination, $contents, FS_CHMOD_FILE);
                    }
                }
            }
        }
    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have Elementor installed or activated.
     *
     * @since  1.0.0
     * @access public
     */
    public function admin_notice_missing_main_plugin(): void
    {
        $message = sprintf(
        /* translators: 1: Plugin name 2: Elementor */
            __('"%1$s" requires "%2$s" to be installed and activated.', 'alpha-google-map-for-elementor'),
            '<strong>' . __('Alpha Google Map For Elementor', 'alpha-google-map-for-elementor') . '</strong>',
            '<strong>' . __('Elementor', 'alpha-google-map-for-elementor') . '</strong>'
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
        $button = '<p><a href="' . esc_url($activation_url) . '" class="button-primary">' . esc_html($button_text) . '</a></p>';

        $allowed_html = [
            'strong' => [],
            'a' => [
                'href' => [],
                'class' => [],
            ],
            'p' => [],
            'div' => [
                'class' => [],
            ],
        ];

        printf(
            '<div class="notice notice-warning is-dismissible">%s</div>',
            wp_kses('<p>' . $message . '</p>' . $button, $allowed_html)
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
    public function admin_notice_minimum_elementor_version(): void
    {
        $message = sprintf(
        /* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
            __('"%1$s" requires "%2$s" version %3$s or greater.', 'alpha-google-map-for-elementor'),
            '<strong>' . __('Alpha Google Map For Elementor', 'alpha-google-map-for-elementor') . '</strong>',
            '<strong>' . __('Elementor', 'alpha-google-map-for-elementor') . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );

        $allowed_html = [
            'strong' => [],
            'p' => [],
            'div' => [
                'class' => [],
            ],
        ];

        printf(
            '<div class="notice notice-warning is-dismissible">%s</div>',
            wp_kses('<p>' . $message . '</p>', $allowed_html)
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
    public function admin_notice_minimum_php_version(): void
    {
        $message = sprintf(
        /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            __('"%1$s" requires "%2$s" version %3$s or greater.', 'alpha-google-map-for-elementor'),
            '<strong>' . __('Alpha Google Map For Elementor', 'alpha-google-map-for-elementor') . '</strong>',
            '<strong>' . __('PHP', 'alpha-google-map-for-elementor') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        $allowed_html = [
            'strong' => [],
            'p' => [],
            'div' => [
                'class' => [],
            ],
        ];

        printf(
            '<div class="notice notice-warning is-dismissible">%s</div>',
            wp_kses('<p>' . $message . '</p>', $allowed_html)
        );
    }

    /**
     * Loading plugin css.
     */
    public function frontend_styles(): void
    {
        wp_enqueue_style('alphamap-widget', ALPHAMAP_PL_ASSETS . 'css/alpha-map-widget.css', '', ALPHAMAP_VERSION);
    }

    /**
     * Loading plugin JavaScript.
     */
    public function frontend_scripts(): void
    {
        // Script register.
        wp_enqueue_script(
            'alphamap', ALPHAMAP_PL_ASSETS . 'js/alpha-map.js',  ['jquery', 'alpha-api-js'], ALPHAMAP_VERSION, array(
            'in_footer' => true,
            'strategy'  => 'defer',
            )
        );

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
    public function register_widgets($widgets_manager): void
    {
        // Include Widget files.
        include_once ALPHAMAP_PL_INCLUDE . '/class-alpha-google-map-widget.php';
        // Register widget.
        $widgets_manager->register(new \AlphaGoogleMap\Alpha_Google_Map_Widget());
    }
}
