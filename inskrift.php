<?php
/**
 * Plugin Name:       Inskrift
 * Description:       A small, modern, and privacy-friendly guestbook.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      8.4
 * Author:            Jim Saari
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       inskrift
 * Domain Path:       /languages
 *
 * @package Inskrift
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'INSKRIFT_VERSION', '0.1.0' );
define( 'INSKRIFT_PLUGIN_FILE', __FILE__ );
define( 'INSKRIFT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

$inskrift_autoloader = INSKRIFT_PLUGIN_DIR . 'vendor/autoload.php';

if ( is_readable( $inskrift_autoloader ) ) {
	require $inskrift_autoloader;
	Inskrift\Plugin::boot();
}
