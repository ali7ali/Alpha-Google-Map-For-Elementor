<?php

/**
 * Plugin Name: Alpha Google Map For Elementor
 * Plugin URI: https://alphatrio.net
 * Description: Premium Google Map features for WordPress.
 * Author:      Ali Ali
 * Author URI:  https://github.com/Ali7Ali
 * Version:     1.2.0
 * Text Domain: alpha-google-map-for-elementor
 * Domain Path: /languages
 * License: GPLv3
 *
 *
 * */

/*
Copyright 2021 Ali Ali (email : ali.abdalhadi.ali@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('ALPHAMAP_VERSION', '1.2.0');
define('ALPHAMAP_ADDONS_PL_ROOT', __FILE__);
define('ALPHAMAP_PL_URL', plugins_url('/', ALPHAMAP_ADDONS_PL_ROOT));
define('ALPHAMAP_PL_PATH', plugin_dir_path(ALPHAMAP_ADDONS_PL_ROOT));
define('ALPHAMAP_PL_ASSETS', trailingslashit(ALPHAMAP_PL_URL . 'assets'));
define('ALPHAMAP_PL_INCLUDE', trailingslashit(ALPHAMAP_PL_PATH . 'include'));
define('ALPHAMAP_PL_LANGUAGES', trailingslashit(ALPHAMAP_PL_PATH . 'languages'));
define('ALPHAMAP_PLUGIN_BASE', plugin_basename(ALPHAMAP_ADDONS_PL_ROOT));

function alpha_google_map_addon()
{

    // Load plugin file
    require_once(ALPHAMAP_PL_INCLUDE . '/class-alpha-google-map.php');

    // Run the plugin
    \Elementor_Alpha_Google_Map_Addon\Alpha_Google_Map::instance();
}
add_action('plugins_loaded', 'alpha_google_map_addon');
