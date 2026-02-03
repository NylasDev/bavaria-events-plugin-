<?php
/**
 * WordPress cron scheduler for Bavaria Events
 */

class Bavaria_Events_Cron_Scheduler {

	/**
	 * Schedule weekly crawl
	 */
	public static function schedule_cron() {
		// If cron not scheduled, schedule it
		if ( ! wp_next_scheduled( BAVARIA_EVENTS_CRON_HOOK ) ) {
			wp_schedule_event( time(), BAVARIA_EVENTS_CRAWL_SCHEDULE, BAVARIA_EVENTS_CRON_HOOK );
		}

		// Hook the cron action
		add_action( BAVARIA_EVENTS_CRON_HOOK, [ __CLASS__, 'run_crawl' ] );
	}

	/**
	 * Unschedule cron
	 */
	public static function unschedule_cron() {
		$timestamp = wp_next_scheduled( BAVARIA_EVENTS_CRON_HOOK );

		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, BAVARIA_EVENTS_CRON_HOOK );
		}
	}

	/**
	 * Run the crawl (called by cron)
	 */
	public static function run_crawl() {
		// Run the crawler
		Bavaria_Events_Crawler::crawl_events();
	}
}
