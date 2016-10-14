<?php
/**
 * Plugin Name: Query Monitor: POST Request Queries
 * Description: Capture and display POST request queries
 * Version: 0.1
 * Author: Jake Foster
 * GitHub Plugin URI: romancandlethoughts/query-monitor-post-request-queries
 */

/*  This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * Version number.
 *
 * @var string
 */
define( 'QMPRQ_VERSION', '0.0.1' );

/**
 * Assets Version number.
 *
 * @var string
 */
define( 'QMPRQ_ASSETS_VERSION', '0.0.1' );

/**
 * Name of the QM Collector
 */
define( 'QMPRQ_COLLECTOR_NAME', 'post_request_queries' );

/**
 * Filesystem path to QMPRQ.
 *
 * @var string
 */
define( 'QMPRQ_PATH', plugin_dir_path( __FILE__ ) );

/**
 * URL for assets.
 */
define( 'QMPRQ_ASSET_URL', plugin_dir_url( __FILE__ ) );

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( esc_html__( 'Denied!', 'query-monitor' ) );
}

/**
 * Load plugin files
 */
function qmprq_plugin_setup() {
	if ( defined( 'QM_DISABLED' ) && QM_DISABLED ) {
		return;
	}

	/**
	 * Register collector, only if Query Monitor is enabled.
	 */
	if ( class_exists( 'QM_Collectors' ) ) {
		require_once( 'php/Collector_Post_Request_Queries.php' );
		\QM_Collectors::add( new \QMPRQ\Collector_Post_Request_Queries() );
	}

	/**
	 * Register output. The filter won't run if Query Monitor is not
	 * installed so we don't have to explicity check for it.
	 */
	add_filter( 'qm/outputter/html', function ( array $output, \QM_Collectors $collectors ) {
		require_once( 'php/Output_Post_Request_Queries.php' );
		$collector = \QM_Collectors::get( QMPRQ_COLLECTOR_NAME );

		if ( ! empty( $collector ) ) {
			$output[ QMPRQ_COLLECTOR_NAME ] = new \QMPRQ\Output_Post_Request_Queries( $collector );
		}

		return $output;
	}, 101, 2 );
}

add_action( 'after_setup_theme', 'qmprq_plugin_setup' );
