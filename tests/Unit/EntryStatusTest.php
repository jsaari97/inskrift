<?php
/**
 * Entry status unit tests.
 *
 * @package Inskrift
 */

declare(strict_types=1);

namespace Inskrift\Tests\Unit;

use Inskrift\Guestbook\EntryStatus;
use PHPUnit\Framework\TestCase;

/**
 * Tests guestbook entry statuses.
 */
final class EntryStatusTest extends TestCase {
	/**
	 * Confirms the database values for all statuses.
	 */
	public function test_has_expected_values(): void {
		self::assertSame( 'pending', EntryStatus::Pending->value );
		self::assertSame( 'approved', EntryStatus::Approved->value );
		self::assertSame( 'spam', EntryStatus::Spam->value );
	}

	/**
	 * Confirms that database values create the correct statuses.
	 */
	public function test_creates_status_from_database_value(): void {
		self::assertSame(
			EntryStatus::Pending,
			EntryStatus::from( 'pending' )
		);

		self::assertSame(
			EntryStatus::Approved,
			EntryStatus::from( 'approved' )
		);

		self::assertSame(
			EntryStatus::Spam,
			EntryStatus::from( 'spam' )
		);
	}

	/**
	 * Confirms that only three statuses exist.
	 */
	public function test_has_three_statuses(): void {
		self::assertCount( 3, EntryStatus::cases() );
	}
}
