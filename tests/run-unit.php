<?php
/**
 * Tests unitaires standalone (PHP CLI, zéro dépendance) — cohérent avec la
 * philosophie du plugin (pas de PHPUnit, pas de wp-env).
 *
 * Couvre l'exposition du filtre `eloqio_acr_settings` : un thème ou un plugin
 * (ex. choreo-engine, en SSOT de la politique IA) doit pouvoir piloter la
 * réservation TDMRep sans que le plugin connaisse quoi que ce soit de lui.
 *
 * Lancer : php tests/run-unit.php
 *
 * @package eloqio-ai-content-reservation
 */

// --- Stubs WordPress minimaux ------------------------------------------------

define( 'ABSPATH', __DIR__ );

$GLOBALS['__filters'] = array();

function add_filter( string $hook, callable $cb ): void {
	$GLOBALS['__filters'][ $hook ][] = $cb;
}

function apply_filters( string $hook, $value ) {
	foreach ( $GLOBALS['__filters'][ $hook ] ?? array() as $cb ) {
		$value = $cb( $value );
	}
	return $value;
}

function get_option( string $key, $default = false ) {
	return $default;
}

function wp_parse_args( $args, array $defaults ): array {
	return array_merge( $defaults, is_array( $args ) ? $args : array() );
}

// --- Harness -----------------------------------------------------------------

$failures = 0;

function check( bool $cond, string $label ): void {
	global $failures;
	if ( $cond ) {
		echo "  ok   {$label}\n";
		return;
	}
	$failures++;
	echo "  FAIL {$label}\n";
}

require __DIR__ . '/../src/Plugin.php';

use ELOQIO\AiContentReservation\Plugin;

// --- Cas : le filtre eloqio_acr_settings pilote la réservation ---------------

// Défaut DEFAULTS = tdm_reservation 1. Un consommateur l'abaisse à 0 (opt-in).
add_filter(
	'eloqio_acr_settings',
	static function ( array $settings ): array {
		$settings['tdm_reservation'] = 0;
		return $settings;
	}
);

$settings = Plugin::instance()->settings();

check(
	0 === $settings['tdm_reservation'],
	'le filtre eloqio_acr_settings est appliqué dans Plugin::settings()'
);

// --- Bilan -------------------------------------------------------------------

echo "\n" . ( 0 === $failures ? "OK\n" : "{$failures} FAILURE(S)\n" );
exit( $failures > 0 ? 1 : 0 );
