<?php
/**
 * Database handler for Bavaria Events
 */

class Bavaria_Events_DB_Handler {

	/**
	 * Create plugin database tables
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Events table
		$events_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}bavaria_events (
			id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			event_title VARCHAR(255) NOT NULL,
			event_title_ro VARCHAR(255),
			start_date DATE NOT NULL,
			end_date DATE NOT NULL,
			location VARCHAR(255) NOT NULL,
			location_ro VARCHAR(255),
			event_link VARCHAR(512) NOT NULL UNIQUE KEY idx_event_link,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			KEY idx_start_date (start_date),
			KEY idx_location (location),
			KEY idx_end_date (end_date)
		) $charset_collate;";

		// Logs table
		$logs_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}bavaria_event_logs (
			id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			crawl_date DATETIME DEFAULT CURRENT_TIMESTAMP,
			status ENUM('success', 'partial', 'failed') DEFAULT 'success',
			events_found INT DEFAULT 0,
			events_updated INT DEFAULT 0,
			error_message LONGTEXT,
			duration_seconds FLOAT,
			KEY idx_crawl_date (crawl_date)
		) $charset_collate;";

		// Translation cache table
		$cache_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}bavaria_translation_cache (
			id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			source_text LONGTEXT NOT NULL,
			source_hash VARCHAR(64) NOT NULL UNIQUE KEY idx_source_hash,
			translated_text LONGTEXT NOT NULL,
			source_lang VARCHAR(5) DEFAULT 'en',
			target_lang VARCHAR(5) DEFAULT 'ro',
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			KEY idx_created_at (created_at)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $events_table );
		dbDelta( $logs_table );
		dbDelta( $cache_table );
	}

	/**
	 * Drop plugin database tables
	 */
	public static function drop_tables() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bavaria_events" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bavaria_event_logs" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bavaria_translation_cache" );
	}

	/**
	 * Insert or update an event
	 */
	public static function insert_or_update_event( $event_data ) {
		global $wpdb;

		$table = $wpdb->prefix . 'bavaria_events';

		// Check if event exists by link
		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id FROM $table WHERE event_link = %s",
				$event_data['event_link']
			)
		);

		$data = [
			'event_title'     => sanitize_text_field( $event_data['event_title'] ),
			'event_title_ro'  => ! empty( $event_data['event_title_ro'] ) ? sanitize_text_field( $event_data['event_title_ro'] ) : null,
			'start_date'      => sanitize_text_field( $event_data['start_date'] ),
			'end_date'        => sanitize_text_field( $event_data['end_date'] ),
			'location'        => sanitize_text_field( $event_data['location'] ),
			'location_ro'     => ! empty( $event_data['location_ro'] ) ? sanitize_text_field( $event_data['location_ro'] ) : null,
			'event_link'      => esc_url_raw( $event_data['event_link'] ),
		];

		$format = [ '%s', '%s', '%s', '%s', '%s', '%s', '%s' ];

		if ( $existing ) {
			// Update existing event
			$wpdb->update( $table, $data, [ 'id' => $existing->id ], $format, [ '%d' ] );
			return $existing->id;
		} else {
			// Insert new event
			$data['created_at'] = current_time( 'mysql' );
			$data['updated_at'] = current_time( 'mysql' );
			$format[]          = '%s';
			$format[]          = '%s';

			$wpdb->insert( $table, $data, $format );
			return $wpdb->insert_id;
		}
	}

	/**
	 * Get all upcoming events
	 */
	public static function get_upcoming_events( $language = 'en', $limit = 20, $sort = 'date' ) {
		global $wpdb;

		$table = $wpdb->prefix . 'bavaria_events';

		$order_by = ( 'title' === $sort ) ? 'event_title ASC' : 'start_date ASC';

		$query = $wpdb->prepare(
			"SELECT * FROM $table WHERE end_date >= %s ORDER BY $order_by LIMIT %d",
			current_time( 'Y-m-d' ),
			intval( $limit )
		);

		$events = $wpdb->get_results( $query );

		// Filter by language
		return self::format_events_by_language( $events, $language );
	}

	/**
	 * Get all events (including past)
	 */
	public static function get_all_events( $language = 'en', $limit = 100 ) {
		global $wpdb;

		$table = $wpdb->prefix . 'bavaria_events';

		$query = $wpdb->prepare(
			"SELECT * FROM $table ORDER BY start_date DESC LIMIT %d",
			intval( $limit )
		);

		$events = $wpdb->get_results( $query );

		return self::format_events_by_language( $events, $language );
	}

	/**
	 * Format events by language
	 */
	private static function format_events_by_language( $events, $language = 'en' ) {
		if ( empty( $events ) ) {
			return [];
		}

		$formatted = [];

		foreach ( $events as $event ) {
			if ( 'ro' === $language ) {
				// Use Romanian if available, fallback to English
				$event->event_title = $event->event_title_ro ?: $event->event_title;
				$event->location    = $event->location_ro ?: $event->location;
			}

			$formatted[] = $event;
		}

		return $formatted;
	}

	/**
	 * Delete old events (older than 1 year)
	 */
	public static function cleanup_old_events() {
		global $wpdb;

		$table = $wpdb->prefix . 'bavaria_events';

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $table WHERE end_date < DATE_SUB(%s, INTERVAL 1 YEAR)",
				current_time( 'Y-m-d' )
			)
		);
	}

	/**
	 * Get event count
	 */
	public static function get_event_count() {
		global $wpdb;

		$table = $wpdb->prefix . 'bavaria_events';

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
	}

	/**
	 * Log crawl attempt
	 */
	public static function log_crawl( $status, $events_found, $events_updated, $error_message = '', $duration = 0 ) {
		global $wpdb;

		$table = $wpdb->prefix . 'bavaria_event_logs';

		$wpdb->insert(
			$table,
			[
				'crawl_date'       => current_time( 'mysql' ),
				'status'           => sanitize_text_field( $status ),
				'events_found'     => intval( $events_found ),
				'events_updated'   => intval( $events_updated ),
				'error_message'    => sanitize_text_field( $error_message ),
				'duration_seconds' => floatval( $duration ),
			],
			[ '%s', '%s', '%d', '%d', '%s', '%f' ]
		);
	}

	/**
	 * Get last crawl log
	 */
	public static function get_last_crawl_log() {
		global $wpdb;

		$table = $wpdb->prefix . 'bavaria_event_logs';

		return $wpdb->get_row( "SELECT * FROM $table ORDER BY crawl_date DESC LIMIT 1" );
	}

	/**
	 * Get recent crawl logs
	 */
	public static function get_recent_crawl_logs( $limit = 10 ) {
		global $wpdb;

		$table = $wpdb->prefix . 'bavaria_event_logs';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table ORDER BY crawl_date DESC LIMIT %d",
				intval( $limit )
			)
		);
	}
}
