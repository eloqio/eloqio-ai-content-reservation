<?php

namespace ELOQIO\AiContentReservation;

defined( 'ABSPATH' ) || exit;

final class SiteHealth {

	private const TEST_ID   = 'eloqio_acr_endpoint';
	private const REST_NS   = 'eloqio-acr/v1';
	private const REST_PATH = '/site-health-endpoint';

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function register(): void {
		add_filter( 'site_status_tests', [ $this, 'register_test' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_route' ] );
	}

	/**
	 * @param array<string, array<string, array<string, mixed>>> $tests
	 * @return array<string, array<string, array<string, mixed>>>
	 */
	public function register_test( array $tests ): array {
		$tests['async'][ self::TEST_ID ] = [
			'label'     => __( 'TDMRep endpoint reachability', 'ai-content-reservation' ),
			'test'      => rest_url( self::REST_NS . self::REST_PATH ),
			'has_rest'  => true,
			'async'     => true,
			'skip_cron' => true,
		];

		return $tests;
	}

	public function register_rest_route(): void {
		register_rest_route(
			self::REST_NS,
			self::REST_PATH,
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'run_test' ],
				'permission_callback' => static function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function run_test(): array {
		$url = home_url( Endpoint::PATH );

		$base = [
			'test'        => self::TEST_ID,
			'label'       => __( 'TDMRep endpoint reachability', 'ai-content-reservation' ),
			'badge'       => [
				'label' => __( 'AI Content Reservation', 'ai-content-reservation' ),
				'color' => 'blue',
			],
			'description' => '',
			'actions'     => '',
			'status'      => 'good',
		];

		if ( ! $this->plugin->is_enabled() ) {
			$base['status']      = 'recommended';
			$base['description'] = '<p>' . esc_html__( 'The plugin is installed but currently disabled. No TDMRep signal is being sent.', 'ai-content-reservation' ) . '</p>';
			return $base;
		}

		$response = wp_safe_remote_get( $url, [ 'timeout' => 5 ] );

		// Self-signed certs on dev environments: retry without verification when the error
		// is clearly an SSL handshake failure, not a connection problem.
		if ( is_wp_error( $response ) && false !== stripos( $response->get_error_message(), 'ssl' ) ) {
			$response = wp_safe_remote_get( $url, [ 'timeout' => 5, 'sslverify' => false ] );
		}

		if ( is_wp_error( $response ) ) {
			$base['status']      = 'critical';
			$base['description'] = '<p>' . sprintf(
				/* translators: %s: error message. */
				esc_html__( 'The TDMRep endpoint could not be reached: %s', 'ai-content-reservation' ),
				esc_html( $response->get_error_message() )
			) . '</p>';
			return $base;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = (string) wp_remote_retrieve_body( $response );
		$json = json_decode( $body, true );

		if ( 200 !== $code || ! is_array( $json ) ) {
			$base['status']      = 'critical';
			$base['description'] = '<p>' . sprintf(
				/* translators: %d: HTTP status code. */
				esc_html__( 'The TDMRep endpoint returned an unexpected response (HTTP %d).', 'ai-content-reservation' ),
				$code
			) . '</p>';
			return $base;
		}

		$base['description'] = '<p>' . esc_html__( 'The TDMRep endpoint responds with valid JSON. Your AI reservation signal is live.', 'ai-content-reservation' ) . '</p>';

		return $base;
	}
}
