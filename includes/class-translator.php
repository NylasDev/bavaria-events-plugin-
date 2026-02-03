<?php
/**
 * Google Translate API integration for Bavaria Events
 */

class Bavaria_Events_Translator {

	const API_ENDPOINT = 'https://translation.googleapis.com/language/translate/v2';
	const CACHE_DURATION = 30 * DAY_IN_SECONDS;
	const SOURCE_LANG = 'en';
	const TARGET_LANG = 'ro';

	/**
	 * Translate event data
	 */
	public static function translate_event( $event ) {
		// Check if translation is enabled
		if ( ! self::is_translation_enabled() ) {
			return $event;
		}

		// Get API key
		$api_key = self::get_api_key();

		if ( empty( $api_key ) ) {
			return $event;
		}

		// Translate title
		if ( ! empty( $event['event_title'] ) ) {
			$event['event_title_ro'] = self::translate_text( $event['event_title'], $api_key );
		}

		// Translate location
		if ( ! empty( $event['location'] ) ) {
			$event['location_ro'] = self::translate_text( $event['location'], $api_key );
		}

		return $event;
	}

	/**
	 * Translate text using Google Translate API
	 */
	public static function translate_text( $text, $api_key = null ) {
		if ( empty( $text ) ) {
			return '';
		}

		// Check cache first
		$cached = self::get_cached_translation( $text );

		if ( ! empty( $cached ) ) {
			return $cached;
		}

		// Get API key if not provided
		if ( empty( $api_key ) ) {
			$api_key = self::get_api_key();
		}

		if ( empty( $api_key ) ) {
			return $text; // Return original if no API key
		}

		// Call Google Translate API
		$translated = self::call_google_api( $text, $api_key );

		if ( ! empty( $translated ) ) {
			// Cache the translation
			self::cache_translation( $text, $translated );
			return $translated;
		}

		// Return original if translation fails
		return $text;
	}

	/**
	 * Translate UI labels
	 */
	public static function translate_label( $label ) {
		if ( ! self::is_translation_enabled() ) {
			return $label;
		}

		$translations = [
			'Date'        => 'Data',
			'Location'    => 'Locație',
			'Learn More'  => 'Aflați mai multe',
			'Other Events' => 'Alte Evenimente',
			'to'          => 'până la',
		];

		return isset( $translations[ $label ] ) ? $translations[ $label ] : $label;
	}

	/**
	 * Call Google Translate API
	 */
	private static function call_google_api( $text, $api_key ) {
		$url = add_query_arg(
			[
				'key' => $api_key,
			],
			self::API_ENDPOINT
		);

		$body = [
			'q'        => $text,
			'source_language_code' => self::SOURCE_LANG,
			'target_language_code' => self::TARGET_LANG,
		];

		$response = wp_remote_post(
			$url,
			[
				'timeout' => 10,
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'body'    => json_encode( $body ),
			]
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $body['data']['translations'][0]['translatedText'] ) ) {
			return null;
		}

		// Clean HTML entities
		return html_entity_decode( $body['data']['translations'][0]['translatedText'] );
	}

	/**
	 * Get cached translation
	 */
	private static function get_cached_translation( $text ) {
		global $wpdb;

		$hash  = hash( 'sha256', $text );
		$table = $wpdb->prefix . 'bavaria_translation_cache';

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT translated_text FROM $table WHERE source_hash = %s AND source_lang = %s AND target_lang = %s",
				$hash,
				self::SOURCE_LANG,
				self::TARGET_LANG
			)
		);

		return $result;
	}

	/**
	 * Cache translation
	 */
	private static function cache_translation( $source_text, $translated_text ) {
		global $wpdb;

		$hash  = hash( 'sha256', $source_text );
		$table = $wpdb->prefix . 'bavaria_translation_cache';

		$wpdb->insert(
			$table,
			[
				'source_text'    => $source_text,
				'source_hash'    => $hash,
				'translated_text' => $translated_text,
				'source_lang'    => self::SOURCE_LANG,
				'target_lang'    => self::TARGET_LANG,
				'created_at'     => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%s', '%s', '%s', '%s' ]
		);
	}

	/**
	 * Check if translation is enabled
	 */
	private static function is_translation_enabled() {
		return (bool) get_option( 'bavaria_events_enable_translation', false );
	}

	/**
	 * Get API key
	 */
	private static function get_api_key() {
		return get_option( 'bavaria_events_google_api_key' );
	}
}
