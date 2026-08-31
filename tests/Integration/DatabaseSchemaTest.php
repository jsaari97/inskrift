<?php
/**
 * Database schema integration tests.
 *
 * @package Inskrift
 */

declare(strict_types=1);

namespace Inskrift\Tests\Integration;

use Inskrift\Database\Schema;
use WP_UnitTestCase;

/**
 * Tests the plugin database schema.
 */
final class DatabaseSchemaTest extends WP_UnitTestCase {
	/**
	 * Confirms that the installer creates the entry table.
	 */
	public function test_creates_entry_table(): void {
		global $wpdb;

		Schema::install();

		$table_name = Schema::table_name( $wpdb );

		self::assertSame(
			$table_name,
			$wpdb->get_var(
				$wpdb->prepare(
					'SHOW TABLES LIKE %s',
					$wpdb->esc_like( $table_name )
				)
			)
		);
	}

	/**
	 * Confirms that the installer stores the schema version.
	 */
	public function test_stores_schema_version(): void {
		Schema::install();

		self::assertSame(
			1,
			(int) get_option( 'inskrift_db_version' )
		);
	}

	/**
	 * Confirms that installation can run more than one time.
	 */
	public function test_installation_is_repeatable(): void {
		global $wpdb;

		Schema::install();
		Schema::install();

		$table_name = Schema::table_name( $wpdb );

		self::assertSame(
			1,
			(int) get_option( 'inskrift_db_version' )
		);

		self::assertSame(
			$table_name,
			$wpdb->get_var(
				$wpdb->prepare(
					'SHOW TABLES LIKE %s',
					$wpdb->esc_like( $table_name )
				)
			)
		);
	}

	/**
	 * Confirms that the update check installs a missing schema version.
	 */
	public function test_update_check_installs_missing_schema_version(): void {
		delete_option( 'inskrift_db_version' );

		Schema::maybe_update();

		self::assertSame(
			Schema::VERSION,
			(int) get_option( 'inskrift_db_version' )
		);
	}

	/**
	 * Confirms that the table contains the required columns.
	 */
	public function test_creates_expected_columns(): void {
		global $wpdb;

		Schema::install();

		$query = $wpdb->prepare(
			'SHOW COLUMNS FROM %i',
			Schema::table_name( $wpdb )
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query prepared above.
		$columns = $wpdb->get_results( $query, ARRAY_A );

		self::assertSame(
			array( 'id', 'name', 'message', 'status', 'created_at' ),
			array_column( $columns, 'Field' )
		);
	}

	/**
	 * Confirms that pending is the default status.
	 */
	public function test_pending_is_the_default_status(): void {
		global $wpdb;

		Schema::install();

		$query = $wpdb->prepare(
			'SHOW COLUMNS FROM %i LIKE %s',
			Schema::table_name( $wpdb ),
			'status'
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query prepared above.
		$column = $wpdb->get_row( $query, ARRAY_A );

		self::assertIsArray( $column );
		self::assertSame( 'pending', $column['Default'] );
	}

	/**
	 * Confirms that the table contains the required indexes.
	 */
	public function test_creates_expected_indexes(): void {
		global $wpdb;

		Schema::install();

		$query = $wpdb->prepare(
			'SHOW INDEX FROM %i',
			Schema::table_name( $wpdb )
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query prepared above.
		$indexes = $wpdb->get_results( $query, ARRAY_A );
		$names   = array_column( $indexes, 'Key_name' );
		$columns = array_values(
			array_map(
				static fn ( array $index ): string => (string) $index['Column_name'],
				array_filter(
					$indexes,
					static fn ( array $index ): bool => 'status_created' === $index['Key_name']
				)
			)
		);

		self::assertContains( 'PRIMARY', $names );
		self::assertContains( 'status_created', $names );
		self::assertSame( array( 'status', 'created_at', 'id' ), $columns );
	}
}
