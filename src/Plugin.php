<?php

namespace ELOQIO\AiContentReservation;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	public const OPTION_KEY = 'eloqio_acr_settings';

	public const DEFAULTS = [
		'enabled'         => true,
		'tdm_reservation' => 1,
		'tdm_policy'      => '',
	];

	private static ?Plugin $instance = null;

	/**
	 * @var array{enabled:bool, tdm_reservation:int, tdm_policy:string}|null
	 */
	private ?array $cache = null;

	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {}

	public function boot(): void {
		( new Endpoint( $this ) )->register();
		( new Headers( $this ) )->register();

		if ( is_admin() ) {
			( new Settings( $this ) )->register();
		}

		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			( new SiteHealth( $this ) )->register();
		}
	}

	/**
	 * @return array{enabled:bool, tdm_reservation:int, tdm_policy:string}
	 */
	public function settings(): array {
		if ( null !== $this->cache ) {
			return $this->cache;
		}

		$stored = get_option( self::OPTION_KEY, [] );
		$merged = wp_parse_args( is_array( $stored ) ? $stored : [], self::DEFAULTS );

		$this->cache = [
			'enabled'         => (bool) $merged['enabled'],
			'tdm_reservation' => 0 === (int) $merged['tdm_reservation'] ? 0 : 1,
			'tdm_policy'      => (string) $merged['tdm_policy'],
		];

		return $this->cache;
	}

	public function is_enabled(): bool {
		return $this->settings()['enabled'];
	}
}
