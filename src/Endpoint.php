<?php
// Served directly from a hook rather than a physical file to avoid conflicts
// with Let's Encrypt and read-only hosting.

namespace ELOQIO\AiContentReservation;

defined( 'ABSPATH' ) || exit;

final class Endpoint {

	public const PATH = '/.well-known/tdmrep.json';

	private const CONTENT_TYPE = 'application/tdmrep+json; charset=utf-8';

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function register(): void {
		add_action( 'init', [ $this, 'maybe_serve' ], 0 );
	}

	public function maybe_serve(): void {
		$request_uri = isset( $_SERVER['REQUEST_URI'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) )
			: '';

		$path = wp_parse_url( $request_uri, PHP_URL_PATH );

		if ( self::PATH !== $path ) {
			return;
		}

		if ( ! $this->plugin->is_enabled() ) {
			return;
		}

		header( 'Content-Type: ' . self::CONTENT_TYPE );
		header( 'Cache-Control: public, max-age=3600' );
		header( 'Access-Control-Allow-Origin: *' );

		echo wp_json_encode( $this->build_payload(), JSON_PRETTY_PRINT );
		exit;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function build_payload(): array {
		$settings = $this->plugin->settings();

		$entry = [
			'location'        => '/',
			'tdm-reservation' => $settings['tdm_reservation'],
		];

		if ( '' !== $settings['tdm_policy'] ) {
			$entry['tdm-policy'] = $settings['tdm_policy'];
		}

		return [ $entry ];
	}
}
