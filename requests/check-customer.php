<?php 
	$payment 				= edd_get_payments(array('output'=>'payments', 'p'=>$ref_id) );
	$posted 				= array(
		'billing_first_name'	=> $payment[0]->user_info['first_name'],
		'billing_last_name'		=> $payment[0]->user_info['last_name'],
		'billing_address_1'		=> $payment[0]->user_info['address']['line1'],
		'billing_address_2'		=> $payment[0]->user_info['address']['line2'],
		'billing_city'			=> $payment[0]->user_info['address']['city'],			
		'billing_state'			=> $payment[0]->user_info['address']['state'],
		'billing_postcode'		=> $payment[0]->user_info['address']['zip'],
		'billing_country'		=> $payment[0]->user_info['address']['country'],
		'billing_email'			=> $payment[0]->user_info['address']['email'],
		'billing_phone'			=> $payment[0]->user_info['address']['phone'],
	);
	
	$qbo  = CP()->client->qbo_add_customer( $ref_id, cpencode( $posted ), CP()->qbo->license );
	if($qbo->qbo_cust_id){
		update_post_meta($ref_id, '_qbo_cust_id',  $qbo->qbo_cust_id );
		wp_set_object_terms( $query->post->ID , 'success', 'queue_status'. false );
		//CP()->sod_qbo_send_order( $ref_id );
	}else{
		$errors = explode(':', $qbo->errors);
		CP()->cp_insert_fallout($ref_id, json_encode($qbo), 'check-customer', 'order');
		update_post_meta( $ref_id , '_cp_errors', $qbo->errors);
		wp_set_object_terms( $query->post->ID , 'failed', 'queue_status'. false );
		
	}