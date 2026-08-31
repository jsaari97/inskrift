<?php
/**
 * Guestbook entry repository
 *
 * @package Inskrift
 */

declare(strict_types=1);

namespace Inskrift\Guestbook;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use RuntimeException;
use wpdb;
use Inskrift\Database\Schema;

/**
 * Repository for guestbook entries.
 */
final readonly class EntryRepository {
	/**
	 * Creates a repository.
	 *
	 * @param wpdb $database WordPress database connection.
	 */
	public function __construct( private wpdb $database ) {}

	/**
	 * Inserts an entry and returns its database ID.
	 *
	 * @param string      $name    Visitor name.
	 * @param string      $message Visitor message.
	 * @param EntryStatus $status  Moderation status.
	 *
	 * @throws RuntimeException If fails to save entry.
	 */
	public function insert(
		string $name,
		string $message,
		EntryStatus $status = EntryStatus::Pending,
	): int {
		$result = $this->database->insert(
			$this->table_name(),
			array(
				'name'       => $name,
				'message'    => $message,
				'status'     => $status->value,
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%s', '%s', '%s', '%s' )
		);

		if ( false === $result ) {
			throw new RuntimeException( 'Could not insert the guestbook entry.' );
		}

		return (int) $this->database->insert_id;
	}

	/**
	 * Finds one entry.
	 *
	 * @param int $id Entry ID.
	 *
	 * @throws RuntimeException If query is invalid.
	 */
	public function find( int $id ): ?Entry {
		$query = $this->database->prepare(
			'SELECT id, name, message, status, created_at
			FROM %i
			WHERE id = %d',
			$this->table_name(),
			$id
		);

		if ( ! is_string( $query ) ) {
			throw new RuntimeException( 'Could not prepare the entry query.' );
		}

		$row = $this->database->get_row( $query, ARRAY_A );

		$this->throw_on_database_error( 'Could not read the guestbook entry.' );

		if ( ! is_array( $row ) ) {
			return null;
		}

		return $this->map_row_to_entry( $row );
	}

	/**
	 * Finds entries with the specified status.
	 *
	 * @param EntryStatus $status Entry status.
	 * @param int         $page Page.
	 * @param int         $per_page Entries per page.
	 *
	 * @throws InvalidArgumentException If invalid arguments.
	 * @throws RuntimeException If fails to query entries.
	 *
	 * @return list<Entry>
	 */
	public function find_by_status(
		EntryStatus $status,
		int $page = 1,
		int $per_page = 20,
	): array {
		if ( $page < 1 ) {
			throw new InvalidArgumentException( 'Page must be at least 1.' );
		}

		if ( $per_page < 1 || $per_page > 100 ) {
			throw new InvalidArgumentException( 'Entries per page must be between 1 and 100.' );
		}

		$offset = ( $page - 1 ) * $per_page;

		$query = $this->database->prepare(
			'SELECT id, name, message, status, created_at
			FROM %i
			WHERE status = %s
			ORDER BY created_at DESC, id DESC
			LIMIT %d OFFSET %d',
			$this->table_name(),
			$status->value,
			$per_page,
			$offset
		);

		if ( ! is_string( $query ) ) {
			throw new RuntimeException( 'Could not prepare the guestbook entry query.' );
		}

		$rows = $this->database->get_results( $query, ARRAY_A );

		$this->throw_on_database_error( 'Could not read guestbook entries.' );

		if ( ! is_array( $rows ) ) {
			throw new RuntimeException( 'Could not read guestbook entries.' );
		}

		$entries = array();

		foreach ( $rows as $row ) {
			if ( is_array( $row ) ) {
				$entries[] = $this->map_row_to_entry( $row );
			}
		}

		return $entries;
	}


	/**
	 * Counts entries with the specified status.
	 *
	 * @param EntryStatus $status Entry status.
	 *
	 * @throws RuntimeException If the query could not be prepared.
	 */
	public function count_by_status( EntryStatus $status ): int {
		$query = $this->database->prepare(
			'SELECT COUNT(*)
			FROM %i
			WHERE status = %s',
			$this->table_name(),
			$status->value
		);

		if ( ! is_string( $query ) ) {
			throw new RuntimeException( 'Could not prepare the entry count query.' );
		}

		$count = $this->database->get_var( $query );

		$this->throw_on_database_error( 'Could not count guestbook entries.' );

		return (int) $count;
	}

	/**
	 * Changes an entry status.
	 *
	 * @param int         $id Entry ID.
	 * @param EntryStatus $status Entry status.
	 *
	 * @throws RuntimeException If entry update fails.
	 */
	public function update_status( int $id, EntryStatus $status ): bool {
		$result = $this->database->update(
			$this->table_name(),
			array(
				'status' => $status->value,
			),
			array(
				'id' => $id,
			),
			array( '%s' ),
			array( '%d' )
		);

		if ( false === $result ) {
			throw new RuntimeException( 'Could not update the guestbook entry.' );
		}

		return 1 === $result;
	}

	/**
	 * Deletes an entry.
	 *
	 * @param int $id Entry ID.
	 *
	 * @throws RuntimeException If entry deletion fails.
	 */
	public function delete( int $id ): bool {
		$result = $this->database->delete(
			$this->table_name(),
			array(
				'id' => $id,
			),
			array( '%d' )
		);

		if ( false === $result ) {
			throw new RuntimeException( 'Could not delete the guestbook entry.' );
		}

		return 1 === $result;
	}

	/**
	 * Returns the full table name.
	 */
	private function table_name(): string {
		return Schema::table_name( $this->database );
	}

	/**
	 * Converts a database row to an entry.
	 *
	 * @param array<string, mixed> $row Database row.
	 */
	private function map_row_to_entry( array $row ): Entry {
		return new Entry(
			id: (int) $row['id'],
			name: (string) $row['name'],
			message: (string) $row['message'],
			status: EntryStatus::from( (string) $row['status'] ),
			created_at: new DateTimeImmutable(
				(string) $row['created_at'],
				new DateTimeZone( 'UTC' )
			),
		);
	}

	/**
	 * Throws an exception after a database error.
	 *
	 * @param string $message Safe error message.
	 *
	 * @throws RuntimeException When the database reports an error.
	 */
	private function throw_on_database_error( string $message ): void {
		if ( '' !== $this->database->last_error ) {
			throw new RuntimeException( esc_html( $message ) );
		}
	}
}
