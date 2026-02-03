<?php
/**
 * Admin menu and settings page for Bavaria Events
 */

class Bavaria_Events_Admin_Menu {

	/**
	 * Initialize admin menu
	 */
	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'add_admin_menu' ] );
		add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
		add_action( 'wp_ajax_bavaria_manual_crawl', [ __CLASS__, 'ajax_manual_crawl' ] );
	}

	/**
	 * Add admin menu
	 */
	public static function add_admin_menu() {
		add_menu_page(
			'Bavaria Events',
			'Bavaria Events',
			'manage_options',
			'bavaria-events',
			[ __CLASS__, 'render_settings_page' ],
			'dashicons-calendar',
			25
		);
	}

	/**
	 * Register settings
	 */
	public static function register_settings() {
		register_setting( 'bavaria-events-settings', 'bavaria_events_google_api_key', [
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => false,
		] );

		register_setting( 'bavaria-events-settings', 'bavaria_events_enable_translation', [
			'sanitize_callback' => function( $value ) {
				return (bool) $value;
			},
			'show_in_rest'      => false,
		] );

		register_setting( 'bavaria-events-settings', 'bavaria_events_language', [
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'en',
			'show_in_rest'      => false,
		] );

		register_setting( 'bavaria-events-settings', 'bavaria_events_avada_div_id', [
			'sanitize_callback' => 'sanitize_html_class',
			'show_in_rest'      => false,
		] );
	}

	/**
	 * Render settings page
	 */
	public static function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have permission to access this page.' );
		}

		$api_key              = get_option( 'bavaria_events_google_api_key' );
		$enable_translation   = (bool) get_option( 'bavaria_events_enable_translation' );
		$language             = get_option( 'bavaria_events_language', 'en' );
		$avada_div_id         = get_option( 'bavaria_events_avada_div_id' );
		$last_crawl           = Bavaria_Events_DB_Handler::get_last_crawl_log();
		$event_count          = Bavaria_Events_DB_Handler::get_event_count();
		$recent_logs          = Bavaria_Events_DB_Handler::get_recent_crawl_logs( 10 );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Bavaria Events Settings', 'bavaria-events' ); ?></h1>

			<?php if ( isset( $_GET['settings-updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings saved successfully.', 'bavaria-events' ); ?></p>
				</div>
			<?php endif; ?>

			<div class="metabox-holder">
				<!-- Status Box -->
				<div class="postbox">
					<h2 class="hndle"><span><?php esc_html_e( 'Status', 'bavaria-events' ); ?></span></h2>
					<div class="inside">
						<p>
							<strong><?php esc_html_e( 'Events Stored:', 'bavaria-events' ); ?></strong>
							<?php echo intval( $event_count ); ?> events
						</p>

						<?php if ( $last_crawl ) : ?>
							<p>
								<strong><?php esc_html_e( 'Last Crawl:', 'bavaria-events' ); ?></strong>
								<?php echo esc_html( $last_crawl->crawl_date ); ?>
								<span class="<?php echo 'success' === $last_crawl->status ? 'dashicons dashicons-yes' : 'dashicons dashicons-no'; ?>"></span>
								<?php echo esc_html( ucfirst( $last_crawl->status ) ); ?>
							</p>
							<?php if ( ! empty( $last_crawl->error_message ) ) : ?>
								<p style="color: red;">
									<strong><?php esc_html_e( 'Error:', 'bavaria-events' ); ?></strong>
									<?php echo esc_html( $last_crawl->error_message ); ?>
								</p>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>

				<!-- Manual Refresh -->
				<div class="postbox">
					<h2 class="hndle"><span><?php esc_html_e( 'Manual Refresh', 'bavaria-events' ); ?></span></h2>
					<div class="inside">
						<button id="bavaria-manual-crawl-btn" class="button button-primary">
							<?php esc_html_e( 'Refresh Events Now', 'bavaria-events' ); ?>
						</button>
						<div id="bavaria-crawl-status" style="margin-top: 10px;"></div>
					</div>
				</div>

				<!-- Settings Form -->
				<div class="postbox">
					<h2 class="hndle"><span><?php esc_html_e( 'Settings', 'bavaria-events' ); ?></span></h2>
					<div class="inside">
						<form method="post" action="options.php">
							<?php settings_fields( 'bavaria-events-settings' ); ?>

							<table class="form-table">
								<tr>
									<th scope="row">
										<label for="bavaria_events_google_api_key">
											<?php esc_html_e( 'Google Translate API Key', 'bavaria-events' ); ?>
										</label>
									</th>
									<td>
										<input 
											type="password" 
											id="bavaria_events_google_api_key" 
											name="bavaria_events_google_api_key" 
											value="<?php echo esc_attr( $api_key ); ?>" 
											class="regular-text"
											placeholder="AIzaSy..."
										/>
										<p class="description">
											<?php esc_html_e( 'Get your API key from Google Cloud Console (Console → Credentials → Create API Key)', 'bavaria-events' ); ?>
										</p>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="bavaria_events_enable_translation">
											<?php esc_html_e( 'Enable Translation', 'bavaria-events' ); ?>
										</label>
									</th>
									<td>
										<input 
											type="checkbox" 
											id="bavaria_events_enable_translation" 
											name="bavaria_events_enable_translation" 
											value="1"
											<?php checked( $enable_translation, true ); ?>
										/>
										<p class="description">
											<?php esc_html_e( 'Enable English to Romanian translation', 'bavaria-events' ); ?>
										</p>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="bavaria_events_language">
											<?php esc_html_e( 'Display Language', 'bavaria-events' ); ?>
										</label>
									</th>
									<td>
										<select id="bavaria_events_language" name="bavaria_events_language">
											<option value="en" <?php selected( $language, 'en' ); ?>>
												<?php esc_html_e( 'English', 'bavaria-events' ); ?>
											</option>
											<option value="ro" <?php selected( $language, 'ro' ); ?>>
												<?php esc_html_e( 'Romanian', 'bavaria-events' ); ?>
											</option>
										</select>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="bavaria_events_avada_div_id">
											<?php esc_html_e( 'Avada Div ID (auto-populate)', 'bavaria-events' ); ?>
										</label>
									</th>
									<td>
										<input 
											type="text" 
											id="bavaria_events_avada_div_id" 
											name="bavaria_events_avada_div_id" 
											value="<?php echo esc_attr( $avada_div_id ); ?>" 
											class="regular-text"
											placeholder="bavaria-events-table"
										/>
										<p class="description">
											<?php esc_html_e( 'Enter the HTML element ID where events should be auto-populated (e.g., bavaria-events-table)', 'bavaria-events' ); ?>
										</p>
									</td>
								</tr>
							</table>

							<?php submit_button(); ?>
						</form>
					</div>
				</div>

				<!-- Recent Logs -->
				<div class="postbox">
					<h2 class="hndle"><span><?php esc_html_e( 'Recent Crawl Logs', 'bavaria-events' ); ?></span></h2>
					<div class="inside">
						<table class="widefat">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Date', 'bavaria-events' ); ?></th>
									<th><?php esc_html_e( 'Status', 'bavaria-events' ); ?></th>
									<th><?php esc_html_e( 'Found', 'bavaria-events' ); ?></th>
									<th><?php esc_html_e( 'Updated', 'bavaria-events' ); ?></th>
									<th><?php esc_html_e( 'Duration', 'bavaria-events' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $recent_logs as $log ) : ?>
									<tr>
										<td><?php echo esc_html( $log->crawl_date ); ?></td>
										<td>
											<span class="dashicons dashicons-<?php echo 'success' === $log->status ? 'yes' : 'no'; ?>"></span>
											<?php echo esc_html( ucfirst( $log->status ) ); ?>
										</td>
										<td><?php echo intval( $log->events_found ); ?></td>
										<td><?php echo intval( $log->events_updated ); ?></td>
										<td><?php echo number_format( floatval( $log->duration_seconds ), 2 ); ?>s</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

		<script type="text/javascript">
			document.getElementById('bavaria-manual-crawl-btn').addEventListener('click', function() {
				const btn = this;
				const status = document.getElementById('bavaria-crawl-status');
				btn.disabled = true;
				status.innerHTML = '<p><?php esc_html_e( 'Running crawl...', 'bavaria-events' ); ?></p>';

				fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: new URLSearchParams({
						action: 'bavaria_manual_crawl',
						nonce: '<?php echo esc_attr( wp_create_nonce( 'bavaria_manual_crawl' ) ); ?>',
					}),
				})
				.then(response => response.json())
				.then(data => {
					btn.disabled = false;
					if (data.success) {
						status.innerHTML = '<div class="notice notice-success"><p>' + data.data.message + '</p></div>';
						setTimeout(() => location.reload(), 2000);
					} else {
						status.innerHTML = '<div class="notice notice-error"><p>Error: ' + data.data + '</p></div>';
					}
				})
				.catch(error => {
					btn.disabled = false;
					status.innerHTML = '<div class="notice notice-error"><p>Error: ' + error.message + '</p></div>';
				});
			});
		</script>
		<?php
	}

	/**
	 * AJAX handler for manual crawl
	 */
	public static function ajax_manual_crawl() {
		check_ajax_referer( 'bavaria_manual_crawl', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$result = Bavaria_Events_Crawler::crawl_events();

		if ( $result['success'] ) {
			wp_send_json_success( [
				'message' => $result['message'],
				'events'  => $result['events'],
				'updated' => $result['updated'],
			] );
		} else {
			wp_send_json_error( $result['message'] );
		}
	}
}
