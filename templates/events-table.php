<?php
/**
 * Events table template - matches Bavaria Yachts design
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
		'other_events' => 'Other Events',
		'to'           => 'to',
	],
	'ro' => [
		'date'         => 'Data',
		'location'     => 'Locație',
		'learn_more'   => 'Aflați mai multe',
		'other_events' => 'Alte Evenimente',
		'to'           => 'până la',
	],
];

$labels = isset( $lang_labels[ $language ] ) ? $lang_labels[ $language ] : $lang_labels['en'];
$first_event = array_shift( $events );
$remaining_events = $events;
?>

<section class="section-row module-events -padded-wide -in-viewport" data-viewport-offset="90%">
	<div class="col-wrapper l-1col -max-width-limited">
		<div class="col">
			
			<!-- Main Featured Event -->
			<a href="<?php echo esc_url( $first_event->event_link ); ?>" class="content-item main-event">
				<div class="col-wrapper l-2col-b2-33-66 l-2col-b2-33-66--inverted">
					<div class="col-one">
						<div class="content-text">
							<span class="gamma heading"><?php echo esc_html( $first_event->event_title ); ?></span>
						</div>
						<div class="content-info">
							<div class="info__date">
								<span class="icon-calendar-before meta-size">
									<?php 
										$start = DateTime::createFromFormat( 'Y-m-d', $first_event->start_date );
										$end = DateTime::createFromFormat( 'Y-m-d', $first_event->end_date );
										if ( $start && $end ) {
											echo esc_html( $start->format( 'd.m.Y' ) . ' ' . $labels['to'] . ' ' . $end->format( 'd.m.Y' ) );
										}
									?>
								</span>
							</div>
							<div class="info__location">
								<span class="icon-pin-outline-before meta-size"><?php echo esc_html( $first_event->location ); ?></span>
							</div>
						</div>
						<div class="content-link">
							<div class="-btn-alpha"><strong><?php echo esc_html( $labels['learn_more'] ); ?></strong><span class="btn-svg"> <svg width="72px" height="51px" viewBox="0 0 72 51" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g transform="translate(-884.000000, -3570.000000)"> <g transform="translate(210.000000, 3211.000000)"> <g transform="translate(674.000000, 359.000000)"> <polygon class="btn-l" fill="#003562" points="26.1738281 0 6.02832031 50.7299957 0 50.7299957 3.55271368e-15 0"></polygon> <polygon class="btn-r" fill="#003562" transform="translate(41.500000, 25.500000) scale(-1, -1) translate(-41.500000, -25.500000) " points="72 0 52 51 11 51 11 0"></polygon> <path class="btn-arr" d="M47.5402667,31.7014887 L51.6749333,27.7356628 C51.6792,27.7315701 51.6845333,27.7290121 51.6888,27.7246636 C51.8376,27.5819307 51.9306667,27.4064562 51.9728,27.222029 C51.9741333,27.2158899 51.9770667,27.2102624 51.9784,27.2041234 C51.9922667,27.1371054 52,27.0688085 52,27.0007674 L52,27.0002558 L52,26.9997442 C52,26.9314473 51.9925333,26.8634061 51.9784,26.7963882 C51.9770667,26.7902491 51.9744,26.7846217 51.9728,26.7784826 C51.9306667,26.5940554 51.8376,26.4185809 51.6888,26.275848 C51.6845333,26.2717553 51.6792,26.2689415 51.6749333,26.2648488 L47.5402667,22.2985113 C47.1253333,21.9004962 46.4466667,21.9004962 46.0317333,22.2985113 C45.6168,22.6965263 45.6168,23.3475214 46.0317333,23.7455364 L48.3578667,25.9768251 L41.0666667,25.9768251 C40.48,25.9768251 40,26.4372538 40,27 C40,27.5627462 40.48,28.0231749 41.0666667,28.0231749 L48.3581333,28.0231749 L46.032,30.2544636 C45.6170667,30.6524786 45.6170667,31.3034737 46.032,31.7014887 C46.4466667,32.0995038 47.1256,32.0995038 47.5402667,31.7014887" fill="#FFFFFF"></path> </g> </g> </g> </g> </svg> </span></div>
						</div>
					</div>
				</div>
			</a>

			<!-- Other Events -->
			<?php if ( ! empty( $remaining_events ) ) : ?>
				<div class="content-item rest-events">
					<span class="delta heading"><?php echo esc_html( $labels['other_events'] ); ?></span>
					
					<?php foreach ( $remaining_events as $event ) : ?>
						<a href="<?php echo esc_url( $event->event_link ); ?>" class="event-wrapper">
							<div class="event__content">
								<div class="event__title">
									<span class="epsilon heading"><?php echo esc_html( $event->event_title ); ?></span>
								</div>
								<div class="event__date">
									<span class="icon-calendar-before meta-size">
										<?php 
											$start = DateTime::createFromFormat( 'Y-m-d', $event->start_date );
											$end = DateTime::createFromFormat( 'Y-m-d', $event->end_date );
											if ( $start && $end ) {
												echo esc_html( $start->format( 'd.m.Y' ) . ' ' . $labels['to'] . ' ' . $end->format( 'd.m.Y' ) );
											}
										?>
									</span>
								</div>
								<div class="event__location">
									<span class="icon-pin-outline-before meta-size"><?php echo esc_html( $event->location ); ?></span>
								</div>
							</div>
							<div class="event__button icon-a-r-before"></div>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
