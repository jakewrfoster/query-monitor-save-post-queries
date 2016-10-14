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

namespace QMPRQ;

class Collector_Post_Request_Queries extends \QM_Collector_DB_Queries {

	public $id = QMPRQ_COLLECTOR_NAME;
	public $post_request_queries = [];

	/**
	 * Name of Collector
	 * @return string|void
	 */
	public function name() {
		return __( 'POST Request Queries', 'query-monitor' );
	}

	public function process() {
		$this->data['post_request_queries_count'] = count( $this->post_request_queries );
	}
}
