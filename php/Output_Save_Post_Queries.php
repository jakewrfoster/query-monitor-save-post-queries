<?php
/**
 * Output_Save_Post_Queries.php
 *
 * @created     10/14/16 3:09 PM
 * @author      Alley Interactive
 * @package     query-monitor-post-request-queries
 * @description Output HTML POST request HTML
 *
 */
namespace QMSPQ;

class Output_Save_Post_Queries extends \QM_Output_Html_DB_Queries {
	/**
	 * Save post query data.
	 * @var array
	 */
	public $save_post_queries_data;
	public $qmspq_collector;

	public function __construct( $collector ) {
		parent::__construct( $collector );
		$this->qmspq_collector = $this->get_collector();
		$this->save_post_queries_data = $this->get_transient_data();

		// override the collector data with the data processed by late_process_db_object()
		$this->collector->data = $this->save_post_queries_data['save_post_qm_db_data'];

		add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 101 );
		add_filter( 'qm/output/menu_class', array( $this, 'admin_class' ) );
	}

	/**
	 * Are we saving a post object?
	 *
	 * @return bool
	 */
	public function is_doing_save() {
		if ( ! empty( $this->save_post_queries_data['is_doing_save'] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Retrieve transient data set by collector.
	 * @return array
	 */
	public function get_transient_data() {
		$transient_data = [];
		foreach ( $this->qmspq_collector->transient_keys as $transient_key ) {
			$transient_data[ $transient_key ] = get_transient( $this->qmspq_collector->get_transient_key( $transient_key ) );
		}

		$this->qmspq_collector->delete_transients();

		return $transient_data;
	}

	/**
	 * Outputs POST request queries data in the footer
	 */
	public function output() {
		if ( ! $this->is_doing_save() ) {
			return;
		}
		$data = $this->collector->get_data();
		?>
		<div class="qm" id="<?php echo esc_attr( $this->collector->id() ) ?>">
			<table cellspacing="30">
				<thead>
				<tr>
					<th scope="col">
						<?php echo sprintf( esc_html__( 'Total number of Save Post queries: %d', 'query-monitor' ), $this->save_post_queries_data['save_post_queries_count'] ); ?>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php if ( ! empty( $data['expensive'] ) ) : ?>
					<tr>
						<td class="qm-ltr">
							<?php
							echo sprintf( esc_html__( 'Total number of expensive save post queries: %d', 'query-monitor' ), count( $data['expensive'] ) );
							$this->output_expensive_queries( $data['expensive'] );
							?>
						</td>
					</tr>
				<?php endif; ?>
				<?php if ( ! empty( $data['dbs'] ) && is_array( $data['dbs'] ) ) : ?>
					<tr class="all-save-post-queries-row">
						<td class="qm-ltr">
							<div class="all-save-post-queries-wrapper" style="padding: 30px 0;">
								<button><?php esc_html_e( 'Show/hide all save post queries', 'query-monitor' ); ?></button>
								<div class="all-save-post-queries hidden">
									<?php
										foreach ( $data['dbs'] as $name => $db ) {
											$this->output_queries( $name, $db, $data );
										}
									?>
								</div>
							</div>
						</td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * @param array $class
	 *
	 * @return array
	 */
	public function admin_class( array $class ) {
		if ( ! $this->is_doing_save() ) {
			return $class;
		}

		$class[] = 'qm-' . QMSPQ_COLLECTOR_NAME;

		return $class;
	}

	public function admin_menu( array $menu ) {
		if ( ! $this->is_doing_save() ) {
			return $menu;
		}

		$menu[] = $this->menu( array(
			'id'    => 'qm-' . QMSPQ_COLLECTOR_NAME,
			'href'  => '#qm-' . QMSPQ_COLLECTOR_NAME,
			'title' => sprintf( __( 'Save post queries (%s)', 'query-monitor' ), absint( $this->save_post_queries_data['save_post_queries_count'] ) ),
		) );

		return $menu;
	}
}
