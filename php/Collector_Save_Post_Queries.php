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

	/**
	 * Replicating this as public accessible in
	 * order to overwrite the $data property in
	 * the Output class.
	 * @var array
	 */
	public $data = array(
		'types'           => array(),
		'component_times' => array(),
	);

	/**
	 * Array of transient keys.
	 * @var array
	 */
	public $transient_keys = [
		'is_doing_save',
		'save_post_qm_db_data',
		'save_post_queries',
		'save_post_queries_count',
	];

	/**
	 * Transient exp time in seconds.
	 * Default 1 minute (60s).
	 * @var int
	 */
	public $transient_expiration;

	public function __construct() {
		$this->set_transient_exp_time();
		add_action( 'save_post', [ $this, 'is_doing_save' ], 11 );
		add_action( 'save_post', [ $this, 'qmspq_processor' ], 12 );

		parent::__construct();
	}

	/**
	 * Name of Collector
	 * @return string|void
	 */
	public function name() {
		return __( 'save_post Queries', 'query-monitor' );
	}

	/**
	 * Get the standardized transient key for caching filtered post content.
	 *
	 * @param string $identifier
	 * @return string transient key
	 */
	public function get_transient_key( $identifier ) {
		return "qmspq_{$identifier}";
	}

	/**
	 * Delete transients set by the collector.
	 */
	public function delete_transients() {
		foreach ( $this->transient_keys as $transient_key ) {
			delete_transient( $this->get_transient_key( $transient_key ) );
		}
	}

	/**
	 * Store filtered content to the cache for a post.
	 * @param string $identifier
	 * @param string $content
	 * @return void
	 */
	public function set_transient( $identifier, $content ) {
		set_transient( $this->get_transient_key( $identifier ), $content, $this->transient_expiration );
	}

	public function set_transient_exp_time() {
		$this->transient_expiration = apply_filters( 'qmspq_transient_exp_time', 120 );
	}

	/**
	 * Set transients on post save.
	 */
	public function is_doing_save() {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		$this->set_transient( 'is_doing_save', true );
		return true;
	}

	/**
	 * Since this is running after QM thinks it should run,
	 * this method needs to be skipped in the parent class.
	 */
	public function process() {
		return;
	}

	public function late_process_db_object() {
		global $wpdb;

		$this->data['total_qs']   = 0;
		$this->data['total_time'] = 0;
		$this->data['errors']     = array();

		$this->db_objects = array( '$wpdb' => $wpdb );

		foreach ( $this->db_objects as $name => $db ) {
			if ( is_a( $db, 'wpdb' ) ) {
				$this->process_db_object( $name, $db );
			} elseif ( is_array( $this->db_objects ) ) {
				unset( $this->db_objects[ $name ] );
			}
		}
		/*
		 * Save processed DB data, since the `process()` method
		 * will run again after the redirect hook and overwrite
		 * all of our save post data that was recorded in
		 * late_processes_queries().
		 */
		$this->set_transient( 'save_post_qm_db_data', $this->get_data() );
	}

	public function qmspq_processor( $location ) {
		if ( ! $this->is_doing_save() ) {
			return $location;
		}

		global $wpdb;
		// set transients for output consumption
		$this->set_transient( 'save_post_queries', $wpdb->queries );
		$this->set_transient( 'save_post_queries_count', count( $wpdb->queries ) );

		$this->late_process_db_object();

		return $location;
	}
}
