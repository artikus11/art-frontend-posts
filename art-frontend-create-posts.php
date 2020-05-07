<?php

/**
 * Plugin Name: Art Frontend Create Posts
 * Plugin URI: wpruse.ru
 * Text Domain:
 * Domain Path: /languages
 * Description: Плагин добавления записей с фронта
 * Version: 1.0.0
 * Author: Artem Abramovich
 * Author URI: https://wpruse.ru/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AFCP_DIR', plugin_dir_path( __FILE__ ) );
define( 'AFCP_URI', plugin_dir_url( __FILE__ ) );

require AFCP_DIR . 'includes/class-afcp-core.php';

function afcp() {

	return AFCP_Core::instance();
}

afcp();