<?php
/**
 * Guestbook renderer unit tests.
 *
 * @package Inskrift
 */

declare(strict_types=1);

namespace Inskrift\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Inskrift\Blocks\GuestbookRenderer;

/**
 * Tests heading level normalization without WordPress.
 */
final class GuestbookRendererTest extends TestCase {
	/**
	 * Provides accepted heading levels.
	 *
	 * @return array<string, array{int}>
	 */
	public static function valid_heading_levels(): array {
		return array(
			'h2' => array( 2 ),
			'h3' => array( 3 ),
			'h4' => array( 4 ),
			'h5' => array( 5 ),
			'h6' => array( 6 ),
		);
	}

	/**
	 * Tests all accepted heading levels.
	 *
	 * @param int $heading_level Accepted heading level.
	 *
	 * @dataProvider valid_heading_levels
	 */
	public function test_accepts_valid_heading_levels( int $heading_level ): void {
		self::assertSame(
			$heading_level,
			GuestbookRenderer::normalize_heading_level( $heading_level )
		);
	}

	/**
	 * Tests unsafe heading levels.
	 */
	public function test_rejects_invalid_heading_levels(): void {
		foreach ( array( 1, 7, '3', null, 3.0 ) as $heading_level ) {
			self::assertSame( 2, GuestbookRenderer::normalize_heading_level( $heading_level ) );
		}
	}
}
