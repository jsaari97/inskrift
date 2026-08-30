<?php
/**
 * Guestbook block integration tests.
 *
 * @package Inskrift
 */

declare(strict_types=1);

namespace Inskrift\Tests\Integration;

use WP_UnitTestCase;

/**
 * Tests the block in WordPress.
 */
final class GuestbookBlockTest extends WP_UnitTestCase {
	/**
	 * Confirms that WordPress knows the block.
	 */
	public function test_registers_the_guestbook_block(): void {
		$block_type = \WP_Block_Type_Registry::get_instance()->get_registered( 'inskrift/guestbook' );

		self::assertNotNull( $block_type );
		self::assertSame( array(), $block_type->view_script_handles );
	}

	/**
	 * Confirms the default title and heading.
	 */
	public function test_renders_the_default_title(): void {
		$output = do_blocks( '<!-- wp:inskrift/guestbook /-->' );

		self::assertStringContainsString( '<h2 class="inskrift-guestbook__title">Guestbook</h2>', $output );
		self::assertStringContainsString( 'wp-block-inskrift-guestbook', $output );
	}

	/**
	 * Confirms that a blank title hides the heading.
	 */
	public function test_hides_an_empty_title(): void {
		$output = do_blocks( '<!-- wp:inskrift/guestbook {"title":""} /-->' );

		self::assertStringNotContainsString( '<h', $output );
	}

	/**
	 * Confirms that the title is escaped.
	 */
	public function test_escapes_the_title(): void {
		$output = do_blocks( '<!-- wp:inskrift/guestbook {"title":"<script>alert(1)</script>","headingLevel":4} /-->' );

		self::assertStringContainsString(
			'<h4 class="inskrift-guestbook__title">&lt;script&gt;alert(1)&lt;/script&gt;</h4>',
			$output
		);
		self::assertStringNotContainsString( '<script>', $output );
	}

	/**
	 * Confirms that unsafe block input cannot select an unsafe element.
	 */
	public function test_rejects_an_invalid_heading_level(): void {
		$output = do_blocks( '<!-- wp:inskrift/guestbook {"title":"Title","headingLevel":7} /-->' );

		self::assertStringContainsString( '<h2 class="inskrift-guestbook__title">Title</h2>', $output );
	}
}
