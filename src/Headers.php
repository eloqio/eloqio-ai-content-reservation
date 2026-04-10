<?php

namespace ELOQIO\AiContentReservation;

defined( 'ABSPATH' ) || exit;

final class Headers {

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function register(): void {
		if ( is_admin() || ! $this->plugin->is_enabled() ) {
			return;
		}

		add_action( 'send_headers', [ $this, 'send_http_header' ] );
		add_action( 'wp_head', [ $this, 'print_meta_tag' ], 1 );
	}

	public function send_http_header(): void {
		foreach ( $this->pairs() as $name => $value ) {
			header( $name . ': ' . $value );
		}
	}

	public function print_meta_tag(): void {
		foreach ( $this->pairs() as $name => $value ) {
			printf(
				'<meta name="%1$s" content="%2$s" />' . "\n",
				esc_attr( $name ),
				esc_attr( $value )
			);
		}
	}

	/**
	 * @return array<string, string>
	 */
	private function pairs(): array {
		$settings = $this->plugin->settings();
		$pairs    = [ 'tdm-reservation' => (string) $settings['tdm_reservation'] ];

		if ( '' !== $settings['tdm_policy'] ) {
			$pairs['tdm-policy'] = str_replace( [ "\r", "\n" ], '', $settings['tdm_policy'] );
		}

		return $pairs;
	}
}
