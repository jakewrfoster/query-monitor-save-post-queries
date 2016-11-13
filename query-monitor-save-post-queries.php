<?php
/**
 * Plugin Name: Query Monitor: save_post Queries
 * Description: Capture and display save_post queries
 * Version: 0.1
 * Author: Jake Foster
 * GitHub Plugin URI: romancandlethoughts/query-monitor-save-post-queries
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
define( 'QMSPQ_VERSION', '0.0.1' );

/**
 * Assets Version number.
 *
 * @var string
 */
define( 'QMSPQ_ASSETS_VERSION', '0.0.1' );

/**
 * Name of the QM Collector
 */
define( 'QMSPQ_COLLECTOR_NAME', 'post_request_queries' );

/**
 * Filesystem path to QMSPQ.
 *
 * @var string
 */
define( 'QMSPQ_PATH', plugin_dir_path( __FILE__ ) );

/**
 * URL for assets.
 */
define( 'QMSPQ_ASSET_URL', plugin_dir_url( __FILE__ ) );

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( esc_html__( 'Denied!', 'query-monitor' ) );
}

/**
 * Load plugin files
 */
function qmspq_plugin_setup() {
	if ( defined( 'QM_DISABLED' ) && QM_DISABLED ) {
		return;
	}

	/**
	 * Register collector, only if Query Monitor is enabled.
	 */
	if ( class_exists( 'QM_Collectors' ) ) {
		require_once( QMSPQ_PATH . 'php/Collector_Save_Post_Queries.php' );
		\QM_Collectors::add( new \QMSPQ\Collector_Save_Post_Queries() );
	}

	/**
	 * Register output. The filter won't run if Query Monitor is not
	 * installed so we don't have to explicity check for it.
	 */
	add_filter( 'qm/outputter/html', function ( array $output, \QM_Collectors $collectors ) {
		require_once( QMSPQ_PATH . 'php/Output_Save_Post_Queries.php' );
		$collector = \QM_Collectors::get( QMSPQ_COLLECTOR_NAME );

		if ( ! empty( $collector ) ) {
			$output[ QMSPQ_COLLECTOR_NAME ] = new \QMSPQ\Output_Save_Post_Queries( $collector );
		}

		return $output;
	}, 101, 2 );

	// Load admin scripts
	add_action( 'admin_enqueue_scripts', function() {
		wp_enqueue_script( 'qmspq-admin-js', QMSPQ_ASSET_URL . '/js/save-post-queries.js', array( 'jquery' ), QMSPQ_ASSETS_VERSION, true );
	} );

}

add_action( 'after_setup_theme', 'qmspq_plugin_setup' );
