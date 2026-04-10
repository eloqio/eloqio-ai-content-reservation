<?php

namespace ELOQIO\AiContentReservation;

defined( 'ABSPATH' ) || exit;

final class Settings {

	private const PAGE_SLUG    = 'ai-content-reservation';
	private const OPTION_GROUP = 'eloqio_acr';
	private const SECTION_MAIN = 'eloqio_acr_main';

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function register(): void {
		add_action( 'admin_menu', [ $this, 'register_page' ] );
		add_action( 'admin_init', [ $this, 'register_fields' ] );
		add_filter(
			'plugin_action_links_' . plugin_basename( ELOQIO_ACR_FILE ),
			[ $this, 'action_links' ]
		);
	}

	public function register_page(): void {
		add_options_page(
			esc_html__( 'AI Content Reservation', 'ai-content-reservation' ),
			esc_html__( 'AI Content Reservation', 'ai-content-reservation' ),
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render_page' ]
		);
	}

	public function register_fields(): void {
		register_setting(
			self::OPTION_GROUP,
			Plugin::OPTION_KEY,
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize' ],
				'default'           => Plugin::DEFAULTS,
			]
		);

		add_settings_section(
			self::SECTION_MAIN,
			esc_html__( 'Reservation settings', 'ai-content-reservation' ),
			[ $this, 'render_section_intro' ],
			self::PAGE_SLUG
		);

		add_settings_field(
			'enabled',
			esc_html__( 'Enable protocol', 'ai-content-reservation' ),
			[ $this, 'render_field_enabled' ],
			self::PAGE_SLUG,
			self::SECTION_MAIN
		);

		add_settings_field(
			'tdm_reservation',
			esc_html__( 'Reservation value', 'ai-content-reservation' ),
			[ $this, 'render_field_reservation' ],
			self::PAGE_SLUG,
			self::SECTION_MAIN
		);

		add_settings_field(
			'tdm_policy',
			esc_html__( 'Policy URL (optional)', 'ai-content-reservation' ),
			[ $this, 'render_field_policy' ],
			self::PAGE_SLUG,
			self::SECTION_MAIN
		);
	}

	/**
	 * @param mixed $input
	 * @return array{enabled:bool, tdm_reservation:int, tdm_policy:string}
	 */
	public function sanitize( $input ): array {
		$input = is_array( $input ) ? $input : [];

		return [
			'enabled'         => ! empty( $input['enabled'] ),
			'tdm_reservation' => (int) ( $input['tdm_reservation'] ?? 1 ),
			'tdm_policy'      => isset( $input['tdm_policy'] ) ? esc_url_raw( wp_unslash( (string) $input['tdm_policy'] ) ) : '',
		];
	}

	public function render_section_intro(): void {
		echo '<p>' . esc_html__(
			'Configure how this site signals its Text & Data Mining reservation to AI crawlers, per the W3C TDM Reservation Protocol.',
			'ai-content-reservation'
		) . '</p>';
	}

	public function render_field_enabled(): void {
		$settings = $this->plugin->settings();
		printf(
			'<label><input type="checkbox" name="%1$s[enabled]" value="1" %2$s /> %3$s</label>',
			esc_attr( Plugin::OPTION_KEY ),
			checked( $settings['enabled'], true, false ),
			esc_html__( 'Serve the endpoint, header and meta tag', 'ai-content-reservation' )
		);
	}

	public function render_field_reservation(): void {
		$settings = $this->plugin->settings();
		$options  = [
			1 => __( '1 — Rights reserved (opt-out)', 'ai-content-reservation' ),
			0 => __( '0 — Rights not reserved (opt-in)', 'ai-content-reservation' ),
		];

		echo '<select name="' . esc_attr( Plugin::OPTION_KEY ) . '[tdm_reservation]">';
		foreach ( $options as $value => $label ) {
			printf(
				'<option value="%1$d" %2$s>%3$s</option>',
				(int) $value,
				selected( $settings['tdm_reservation'], $value, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
	}

	public function render_field_policy(): void {
		$settings = $this->plugin->settings();
		printf(
			'<input type="url" class="regular-text" name="%1$s[tdm_policy]" value="%2$s" placeholder="https://example.com/tdm-policy" />',
			esc_attr( Plugin::OPTION_KEY ),
			esc_attr( $settings['tdm_policy'] )
		);
		echo '<p class="description">' . esc_html__(
			'Optional URL of a human-readable policy document describing your reservation terms.',
			'ai-content-reservation'
		) . '</p>';
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$endpoint_url = home_url( Endpoint::PATH );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'AI Content Reservation (TDMRep)', 'ai-content-reservation' ); ?></h1>

			<form method="post" action="options.php">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>

			<h2><?php esc_html_e( 'Endpoint', 'ai-content-reservation' ); ?></h2>
			<p>
				<?php esc_html_e( 'Your TDMRep endpoint is served at:', 'ai-content-reservation' ); ?>
				<br />
				<code><a href="<?php echo esc_url( $endpoint_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $endpoint_url ); ?></a></code>
			</p>
			<p class="description">
				<?php esc_html_e( 'Open the URL above in a new tab to verify that it returns valid JSON. The Site Health screen also runs an automated check.', 'ai-content-reservation' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * @param array<int, string> $links
	 * @return array<int, string>
	 */
	public function action_links( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=' . self::PAGE_SLUG ) ),
			esc_html__( 'Settings', 'ai-content-reservation' )
		);
		array_unshift( $links, $settings_link );

		return $links;
	}
}
