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
		wp_enqueue_style(
			'bavaria-events-public',
			BAVARIA_EVENTS_ASSETS_URL . 'css/public.css',
			[],
			BAVARIA_EVENTS_VERSION
		);
	}
}
