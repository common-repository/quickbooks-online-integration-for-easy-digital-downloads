<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * CP AJAX
 *
 * AJAX Event Handler
 *
 * @class 		CP_AJAX
  */
class CP_AJAX {
	public static function init() {

		// woocommerce_EVENT => nopriv
		$ajax_events = array(
			'cp_transfer_order' 		=> false, 
			'cp_transfer_single_order'	=> false,
			'cp_sync_product'			=> false,
			'cp_sync_start'				=> false,  
			'cp_update_product'			=> false,
			'cp_transfer_order_qbo'		=> false,
			'cp_refresh_taxrates'		=> false,
			'cp_refresh_taxcodes'		=> false,
			'cp_refresh_payments'		=> false,
			'cp_refresh_accounts'		=> false,
			'cp_release_queue'			=> false,
			'cp_resend_order_qbo'		=> false,
			'cp_break_sync'				=> false,
			'cp_add_message'			=> false,
			'cp_hide_messages'			=> false,
			'cp_deactivate_license'		=> false,
			'cp_activate_license'		=> false,
			'cp_recheck_license'		=> false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}
	public static function cp_hide_messages(){
		check_ajax_referer( 'cp-options-nonce', 'security' );
		update_option('cp_hide_messages', 'yes');
		die();
	}
	public static function cp_activate_license(){
		check_ajax_referer( 'cp-options-nonce', 'security' );
		
		if( CP()->qbo->license_info->status=="valid" || CP()->qbo->license_info->status == 'site_inactive' || CP()->qbo->license_info->status == 'inactive' ):
			$client 				= CP()->client;
			$license_data 			= $client->cp_activate_license( CP()->qbo->license, get_home_url() );
			$qbo 					= maybe_unserialize( get_option('qbo') );
			$qbo['license_info']	= $license_data;
			update_option('qbo', $qbo);
			CP_Messages::add_message('Your license for Cartpipe.com has been activated.');
			
		endif;
		echo ( json_encode(CP()->qbo->license_info ) );
		die();
	}
	public static function cp_recheck_license(){
		check_ajax_referer( 'cp-options-nonce', 'security' );
		$client 				= CP()->client;
		$license_data 			= $client->check_service( CP()->qbo->license, get_home_url() );
		$qbo 					= maybe_unserialize( get_option('qbo') );
		$qbo['license_info']	= $license_data;
		update_option('qbo', $qbo);
		die();
	}
	public static function cp_deactivate_license(){
		check_ajax_referer( 'cp-options-nonce', 'security' );
		if(CP()->qbo->license_info->status=="valid" ||  CP()->qbo->license_info->status=="site_inactive" || CP()->qbo->license_info->status == 'inactive' ):
			$client 				= CP()->client;
			$license_data 			= $client->cp_deactivate_license( CP()->qbo->license, get_home_url() );
			$qbo 					= maybe_unserialize( get_option('qbo') );
			$qbo['license_info']	= $license_data;
			update_option('qbo', $qbo);
			CP_Messages::add_message('Your license for Cartpipe.com has been deactivated.');
		endif;
			echo ( json_encode($license_data ) );
		die();
	}
	public static function cp_add_message(){
		//check_ajax_referer( 'transfer-order-qbo', 'security' );
		$message   = $_POST['message'];
		CP_Messages::add_message($message);
		echo $message;
		die();
	}
	public static function cp_refresh_payments(){
		check_ajax_referer( 'cp-options-nonce', 'security' );
		if(CP()->qbo->license_info->status=="valid" ):
			delete_option('qbo_payment_methods');
			$payment_methods = CP()->client->qbo_get_payment_methods( CP()->qbo->license  );
			update_option('qbo_payment_methods', $payment_methods);
		endif;
		die();
	}
	public static function cp_refresh_accounts(){
		check_ajax_referer( 'cp-options-nonce', 'security' );
		if(CP()->qbo->license_info->status=="valid" ):
			delete_option('qbo_accounts');
			$accounts = CP()->client->qbo_get_accounts( CP()->qbo->license  );
			update_option('qbo_accounts', $accounts);
		endif;
		die();
	}
	public static function cp_refresh_taxcodes(){
		check_ajax_referer( 'cp-options-nonce', 'security' );
		if(CP()->qbo->license_info->status=="valid"):
			$codes = CP()->client->qbo_get_sales_tax_codes( CP()->qbo->license  );
			update_option('qbo_sales_tax_codes', $codes);
		endif;
		die();
	}
	public static function cp_refresh_taxrates(){
		check_ajax_referer( 'cp-options-nonce', 'security' );
		if(CP()->qbo->license_info->status=="valid"):
			$info = CP()->client->qbo_get_sales_tax_info( CP()->qbo->license );
			update_option('qbo_sales_tax_info', $info);
		endif;
		die();
	}
	public static function cp_transfer_single_order(){
		check_ajax_referer( 'transfer-order', 'security' );
		if(CP()->qbo->license_info->status=="valid"):
			$post_id   	= (int) $_POST['post_id'];
			if($post_id > 0){
				$data 		= array();
				$payment 				=  edd_get_payments(array('output'=>'payments', 'p'=>$post_id) );
				$data		= array(
					'billing_first_name'	=> $payment[0]->user_info['first_name'],
					'billing_last_name'		=> $payment[0]->user_info['last_name'],
					'billing_address_1'		=> $payment[0]->user_info['address']['line1'],
					'billing_address_2'		=> $payment[0]->user_info['address']['line2'],
					'billing_city'			=> $payment[0]->user_info['address']['city'],			
					'billing_state'			=> $payment[0]->user_info['address']['state'],
					'billing_postcode'		=> $payment[0]->user_info['address']['zip'],
					'billing_email'			=> $order->billing_email,
				);
				$customer_id 	= get_post_meta( $post_id, '_qbo_cust_id', true );
				if( !$customer_id ){
					$qbo  = CP()->client->qbo_add_customer( $post_id, $data, CP()->qbo->license );
					if($qbo->qbo_cust_id){
						update_post_meta( $post_id, '_qbo_cust_id', $qbo->qbo_cust_id );
						CP()->cp_qbo_send_order( $post_id, CP()->qbo->order_trigger, '' );
					}
				}else{
					CP()->cp_qbo_send_order( $post_id, CP()->qbo->order_trigger, '' );
				}
				$notices	= get_option('cp_admin_notices', array());
				$notices[]	= 'Payment #' . $post_id . ' has been queued to send to QuickBooks.';
				update_post_meta( $post_id, '_cp_is_queued', 'yes');
				update_option('cp_admin_notices', $notices);
			}
			// Quit out
		endif;
		die();
	}
	public static function cp_transfer_order(){
		check_ajax_referer( 'transfer-order', 'security' );
		if(CP()->qbo->license_info->status=="valid"):
			$post_id   = (int) $_POST['post_id'];
			$customer_id 	= get_post_meta( $post_id, '_qbo_cust_id', true );
			if( !$customer_id ){
				$qbo  = CP()->client->qbo_add_customer( $post_id, $posted );
				if($qbo->qbo_cust_id){
					update_post_meta( $post_id, '_qbo_cust_id', $qbo->qbo_cust_id );
					CP()->cp_qbo_send_order( $post_id, CP()->qbo->order_trigger, '' );
				}
			}else{
				CP()->cp_qbo_send_order( $post_id, CP()->qbo->order_trigger, '' );
			}
			$notices	= get_option('cp_admin_notices', array());
			$notices[]	= 'Payment #' . $post_id . ' has been queued to send to QuickBooks.';
			update_post_meta( $post_id, '_cp_is_queued', 'yes');
			update_option('cp_admin_notices', $notices);
			// Quit out
		endif;
		die();
	}
	public static function cp_resend_order_qbo(){
		if( current_user_can( 'manage_options' ) && check_ajax_referer( 'cp-transfer-order-qbo', 'security' ) ){
			if(CP()->qbo->license_info->status=="valid"):
				$post_id   = (int) $_POST['order_id'];
				$customer_id 	= get_post_meta( $post_id, '_qbo_cust_id', true );
				if( !$customer_id ){
					$qbo  = CP()->client->qbo_add_customer( $post_id, $posted );
					if($qbo->qbo_cust_id){
						update_post_meta( $post_id, '_qbo_cust_id', $qbo->qbo_cust_id );
						CP()->cp_qbo_send_order( $post_id, CP()->qbo->order_trigger, '' );
					}
				}else{
					CP()->cp_qbo_send_order( $post_id, CP()->qbo->order_trigger, '' );
				}
				$notices	= get_option('cp_admin_notices', array());
				$notices[]	= 'Payment #' . $post_id . ' has been queued to send to QuickBooks.';
				update_post_meta( $post_id, '_cp_is_queued', 'yes');
				update_option('cp_admin_notices', $notices);
				// Quit out
			endif;
		}
		die();
	}
	public static function cp_transfer_order_qbo() {
		if(CP()->qbo->license_info->status=="valid"):
			if ( current_user_can( 'manage_options' ) && check_ajax_referer( 'payment-transfer-nonce', 'security' ) ) {
				$order_id = absint( $_POST['order_id'] );
				if($order_id > 0){
					CP_Messages::add_message('Payment #' . $order_id . ' has been queued to send to QuickBooks.');
					CP()->cp_qbo_send_order( $order_id, $_POST['trigger'], '' );
				}
				//update_post_meta( $order_id, '_cp_is_queued', 'yes');
			}
		endif;
		die();
	}
	public static function cp_sync_start(){
		check_ajax_referer( 'cp-options-nonce', 'security' );
		if(CP()->qbo->license_info->status=="valid"):
			CP()->cp_queue_inventory( );
		endif;
		// Quit out
		die();
	}
	public static function cp_break_sync(){
		check_ajax_referer( 'sync-product', 'security' );
		if(CP()->qbo->license_info->status=="valid"):
			$post_id   = (int) $_POST['post_id'];
			delete_post_meta( $post_id, 'qbo_data' );
		endif;
		die();
	}
	public static function cp_sync_product(){
		check_ajax_referer( 'sync-product', 'security' );
		if(CP()->qbo->license_info->status=="valid"):
			$post_id   = (int) $_POST['post_id'];
			if ( $post_id > 0 ) {
				$queue_id = CP()->cp_queue_product( $post_id );
			};
		endif;
		//wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=product' ) );
	}
	public static function cp_update_product(){
		
		check_ajax_referer( 'sync-product', 'security' );
		if(CP()->qbo->license_info->status=="valid"):
			$data = array(
				'desc'	=> $_POST['qb_desc'],
				'id'	=> $_POST['qb_id'],
				'name'	=> $_POST['qb_name'],
				'price'	=> $_POST['qb_price'],
				'status'=> $_POST['qb_status'],
				'tax'	=> $_POST['qb_taxable'],
				'type'	=> $_POST['qb_type'],
			);
			//echo json_encode($data);
			$qbo = maybe_unserialize(  CP()->client->qbo_update_item( $data ) );
			echo json_encode($qbo);
		endif;
		die();
		
		
	}
	public static function cp_release_queue(){
		check_ajax_referer( 'transfer-order', 'security' );
		update_option('cp_is_working', 'no');
		echo 'is released';
		die();
	}
}
CP_AJAX::init();
