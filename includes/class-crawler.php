<?php
/**
 * Web crawler for Bavaria Yachts events
 */

class Bavaria_Events_Crawler {

	/**
	 * Fetch and parse events from Bavaria Yachts website
	 */
	public static function crawl_events() {
		$start_time = microtime( true );

		try {
			// Fetch HTML
			$html = self::fetch_html( BAVARIA_EVENTS_SOURCE_URL );

			if ( ! $html ) {
				throw new Exception( 'Failed to fetch events page' );
			}

			// Parse events
			$events = self::parse_events( $html );

			if ( empty( $events ) ) {
				throw new Exception( 'No events found on page' );
			}

			// Process and translate events
			$processed_count = 0;
			$updated_count   = 0;

			foreach ( $events as $event ) {
				// Translate event
				$event = Bavaria_Events_Translator::translate_event( $event );

				// Save to database
				$result = Bavaria_Events_DB_Handler::insert_or_update_event( $event );

				if ( $result ) {
					$processed_count++;
					$updated_count++;
				}
			}

			// Cleanup old events
			Bavaria_Events_DB_Handler::cleanup_old_events();

			// Log successful crawl
			$duration = microtime( true ) - $start_time;
			Bavaria_Events_DB_Handler::log_crawl( 'success', count( $events ), $updated_count, '', $duration );

			return [
				'success'  => true,
				'message'  => sprintf( 'Found %d events, updated %d', count( $events ), $updated_count ),
				'events'   => count( $events ),
				'updated'  => $updated_count,
				'duration' => $duration,
			];
		} catch ( Exception $e ) {
			$duration = microtime( true ) - $start_time;
			Bavaria_Events_DB_Handler::log_crawl( 'failed', 0, 0, $e->getMessage(), $duration );

			return [
				'success'  => false,
				'message'  => 'Crawl failed: ' . $e->getMessage(),
				'duration' => $duration,
			];
		}
	}

	/**
	 * Fetch HTML from URL
	 */
	private static function fetch_html( $url ) {
		$response = wp_remote_get(
			$url,
			[
				'timeout'   => 30,
				'sslverify' => true,
				'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
			]
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $status_code ) {
			return false;
		}

		return wp_remote_retrieve_body( $response );
	}

	/**
	 * Parse events from HTML
	 */
	private static function parse_events( $html ) {
		$dom = new DOMDocument();

		// Suppress warnings from invalid HTML
		libxml_use_internal_errors( true );
		$dom->loadHTML( $html );
		libxml_clear_errors();

		$xpath  = new DOMXPath( $dom );
		$events = [];

		// Find all event items
		$event_items = $xpath->query(
			"//a[contains(@class, 'event-wrapper') or contains(@class, 'content-item') and contains(@class, 'main-event')]"
		);

		foreach ( $event_items as $item ) {
			$event = self::extract_event_data( $item, $xpath );

			if ( ! empty( $event['event_title'] ) && ! empty( $event['event_link'] ) ) {
				$events[] = $event;
			}
		}

		// If no events found, try alternative selector
		if ( empty( $events ) ) {
			$event_items = $xpath->query( "//div[contains(@class, 'event__event')]" );

			foreach ( $event_items as $item ) {
				// Get parent link
				$link = $item->parentNode;
				if ( $link && 'a' === $link->nodeName ) {
					$event = self::extract_event_data( $link, $xpath );

					if ( ! empty( $event['event_title'] ) && ! empty( $event['event_link'] ) ) {
						$events[] = $event;
					}
				}
			}
		}

		return $events;
	}

	/**
	 * Extract event data from DOM node
	 */
	private static function extract_event_data( $item, $xpath ) {
		$event = [
			'event_title' => '',
			'event_link'  => '',
			'start_date'  => '',
			'end_date'    => '',
			'location'    => '',
		];

		// Get link
		$href = $item->getAttribute( 'href' );
		if ( ! empty( $href ) ) {
			$event['event_link'] = self::normalize_url( $href );
		}

		// Get title
		$title_nodes = $xpath->query( ".//span[contains(@class, 'gamma heading') or contains(@class, 'epsilon heading')]", $item );
		if ( $title_nodes->length > 0 ) {
			$event['event_title'] = trim( $title_nodes->item( 0 )->textContent );
		}

		// Get dates
		$date_nodes = $xpath->query( ".//span[contains(@class, 'icon-calendar-before')]", $item );
		if ( $date_nodes->length > 0 ) {
			$date_text = trim( $date_nodes->item( 0 )->textContent );
			$dates     = self::parse_dates( $date_text );

			if ( ! empty( $dates ) ) {
				$event['start_date'] = $dates['start_date'];
				$event['end_date']   = $dates['end_date'];
			}
		}

		// Get location
		$location_nodes = $xpath->query( ".//span[contains(@class, 'icon-pin-outline-before')]", $item );
		if ( $location_nodes->length > 0 ) {
			$event['location'] = trim( $location_nodes->item( 0 )->textContent );
		}

		return $event;
	}

	/**
	 * Parse dates from Bavaria format: "07.02.2026 to 15.02.2026"
	 */
	private static function parse_dates( $date_string ) {
		$pattern = '/(\d{2})\.(\d{2})\.(\d{4})\s+to\s+(\d{2})\.(\d{2})\.(\d{4})/';

		if ( ! preg_match( $pattern, $date_string, $matches ) ) {
			return false;
		}

		return [
			'start_date' => $matches[3] . '-' . $matches[2] . '-' . $matches[1],
			'end_date'   => $matches[6] . '-' . $matches[5] . '-' . $matches[4],
		];
	}

	/**
	 * Normalize URL
	 */
	private static function normalize_url( $url ) {
		if ( strpos( $url, 'http' ) === 0 ) {
			return $url;
		}

		// Remove leading slash
		$url = ltrim( $url, '/' );

		// Skip /de-de/ prefix - we want English version
		$url = str_replace( '/de-de/', '/', $url );

		return 'https://www.bavariayachts.com/' . $url;
	}
}
