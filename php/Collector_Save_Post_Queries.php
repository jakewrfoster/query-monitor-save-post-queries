<?php
/**
 * Collector_Post_Request_Queries.php
 *
 * @created     10/12/16 3:09 PM
 * @author      Alley Interactive
 * @package     query-monitor-post-request-queries
 * @description Collector for POST Requests
 *
 */

namespace QMSPQ;

class Collector_Save_Post_Queries extends \QM_Collector_DB_Queries {

	public $id = QMSPQ_COLLECTOR_NAME;
	public $post_request_queries = [];
	public $is_doing_save;

	public function __construct() {
		parent::__construct();
		add_action( 'save_post', [ $this, 'collect_save_post_queries' ] );
	}

	/**
	 * Name of Collector
	 * @return string|void
	 */
	public function name() {
		return __( 'save_post Queries', 'query-monitor' );
	}

	/**
	 * Collecting save post queries
	 */
	public function collect_save_post_queries() {
		global $wpdb;
		set_transient( 'qmspq_is_doing_save', true, 1 * MINUTE_IN_SECONDS );
		set_transient( 'qmspq_save_post_queries', $wpdb->queries, 1 * MINUTE_IN_SECONDS );
	}

	public function process() {
		if ( true != get_transient( 'qmspq_is_doing_save' ) ) {
			return;
		}

		$this->data['save_post_queries'] = get_transient( 'qmspq_save_post_queries' );

		if ( empty( $this->data['save_post_queries'] ) ) {
			return;
		}

		$this->data['save_post_queries_count'] = count( $this->data['save_post_queries'] );
	}
}
