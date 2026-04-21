<?php
/**
 * Plugin Name:       ELOQIO AI Content Reservation
 * Plugin URI:        https://github.com/eloqio/eloqio-ai-content-reservation
 * Description:       Implements the W3C TDM Reservation Protocol to signal AI training opt-out on your WordPress content. Exposes /.well-known/tdmrep.json, a tdm-reservation HTTP header, and a matching HTML meta tag.
 * Version:           1.0.1
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            ELOQIO
 * Author URI:        https://eloq.io
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       eloqio-ai-content-reservation
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'ELOQIO_ACR_VERSION', '1.0.1' );
define( 'ELOQIO_ACR_FILE', __FILE__ );
define( 'ELOQIO_ACR_DIR', plugin_dir_path( __FILE__ ) );

foreach ( [ 'Plugin', 'Endpoint', 'Headers', 'Settings', 'SiteHealth' ] as $eloqio_acr_class ) {
	require_once ELOQIO_ACR_DIR . 'src/' . $eloqio_acr_class . '.php';
}
unset( $eloqio_acr_class );

add_action( 'plugins_loaded', static function () {
	\ELOQIO\AiContentReservation\Plugin::instance()->boot();
} );
