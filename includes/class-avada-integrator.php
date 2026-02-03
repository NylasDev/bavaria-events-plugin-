<?php
/**
 * Avada theme integration
 */

class Bavaria_Events_Avada_Integrator {

	/**
	 * Initialize Avada integration
	 */
	public static function init() {
		// Register AJAX endpoint
		add_action( 'wp_ajax_nopriv_bavaria_get_events_table', [ __CLASS__, 'ajax_get_events_table' ] );
		add_action( 'wp_ajax_bavaria_get_events_table', [ __CLASS__, 'ajax_get_events_table' ] );

		// Enqueue frontend script
		add_action( 'wp_footer', [ __CLASS__, 'enqueue_populator_script' ] );
	}

	/**
	 * AJAX endpoint to get events table HTML
	 */
	public static function ajax_get_events_table() {
		// Get language from request
		$language = isset( $_POST['language'] ) ? sanitize_text_field( $_POST['language'] ) : 'en';
		$limit    = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 20;

		// Get events
		$events = Bavaria_Events_DB_Handler::get_upcoming_events( $language, $limit, 'date' );

		if ( empty( $events ) ) {
			wp_send_json_error( 'No events found' );
		}

		// Render table
		ob_start();
		include BAVARIA_EVENTS_PLUGIN_DIR . 'templates/events-table.php';
		$html = ob_get_clean();

		wp_send_json_success( [ 'html' => $html ] );
	}

	/**
	 * Enqueue populator script
	 */
	public static function enqueue_populator_script() {
		$div_id = get_option( 'bavaria_events_avada_div_id' );

		if ( empty( $div_id ) ) {
			return;
		}

		$div_id = sanitize_html_class( $div_id );

		?>
		<script type="text/javascript">
			document.addEventListener('DOMContentLoaded', function() {
				const targetDiv = document.getElementById('<?php echo esc_attr( $div_id ); ?>');
				
				if (!targetDiv) return;

				// Create loading placeholder
				targetDiv.innerHTML = '<div class="bavaria-events-loading">Loading events...</div>';

				// Fetch events via AJAX
				fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: new URLSearchParams({
						action: 'bavaria_get_events_table',
						language: '<?php echo esc_attr( get_option( 'bavaria_events_language', 'en' ) ); ?>',
					}),
				})
				.then(response => response.json())
				.then(data => {
					if (data.success && data.data.html) {
						targetDiv.innerHTML = data.data.html;
					} else {
						targetDiv.innerHTML = '<div class="bavaria-events-error">Failed to load events</div>';
					}
				})
				.catch(error => {
					console.error('Error loading events:', error);
					targetDiv.innerHTML = '<div class="bavaria-events-error">Error loading events</div>';
				});
			});
		</script>
		<?php
	}
}
