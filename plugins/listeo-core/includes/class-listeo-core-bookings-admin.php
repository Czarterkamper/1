<?php

if ( ! defined( 'ABSPATH' )) exit; //  Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Bookings_Admin_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Booking', 'listeo_core' ), // singular name of the listed records
			'plural'   => __( 'Bookings', 'listeo_core' ), // plural name of the listed records
			'ajax'     => false // does this table support ajax?
		] );

	}


	/**
	 * Retrieve bookings data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 * @param int $id
	 *
	 * @return mixed
	 */
	public static function get_bookings( $per_page = 5, $page_number = 1, $id = NULL ) {

		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}bookings_calendar";

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}

		$sql .= ' WHERE `status` IS NOT NULL';
		
		if ( $id ) 
		{
			// for single one
			$sql .= ' AND `ID` = ' . esc_sql( $id );

		}
		else
		{

			// when we taking all
			$sql .= " LIMIT $per_page";
			$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		}


		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}


	/**
	 * Delete a booking record.
	 *
	 * @param int $id booking ID
	 */
	public static function delete_booking( $id ) {

		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}bookings_calendar",
			[ 'ID' => $id ],
			[ '%d' ]
		);

	}

	/**
	 * Update a booking record.
	 *
	 * @param array $values to change
	 * 
	 * @return number $records that was changed
	 */
	public static function update_booking( $values ) {

		global $wpdb;

		return $wpdb->update ( "{$wpdb->prefix}bookings_calendar", $values, array('ID' => $values['ID']) );

	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}bookings_calendar";
		$sql .= ' WHERE `status` IS NOT NULL';

		return $wpdb->get_var( $sql );
	}


	/** Text displayed when no booking data is available */
	public function no_items() {
		_e( 'No bookings avaliable.', 'listeo_core' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		
		
		switch ( $column_name ) {
			case 'ID':
			case 'date_start':
			case 'date_end':
			case 'order_id':
			case 'status':
			case 'type':
			case 'price':
			case 'expiring':
			
			case 'created':
				return $item[ $column_name ];
			case 'listing_id':	
				return get_the_title($item[ $column_name ]);
			case 'owner_id':
			if($item[ $column_name ] != 0){
				$avatar = get_avatar( $item[ $column_name ], 32 );
				$user_data = get_userdata( $item[ $column_name ] );
				return '<a href="' . get_edit_user_link($user_data->ID) . '" >' . $user_data->user_login . '</a>';
			} else {
				return esc_html__('iCal import','listeo_core');
			}
				
			case 'bookings_author':
			if($item[ $column_name ] != 0){
				$avatar = get_avatar( $item[ $column_name ], 32 );
				$user_data = get_userdata( $item[ $column_name ] );
				return '<a href="' . get_edit_user_link($user_data->ID) . '" >' . $user_data->user_login . '</a>';
			} else {
				return esc_html__('iCal import','listeo_core');
			}
			case 'action' :
				$actions = array(
					'edit' => sprintf('<a href="?page=%s&action=%s&id=%s">' . __('Edit', 'listeo_core') . '</a>',$_REQUEST['page'],'edit',$item['ID']),
					'delete' => sprintf('<a href="?page=%s&action=%s&id=%s">' . __('Delete', 'listeo_core') . '</a>',$_REQUEST['page'],'delete',$item['ID']),
				);
			return sprintf('%1$s %2$s', $item['ID'], $this->row_actions($actions) );
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {

		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
		);

	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'sp_delete_booking' );

		$title = '<strong>' . $item['name'] . '</strong>';

		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&booking=%s&_wpnonce=%s">' . __('Delete', 'listeo_core') . '</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );

	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {

		$columns = [
			'cb'      			=> '<input type="checkbox" />',
			'ID'    			=> __( 'ID', 'listeo_core' ),
			'bookings_author' 	=> __( 'Client', 'listeo_core' ),
			'owner_id'    		=> __( 'Owner', 'listeo_core' ),
			'listing_id' 		=> __( 'Listing', 'listeo_core' ),
			'date_start' 		=> __( 'Start date', 'listeo_core' ),
			'date_end' 			=> __( 'End date', 'listeo_core' ),
			'type' 				=> __( 'Type', 'listeo_core' ),
			'created' 			=> __( 'Created', 'listeo_core' ),
			'price' 			=> __( 'Price', 'listeo_core' ),
			'action' 			=> __( 'Action', 'listeo_core' )
		];

		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'ID' 			=> array( 'ID', true ),
			'city' 			=> array( 'city', false ),
			'bookings_author' => array( 'Client', true ),
			'owner_id' 		=> array( 'Owner', true ),
			'listing_id' 	=> array( 'Listing', true ),
			'date_start' 	=> array( 'Start date', true ),
			'date_end' 		=> array( 'End date', true ),
			'type' 			=> array( 'Type', true ),
			'created' 		=> array( 'Created', true ),
			'price' 		=> array( 'Price', true ),
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'per_page', 20 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, // WE have to calculate the total number of items
			'per_page'    => $per_page // WE have to determine how many items to show on a page
		] );

		$this->items = self::get_bookings( $per_page, $current_page );
	}

	public function process_bulk_action() {

		// Edit action
		if ( 'edit' === $this->current_action()) {

			$booking = self::get_bookings( NULL, NULL, $_GET['id']);

			if  ( isset($_POST['ID']) )
			{

				if ( ! self::update_booking( $_POST ) ) wp_die( __( 'Error while updating', 'listeo_core' ) );
				else wp_redirect(  menu_page_url( 'listeo_bookings_manage' ) );
				
			}

			?>
			<form action="" method="POST">
				<div class="wrap">     

				<table class="form-table">
				
				<input type="hidden" name="ID" value="<?php echo $booking[0]['ID'] ?>" /> 

				<tbody>
				
				<tr>
				<th scope="row"><label for="bookings_author"><?php _e( 'User id', 'listeo_core' );  ?></label></th>
				<td><input type="text" name="bookings_author" value="<?php echo $booking[0]['bookings_author'] ?>" class="regular-text"></td>
				</tr>

				<tr>
				<th scope="row"><label for="owner_id"><?php _e( 'Owner id', 'listeo_core' );  ?></label></th>
				<td><input type="text" name="owner_id" value="<?php echo $booking[0]['owner_id'] ?>" class="regular-text"></td>
				</tr>

				<tr>
				<th scope="row"><label for="listing_id"><?php _e( 'Listing id', 'listeo_core' );  ?></label></th>
				<td><input type="text" name="listing_id" value="<?php echo $booking[0]['listing_id'] ?>" class="regular-text"></td>
				</tr>

				<tr>
				<th scope="row"><label for="date_start"><?php _e( 'Date start', 'listeo_core' );  ?></label></th>
				<td><input type="text" name="date_start" value="<?php echo $booking[0]['date_start'] ?>" class="regular-text"></td>
				</tr>

				<tr>
				<th scope="row"><label for="date_end"><?php _e( 'Date end', 'listeo_core' );  ?></label></th>
				<td><input type="text" name="date_end" value="<?php echo $booking[0]['date_end'] ?>" class="regular-text"></td>
				</tr>

				<tr>
				<th scope="row"><label for="order_id"><?php _e( 'Order id', 'listeo_core' );  ?></label></th>
				<td><input type="text" name="order_id" value="<?php echo $booking[0]['order_id'] ?>" class="regular-text"></td>
				</tr>

				<tr>
				<th scope="row"><label for="status"><?php _e( 'Status', 'listeo_core' );  ?></label></th>
				<td><input type="text" name="status" value="<?php echo $booking[0]['status'] ?>" class="regular-text"></td>
				</tr>

				<tr>
				<th scope="row"><label for="created"><?php _e( 'Created date', 'listeo_core' );  ?></label></th>
				<td><input type="text" name="created" value="<?php echo $booking[0]['created'] ?>" class="regular-text"></td>
				</tr>

				<tr>
				<th scope="row"><label for="expiring"><?php _e( 'Expiring date', 'listeo_core' );  ?></label></th>
				<td><input type="text" name="expiring" value="<?php echo $booking[0]['expiring'] ?>" class="regular-text"></td>
				</tr>

				<tr>
				<th scope="row"><label for="price"><?php _e( 'Price', 'listeo_core' );  ?></label></th>
				<td><input type="text" name="price" value="<?php echo $booking[0]['price'] ?>" class="regular-text"></td>
				</tr>

				</tbody></table>
   
			</div>
			<p class="submit"><input type="submit" id="submit" class="button button-primary" value="<?php _e( 'Save', 'listeo_core' );  ?>"></p>
			</form>
			<?php

			exit();

		}

		// Detect when a bulk action is being triggered
		if ( 'delete' === $this->current_action() ) {


				self::delete_booking( absint( $_GET['id'] ) );

		                //  esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		                //  add_query_arg() return the current url
		                // wp_redirect( esc_url_raw(add_query_arg()) );
				// exit;


		}

		//  If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			//  loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_booking( $id );

			}

			//  esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		        //  add_query_arg() return the current url
		        wp_redirect( esc_url_raw(add_query_arg()) );
			exit;
		}
	}

}


class Bookings_Admin_Plugin {

	//  class instance
	static $instance;

	//  booking WP_List_Table object
	public $bookings_obj;

	//  class constructor
	public function __construct() {

		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'plugin_menu' ] );

	}


	public static function set_screen( $status, $option, $value ) {

		return $value;

	}

	public function plugin_menu() {

		$hook = add_menu_page(
			'Manage bookings',
			'Bookings',
			'manage_options',
			'listeo_bookings_manage',
			[ $this, 'plugin_settings_page' ]
		);

		add_action( "load-$hook", [ $this, 'screen_option' ] );

	}


	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page() {
		?>
		<div class="wrap">
			<h2><?php _e('Manage Bookings', 'listeo_core'); ?></h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-3">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->bookings_obj->prepare_items();
								$this->bookings_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
	}

	/**
	 * Screen options
	 */
	public function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => __( 'Bookings per page', 'listeo_core'),
			'default' => 20,
			'option'  => 'per_page'
		];

		add_screen_option( $option, $args );

		$this->bookings_obj = new Bookings_Admin_List();

	}


	/** Singleton instance */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

}


add_action( 'plugins_loaded', function () {

	Bookings_Admin_Plugin::get_instance();

} );