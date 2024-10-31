<?php 
	$data 					= array();
	$payment 				=  edd_get_payments(array('output'=>'payments', 'p'=>$ref_id) );
	$items 					= $payment[0]->cart_details;
	$taxes 					= $payment[0]->tax;
	$qbo_id 				= false;
	$edd_payment_method		= edd_get_payment_gateway( $ref_id );
	$qbo_pay_methods 		= CP()->qbo->payment_methods;
	if(sizeof($qbo_pay_methods) > 0){
		$qbo_pay_method = $qbo_pay_methods[$edd_payment_method];
	}
	
	foreach($items as $key=>$value){
		if(isset( $value['id'] ) && absint($value['id']) > 0 ){
			$qbo_id = get_post_meta( $value['id'], 'qbo_product_id', true );
		} 
		$new_items[]= array(
			'name' 			=> isset($value['id']) && ($value['id'] > 0 ) ? edd_get_download_sku($value['id']) : $value['name'],
			'sku' 			=> isset($value['id']) && ($value['id'] > 0 ) ? edd_get_download_sku($value['id']) : $value['name'],
			'qty'			=> $value['quantity'],
			'web_id'		=> isset($value['id']) && ($value['id'] > 0 ) ? $value['id'] : '',
			'subtotal' 		=> $value['subtotal'],
			'qbo_product_id'=> $qbo_id ? $qbo_id : '',
			'tax'			=> $value['tax'],
			'fees'			=> $value['fees'],
			'total'			=> $value['price'],
		);
		
	};
	$order_items				= json_encode( $new_items );
	$data = array(
			'order_id'				=> $ref_id,
			'refRenumber' 			=> $payment[0]->ID,
			'txnTime' 				=> $payment[0]->date,
			'billing_first_name'	=> $payment[0]->user_info['first_name'],
			'billing_last_name'		=> $payment[0]->user_info['last_name'],
			'billing_address_1'		=> $payment[0]->user_info['address']['line1'],
			'billing_address_2'		=> $payment[0]->user_info['address']['line2'],
			'billing_city'			=> $payment[0]->user_info['address']['city'],			
			'billing_state'			=> $payment[0]->user_info['address']['state'],
			'billing_postcode'		=> $payment[0]->user_info['address']['zip'],
			'qbo_cust_id'			=> get_post_meta( $ref_id, '_qbo_cust_id', true),
			'order_items' 			=> $new_items,
			'order_total'			=> $payment[0]->total,
			'order_subtotal'		=> $payment[0]->subtotal,
			'payment_method'		=> $qbo_pay_method,
			'posting_type'			=> 'invoice'
	);
	
	if(sizeof($taxes) > 0){
		$rates 		= array();
		$mappings 	= CP()->qbo->taxes;
		if(isset($mappings[strtolower($payment[0]->user_info['address']['country'] . '_' . $payment[0]->user_info['address']['state'])])){
			$rates[] = array(
				'qbo_id'	=> $mappings[strtolower($payment[0]->user_info['address']['country'] . '_' . $payment[0]->user_info['address']['state'])],
				'tax_amount'=> $payment[0]->tax,
				'rate_id'	=> strtolower($payment[0]->user_info['address']['country'] . '_' . $payment[0]->user_info['address']['state'])
			);	
		}  
		$data['taxes'] = $rates;
	}
	
	$qbo 			= CP()->client->qbo_add_order( $ref_id, cpencode( $data ), CP()->qbo->license );
	$qbo->post_id 	= $query->post->ID;
	if(isset($qbo->data)){
		$qbo->has_transferred = true;
		$data 				= (array) maybe_unserialize( get_post_meta( $ref_id, '_quickbooks_data', true) );
		$data['invoice'] 	= $qbo;
		update_post_meta( $ref_id , '_quickbooks_data', maybe_serialize( $data ) );
		update_post_meta( $ref_id , '_qbo_invoice_number', $qbo->data );
		update_post_meta( $ref_id , '_qbo_cust_id', $qbo->cust_id);
		update_post_meta( $ref_id , '_cp_is_queued', 'success');
		wp_set_object_terms( $query->post->ID , 'success', 'queue_status'. false );
		wp_set_object_terms($ref_id , 'in-quickbooks', 'qb_status'. false );
	}else{
		$errors = explode(':', $qbo->errors);
		if($errors[0] == '3102'){
			$message = 'You couldn\'t authenticate with QuickBooks Online. Make sure you\'ve connected your QuickBooks Online account with Cartpipe at <a href="cartpipe.com">Cartpipe.com</a>';
			CPM()->add_message( $message, $ref_id, true);
			update_post_meta( $ref_id , '_quickbooks_error_message', $message );
		}else{
			if($qbo->cp_messages){
				if(is_array($qbo->cp_messages)){
					foreach($qbo->cp_messages as $message){
						CPM()->add_message($qbo->cp_messages, $ref_id, true);	
					}	
				}else{
					CPM()->add_message($qbo->cp_messages, $ref_id, true);
				}
			}
		}
		CP()->cp_insert_fallout('Order #'.$ref_id, $ref_id, $qbo->errors, 'create-invoice', 'order');
		
		update_post_meta( $ref_id , '_cp_errors', $qbo->errors);
		wp_set_object_terms( $query->post->ID , 'failed', 'queue_status'. false );
		wp_set_object_terms($ref_id , 'not-in-quickbooks', 'qb_status'. false );
		//}
	}
	return $qbo;
