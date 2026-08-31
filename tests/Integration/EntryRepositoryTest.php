<?php
/**
 * Entry repository integration tests.
 *
 * @package Inskrift
 */

declare(strict_types=1);

namespace Inskrift\Tests\Integration;

use Inskrift\Database\Schema;
use Inskrift\Guestbook\Entry;
use Inskrift\Guestbook\EntryRepository;
use Inskrift\Guestbook\EntryStatus;
use RuntimeException;
use WP_UnitTestCase;

/**
 * Tests the entry repository with a WordPress database.
 */
final class EntryRepositoryTest extends WP_UnitTestCase {
	/**
	 * Repository under test.
	 *
	 * @var EntryRepository $repository Repository.
	 */
	private EntryRepository $repository;

	/**
	 * IDs that must be deleted after each test.
	 *
	 * @var list<int>
	 */
	private array $entry_ids = array();

	/**
	 * Creates the database table and repository before each test.
	 */
	public function set_up(): void {
		parent::set_up();

		global $wpdb;

		Schema::install();

		$this->repository = new EntryRepository( $wpdb );
		$this->entry_ids  = array();
	}

	/**
	 * Deletes test entries after each test.
	 */
	public function tear_down(): void {
		foreach ( $this->entry_ids as $entry_id ) {
			$this->repository->delete( $entry_id );
		}

		parent::tear_down();
	}

	/**
	 * Tests insertion and retrieval.
	 */
	public function test_inserts_and_finds_entry(): void {
		$entry_id = $this->insert_entry(
			'Jane',
			'Thank you for the website.'
		);

		$entry = $this->find_required_entry( $entry_id );

		self::assertSame( $entry_id, $entry->id );
		self::assertSame( 'Jane', $entry->name );
		self::assertSame(
			'Thank you for the website.',
			$entry->message
		);
		self::assertSame( EntryStatus::Pending, $entry->status );
		self::assertSame(
			'UTC',
			$entry->created_at->getTimezone()->getName()
		);
	}

	/**
	 * Tests the default pending status.
	 */
	public function test_new_entry_is_pending_by_default(): void {
		$entry_id = $this->insert_entry(
			'Jane',
			'A pending message.'
		);

		$entry = $this->find_required_entry( $entry_id );

		self::assertSame( EntryStatus::Pending, $entry->status );
	}

	/**
	 * Tests a status change.
	 */
	public function test_updates_entry_status(): void {
		$entry_id = $this->insert_entry(
			'Jane',
			'Please approve this message.'
		);

		$result = $this->repository->update_status(
			$entry_id,
			EntryStatus::Approved
		);

		$entry = $this->find_required_entry( $entry_id );

		self::assertTrue( $result );
		self::assertSame( EntryStatus::Approved, $entry->status );
	}

	/**
	 * Confirms that a missing entry cannot be updated.
	 */
	public function test_returns_false_when_updating_missing_entry(): void {
		self::assertFalse(
			$this->repository->update_status(
				999999999,
				EntryStatus::Approved
			)
		);
	}

	/**
	 * Confirms that an unchanged status returns false.
	 */
	public function test_returns_false_when_status_does_not_change(): void {
		$entry_id = $this->insert_entry(
			'Jane',
			'Pending message.'
		);

		self::assertFalse(
			$this->repository->update_status(
				$entry_id,
				EntryStatus::Pending
			)
		);
	}

	/**
	 * Tests status filtering.
	 */
	public function test_finds_entries_by_status(): void {
		$pending_id = $this->insert_entry(
			'Pending visitor',
			'Pending message.'
		);

		$approved_id = $this->insert_entry(
			'Approved visitor',
			'Approved message.',
			EntryStatus::Approved
		);

		$entries = $this->repository->find_by_status(
			EntryStatus::Approved
		);

		$entry_ids = array_map(
			static fn ( Entry $entry ): int => $entry->id,
			$entries
		);

		self::assertContains( $approved_id, $entry_ids );
		self::assertNotContains( $pending_id, $entry_ids );
	}

	/**
	 * Tests entry counting.
	 */
	public function test_counts_entries_by_status(): void {
		$count_before = $this->repository->count_by_status(
			EntryStatus::Spam
		);

		$this->insert_entry(
			'Spam visitor',
			'Spam message.',
			EntryStatus::Spam
		);

		$count_after = $this->repository->count_by_status(
			EntryStatus::Spam
		);

		self::assertSame( $count_before + 1, $count_after );
	}

