<?php
/**
 * Shortcode renderer for Bavaria Events
 */

class Bavaria_Events_Shortcode_Renderer {

	/**
	 * Register shortcode
	 */
	public static function register() {
		add_shortcode( 'bavaria_events', [ __CLASS__, 'render' ] );
	}

	/**
	 * Render shortcode
	 */
	public static function render( $atts ) {
		// Parse attributes
		$atts = shortcode_atts(
			[
				'language' => 'en',
				'limit'    => 20,
				'sort'     => 'date',
			],
			$atts,
			'bavaria_events'
		);

		$language = sanitize_text_field( $atts['language'] );
		$limit    = intval( $atts['limit'] );
		$sort     = sanitize_text_field( $atts['sort'] );

		// Get events from database
		$events = Bavaria_Events_DB_Handler::get_upcoming_events( $language, $limit, $sort );

		if ( empty( $events ) ) {
			return '<div class="bavaria-events-notice">No upcoming events found.</div>';
		}

		// Load template
		ob_start();
		include BAVARIA_EVENTS_PLUGIN_DIR . 'templates/events-table.php';
		return ob_get_clean();
	}
}
