<?php
/**
 * Public frontend display for Bavaria Events
 */

class Bavaria_Events_Public_Display {

	/**
	 * Initialize public display
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_styles' ] );
	}

	/**
	 * Enqueue frontend styles
	 */
	public static function enqueue_styles() {
		// Enqueue Font Awesome
		wp_enqueue_style(
			'font-awesome',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/all.min.css',
			[],
			'6.4.0'
		);

		wp_enqueue_style(
			'bavaria-events-public',
			BAVARIA_EVENTS_ASSETS_URL . 'css/public.css',
			[ 'font-awesome' ],
			BAVARIA_EVENTS_VERSION
		);
	}
}