	/**
	 * Tests entry deletion.
	 */
	public function test_deletes_entry(): void {
		$entry_id = $this->insert_entry(
			'Jane',
			'Delete this message.'
		);

		$result = $this->repository->delete( $entry_id );

		self::assertTrue( $result );
		self::assertNull( $this->repository->find( $entry_id ) );
	}

	/**
	 * Confirms that a missing entry cannot be deleted.
	 */
	public function test_returns_false_when_deleting_missing_entry(): void {
		self::assertFalse( $this->repository->delete( 999999999 ) );
	}

	/**
	 * Tests a missing entry.
	 */
	public function test_returns_null_for_missing_entry(): void {
		self::assertNull( $this->repository->find( 999999999 ) );
	}

	/**
	 * Tests pagination.
	 */
	public function test_paginates_entries(): void {
		$first_id = $this->insert_entry(
			'First',
			'First message.',
			EntryStatus::Approved
		);

		$second_id = $this->insert_entry(
			'Second',
			'Second message.',
			EntryStatus::Approved
		);

		$third_id = $this->insert_entry(
			'Third',
			'Third message.',
			EntryStatus::Approved
		);

		$first_page = $this->repository->find_by_status(
			EntryStatus::Approved,
			1,
			2
		);

		$second_page = $this->repository->find_by_status(
			EntryStatus::Approved,
			2,
			2
		);

		self::assertSame(
			array( $third_id, $second_id ),
			array_map(
				static fn ( Entry $entry ): int => $entry->id,
				$first_page
			)
		);

		self::assertSame(
			array( $first_id ),
			array_map(
				static fn ( Entry $entry ): int => $entry->id,
				$second_page
			)
		);
	}

	/**
	 * Confirms that page numbers must be positive.
	 */
	public function test_rejects_page_below_one(): void {
		$this->expectException( \InvalidArgumentException::class );

		$this->repository->find_by_status(
			EntryStatus::Approved,
			0,
			20
		);
	}

	/**
	 * Confirms that the page size cannot exceed the limit.
	 */
	public function test_rejects_page_size_above_limit(): void {
		$this->expectException( \InvalidArgumentException::class );

		$this->repository->find_by_status(
			EntryStatus::Approved,
			1,
			101
		);
	}

	/**
	 * Confirms that page sizes must be positive.
	 */
	public function test_rejects_page_size_below_one(): void {
		$this->expectException( \InvalidArgumentException::class );

		$this->repository->find_by_status(
			EntryStatus::Approved,
			1,
			0
		);
	}

	/**
	 * Confirms that a status with no entries returns an empty list.
	 */
	public function test_returns_empty_list_when_status_has_no_entries(): void {
		self::assertSame(
			array(),
			$this->repository->find_by_status( EntryStatus::Spam )
		);
	}

	/**
	 * Confirms that names and messages support Unicode.
	 */
	public function test_stores_unicode_text(): void {
		$entry_id = $this->insert_entry(
			'Järvinen',
			'Kiitos vieraskirjasta! 😊'
		);

		$entry = $this->find_required_entry( $entry_id );

		self::assertSame( 'Järvinen', $entry->name );
		self::assertSame(
			'Kiitos vieraskirjasta! 😊',
			$entry->message
		);
	}

	/**
	 * Inserts an entry and records its ID for cleanup.
	 *
	 * @param string      $name Name.
	 * @param string      $message Message.
	 * @param EntryStatus $status Status.
	 */
	private function insert_entry(
		string $name,
		string $message,
		EntryStatus $status = EntryStatus::Pending,
	): int {
		$entry_id = $this->repository->insert(
			$name,
			$message,
			$status
		);

		$this->entry_ids[] = $entry_id;

		return $entry_id;
	}

	/**
	 * Finds an entry or stops the test.
	 *
	 * @param int $entry_id Entry ID.
	 *
	 * @throws RuntimeException If entry is not found.
	 */
	private function find_required_entry( int $entry_id ): Entry {
		$entry = $this->repository->find( $entry_id );

		if ( ! $entry instanceof Entry ) {
			throw new RuntimeException( 'The test entry was not found.' );
		}

		return $entry;
	}
}
