<?php
/**
 * Guestbook block renderer.
 *
 * @package Inskrift
 */

declare(strict_types=1);

namespace Inskrift\Blocks;

/**
 * Renders the guestbook block on the server.
 */
final class GuestbookRenderer {
	/**
	 * Renders the block.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 */
	public static function render( array $attributes ): string {
		$title = array_key_exists( 'title', $attributes ) && is_string( $attributes['title'] )
			? $attributes['title']
			: __( 'Guestbook', 'inskrift' );

		$heading_level = self::normalize_heading_level( $attributes['headingLevel'] ?? 2 );
		$title_markup  = '';

		if ( '' !== $title ) {
			$title_markup = sprintf(
				'<h%1$d class="inskrift-guestbook__title">%2$s</h%1$d>',
				$heading_level,
				esc_html( $title )
			);
		}

		$placeholder = sprintf(
			'<p class="inskrift-guestbook__placeholder">%s</p>',
			esc_html__( 'Guestbook entries and the submission form will appear here.', 'inskrift' )
		);

		return sprintf(
			'<div %1$s>%2$s%3$s</div>',
			get_block_wrapper_attributes(),
			$title_markup,
			$placeholder
		);
	}

	/**
	 * Returns a safe heading level.
	 *
	 * @param mixed $heading_level Requested heading level.
	 */
	public static function normalize_heading_level( mixed $heading_level ): int {
		if ( ! is_int( $heading_level ) || $heading_level < 2 || $heading_level > 6 ) {
			return 2;
		}

		return $heading_level;
	}
}
