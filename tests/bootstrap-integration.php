<?php
/**
 * WordPress integration test bootstrap.
 *
 * @package Inskrift
 */

declare(strict_types=1);

$inskrift_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! is_string( $inskrift_tests_dir ) || '' === $inskrift_tests_dir ) {
	throw new RuntimeException( 'WP_TESTS_DIR is not set. Run integration tests in wp-env.' );
}

require dirname( __DIR__ ) . '/vendor/autoload.php';
require $inskrift_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function (): void {
		require dirname( __DIR__ ) . '/inskrift.php';
	}
);

require $inskrift_tests_dir . '/includes/bootstrap.php';
