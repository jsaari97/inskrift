<?php
/**
 * Database schema.
 *
 * @package Inskrift
 */

declare(strict_types=1);

namespace Inskrift\Database;

use wpdb;
use RuntimeException;

/**
 * Creates and updates the plugin database tables.
 */
final class Schema {
	/**
	 * Current database schema version.
	 */
	public const VERSION = 1;

	/**
	 * WordPress option that stores the installed schema version.
	 */
	private const VERSION_OPTION = 'inskrift_db_version';

	/**
	 * Creates or updates the database table.
	 *
	 * @throws RuntimeException When the schema cannot be installed.
	 */
	public static function install(): void {
		global $wpdb;

		$table_name      = self::table_name( $wpdb );
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(100) NOT NULL,
				message text NOT NULL,
				status varchar(20) NOT NULL DEFAULT 'pending',
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY status_created (status, created_at, id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );

		if ( '' !== $wpdb->last_error ) {
			throw new RuntimeException( 'Could not install the Inskrift database schema.' );
		}

		$table_query = $wpdb->prepare(
			'SHOW TABLES LIKE %s',
			$wpdb->esc_like( $table_name )
		);

		if ( ! is_string( $table_query ) ) {
			throw new RuntimeException( 'Could not prepare the schema verification query.' );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query prepared above.
		$installed_table = $wpdb->get_var( $table_query );

		if ( $table_name !== $installed_table ) {
			throw new RuntimeException( 'Could not verify the Inskrift database table.' );
		}

		update_option( self::VERSION_OPTION, self::VERSION );
	}

	/**
	 * Updates the schema when the installed version is old.
	 *
	 * @throws RuntimeException When the schema cannot be updated.
	 */
	public static function maybe_update(): void {
		$installed_option = (int) get_option( self::VERSION_OPTION, 0 );

		if ( $installed_option < self::VERSION ) {
			self::install();
		}
	}

	/**
	 * Returns the full entry table name.
	 *
	 * @param wpdb $database Database object.
	 */
	public static function table_name( wpdb $database ): string {
		return $database->prefix . 'inskrift_entries';
	}
}
