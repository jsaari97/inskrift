<?php
/**
 * Main plugin setup.
 *
 * @package Inskrift
 */

declare(strict_types=1);

namespace Inskrift;

use Inskrift\Blocks\GuestbookRenderer;
use Inskrift\Database\Schema;

/**
 * Connects the plugin to WordPress.
 */
final class Plugin {
	/**
	 * Adds the plugin hooks.
	 */
	public static function boot(): void {
		add_action( 'plugins_loaded', array( Schema::class, 'maybe_update' ) );

		add_action( 'init', array( self::class, 'register_blocks' ) );
	}

	/**
	 * Registers all plugin blocks.
	 */
	public static function register_blocks(): void {
		$block_path = INSKRIFT_PLUGIN_DIR . 'build/guestbook';

		if ( ! is_readable( $block_path . '/block.json' ) ) {
			return;
		}

		register_block_type(
			$block_path,
			array(
				'render_callback' => array( GuestbookRenderer::class, 'render' ),
			)
		);
	}
}
