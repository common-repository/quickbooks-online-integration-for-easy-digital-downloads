<?php 
	$data 					= array();
	$payment 				=  edd_get_payments(array('output'=>'payments', 'p'=>$ref_id) );
	$edd_payment_method		= edd_get_payment_gateway( $ref_id );
	$qbo_pay_methods 		= CP()->qbo->payment_methods;
	if(sizeof($qbo_pay_methods) > 0){
		$qbo_pay_method = $qbo_pay_methods[$edd_payment_method];
	}
	$data = array(
			'order_id'				=> $ref_id,
			'refRenumber' 			=> $payment[0]->ID,
			'txnTime' 				=> $payment[0]->date,
			'qbo_cust_id'			=> get_post_meta( $payment[0]->ID, '_qbo_cust_id', true),
			'qbo_invoice_number'	=> get_post_meta( $payment[0]->ID, '_qbo_invoice_number', true),
			'order_total'			=> $payment[0]->total,
			'order_subtotal'		=> $payment[0]->subtotal,
			'payment_method'		=> $qbo_pay_method,
			'deposit_account'		=> CP()->qbo->deposit_account,
			'posting_type'			=> 'payment'
	);
	
	$qbo = CP()->client->qbo_add_order( $ref_id, cpencode( $data ), CP()->qbo->license );
	
	
	if($qbo->data){
		$qbo->has_transferred = true;
		$data 				= (array) maybe_unserialize( get_post_meta( $ref_id, '_quickbooks_data', true) );
		$data['payment'] 	= $qbo;
		update_post_meta( $ref_id , '_quickbooks_data', maybe_serialize( $data ) );
		update_post_meta( $ref_id , '_qbo_payment_number', $qbo->data );
		wp_set_object_terms( $query->post->ID , 'success', 'queue_status'. false );
	}else{
		CP()->cp_insert_fallout('Payment #'.$ref_id,$ref_id, $qbo->errors, 'create-payment', 'order');
		update_post_meta( $ref_id , '_cp_errors', $qbo->errors);
		wp_set_object_terms( $query->post->ID , 'failed', 'queue_status'. false );
	}