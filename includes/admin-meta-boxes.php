<?php
/**
 * CartPipe Meta Boxes
 *
 * Adds Meta Boxes for QB data from CartPipe
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class CP_Meta_Boxes {

	private static $meta_box_errors = array();
	private static $meta_box_messages = array();
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 1, 2 );
		
		/**
		 * Save Order Meta Boxes
		 *
		 * In order:
		 * 		Save the order items
		 * 		Save the order totals
		 * 		Save the order downloads
		 * 		Save order data - also updates status and sends out admin emails if needed. Last to show latest data.
		 * 		Save actions - sends out other emails. Last to show latest data.
		 */
		add_action( 'cp_process_shop_order_meta', 'CP_QBO_Order_Meta_Box::save', 10, 2 );

		// Save Product Meta Boxes
		//add_action( 'cp_process_product_meta', 'CP_QBO_Dowmload_Meta_Box::save', 10, 2 );

		
		// Error handling (for showing errors from meta boxes on next page load)
		add_action( 'admin_notices', array( $this, 'output_errors' ) );
		add_action( 'shutdown', array( $this, 'save_errors' ) );
		add_action( 'cp_messages', array( $this, 'output_messages' ) );
		add_action( 'shutdown', array( $this, 'save_messages' ) );
	}

	/**
	 * Add an error message
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$meta_box_errors[] = $text;
	}

	/**
	 * Save errors to an option
	 */
	public function save_errors() {
		update_option( 'cp_meta_box_errors', self::$meta_box_errors );
	}
	
	/**
	 * Show any stored error messages.
	 */
	public function output_errors() {
		$errors = maybe_unserialize( get_option( 'cp_meta_box_errors' ) );

		if ( ! empty( $errors ) ) {

			echo '<div id="cp_errors" class="error fade">';
			foreach ( $errors as $error ) {
				echo '<p>' . esc_html( $error ) . '</p>';
			}
			echo '</div>';

			// Clear
			//delete_option( 'cp_meta_box_errors' );
		}
	}
	public static function add_message( $text ) {
		
		self::$meta_box_messages[] = $text;
	}

	
	/**
	 * Show any stored error messages.
	 */
	public function output_messages() {
		$errors = maybe_unserialize( get_option( 'cp_meta_box_messages' ) );
		
		if ( ! empty( $errors ) ) {

			echo '<div id="cp_messages" class="message fade">';
			foreach ( $errors as $error ) {
				echo '<p>' . esc_html( $error ) . '</p>';
			}
			echo '</div>';

			// Clear
			//delete_option( 'cp_meta_box_errors' );
		}
	}
	public function save_messages() {
		
		update_option( 'cp_meta_box_messages', self::$meta_box_messages );
	}

	/**
	 * Add WC Meta boxes
	 */
	public function add_meta_boxes() {
		// Products
		add_meta_box( 'qbo-product-data', __( 'Cartpipe', 'cartpipe' ), 'CP_QBO_Download_Meta_Box::output', 'download', 'normal' );
		add_meta_box( 'qbo-order-data', __( 'Cartpipe', 'cartpipe' ), 'CP_QBO_Order_Meta_Box::output', 'shop_order', 'normal', 'high' );
		add_meta_box( 'qbo-fallout-data', __( 'Fallout Info', 'cartpipe' ), 'CP_QBO_Fallout_Meta_Box::output', 'cp_fallout', 'normal', 'high' );
	}

	

	/**
	 * Check if we're saving, the trigger an action based on the post type
	 *
	 * @param  int $post_id
	 * @param  object $post
	 */
	public function save_meta_boxes( $post_id, $post ) {
		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce
		if ( empty( $_POST['cp_meta_nonce'] ) || ! wp_verify_nonce( $_POST['cp_meta_nonce'], 'cp_save_data' ) ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}

		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check the post type
		if ( in_array( $post->post_type, array( 'product', 'shop_coupon', 'shop_order' ) ) ) {
			do_action( 'cp_process_' . $post->post_type . '_meta', $post_id, $post );
		}
	}

}

new CP_Meta_Boxes();
