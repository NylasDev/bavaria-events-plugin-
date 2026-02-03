<?php
/**
 * Events table template - Horizontal row layout (Avada style)
 *
 * Variables available:
 * - $events: Array of event objects
 * - $language: 'en' or 'ro'
 */

if ( empty( $events ) ) {
	return;
}

$lang_labels = [
	'en' => [
		'date'         => 'Date',
		'location'     => 'Location',
		'learn_more'   => 'Learn More',
		'to'           => 'to',
	],
	'ro' => [
		'date'         => 'Data',
		'location'     => 'Locație',
		'learn_more'   => 'Aflați mai multe',
		'to'           => 'până la',
	],
];

$labels = isset( $lang_labels[ $language ] ) ? $lang_labels[ $language ] : $lang_labels['en'];
?>

<div class="bavaria-events-container">
	<div class="bavaria-events-list">
		<?php foreach ( $events as $event ) : ?>
			<a href="<?php echo esc_url( $event->event_link ); ?>" class="event-row" target="_blank">
				<div class="event-row-content">
					<!-- Title Column -->
					<div class="event-column event-title-col">
						<span class="event-title"><?php echo esc_html( $event->event_title ); ?></span>
					</div>

					<!-- Date Column -->
					<div class="event-column event-date-col">
						<span class="event-date">
							<?php 
								$start = DateTime::createFromFormat( 'Y-m-d', $event->start_date );
								$end = DateTime::createFromFormat( 'Y-m-d', $event->end_date );
								if ( $start && $end ) {
									echo esc_html( $start->format( 'd.m.Y' ) . ' – ' . $end->format( 'd.m.Y' ) );
								}
							?>
						</span>
					</div>

					<!-- Location Column -->
					<div class="event-column event-location-col">
						<span class="event-location"><?php echo esc_html( $event->location ); ?></span>
					</div>

					<!-- Arrow Icon -->
					<div class="event-column event-arrow-col">
						<span class="event-arrow">→</span>
					</div>
				</div>
				<div class="event-row-divider"></div>
			</a>
		<?php endforeach; ?>
	</div>
</div>
