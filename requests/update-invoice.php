<?php 
	$data 					= array();
	$WC_Order 				= wc_get_order( $ref_id );
	$items 					= $WC_Order->get_items();
	$taxes 					= $WC_Order->get_taxes();
	$qbo_id 				= false;
	$qbo_data				= maybe_unserialize( get_post_meta( $ref_id, '_quickbooks_data', true ) );
	$trans_id				= $qbo_data->data;
	if($trans_id){
		foreach($items as $key=>$value){
			if(isset( $value['variation_id'] ) && absint($value['variation_id']) > 0 ){
				$qbo_id = get_post_meta( $value['variation_id'], 'qbo_product_id', true );
			}elseif(( $value['product_id'] ) ){
				$qbo_id = get_post_meta( $value['product_id'], 'qbo_product_id', true );
			}; 
			$new_items[]= array(
				'name' 			=> isset($value['variation_id']) && ($value['variation_id'] > 0 ) ? get_post_meta( $value['variation_id'], '_sku', true ) : $value['name'],
				'qty'			=> $value['qty'],
				'tax_class'		=> $value['tax_class'],
				'web_id'		=> isset($value['variation_id']) && ($value['variation_id'] > 0 ) ? $value['variation_id'] : $value['product_id'],
				'subtotal' 		=> $value['line_total'],
				'qbo_product_id'=> $qbo_id ? $qbo_id : ''
			);
			
		};
		$order_items				= json_encode( $new_items );
		$data = array(
				'trans_id'				=> cptexturize( $trans_id ) ,
				'order_id'				=> $ref_id,
				'refRenumber' 			=> $WC_Order->get_order_number(),
				'txnTime' 				=> $WC_Order->post->post_date,
				'billing_first_name'	=> $WC_Order->billing_first_name,
				'billing_last_name'		=> $WC_Order->billing_last_name,
				'billing_address_1'		=> $WC_Order->billing_address_1,
				'billing_address_2'		=> $WC_Order->billing_address_2,
				'billing_city'			=> $WC_Order->billing_city,			
				'billing_state'			=> $WC_Order->billing_state,
				'billing_postcode'		=> $WC_Order->billing_postcode,
				'shipping_first_name'	=> $WC_Order->shipping_first_name,
				'shipping_last_name'	=> $WC_Order->shipping_last_name,
				'shipping_address_1'	=> $WC_Order->shipping_address_1,
				'shipping_address_2'	=> $WC_Order->shipping_address_2,
				'shipping_city'			=> $WC_Order->shipping_city,
				'shipping_state'		=> $WC_Order->shipping_state,
				'shipping_postcode'		=> $WC_Order->shipping_postcode,
				'qbo_cust_id'			=> get_post_meta( $ref_id, '_qbo_cust_id', true),
				'order_items' 			=> $new_items,
				'order_total'			=> $WC_Order->get_total(),
				'order_subtotal'		=> $WC_Order->get_subtotal(),
				'posting_type'			=> 'invoice'
		);
		if(sizeof($taxes) > 0){
			$data['taxes'] = $taxes;
		}
		
		$qbo 			= CP()->client->qbo_update_order( $ref_id, $data );
		$qbo->post_id 	= $query->post->ID;
		if($qbo->data){
			$qbo->has_transferred = true;
			update_post_meta( $ref_id , '_quickbooks_data', maybe_serialize( $qbo ) );
			update_post_meta( $ref_id , 'cust_id', $qbo->cust_id);
			update_post_meta( $ref_id , '_cp_is_queued', 'success');
			wp_set_object_terms( $query->post->ID , 'success', 'queue_status'. false );
		}else{
			$errors = explode(':', $qbo->errors);
			if($errors[0] == '3200'){
				wp_delete_post( $query->post->ID );
				sleep(60);
				CP()->sod_qbo_send_order( $ref_id );
			}else{
				CP()->cp_insert_fallout($ref_id, json_encode($qbo), 'update-invoice', 'order');
				update_post_meta( $ref_id , '_cp_errors', $qbo->errors);
				wp_set_object_terms( $query->post->ID , 'failed', 'queue_status'. false );
			}
		}
		return $qbo;
	}