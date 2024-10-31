<?php 
	$data 					= array();
	$WC_Order 				= wc_get_order( $ref_id );
	$items 					= $WC_Order->get_items();
	$taxes 					= $WC_Order->get_taxes();
	foreach($items as $key=>$value){
		$new_items[]= array(
			'name' 			=> isset($value['variation_id']) && ($value['variation_id'] > 0 ) ? $value['variable'] : $value['name'],
			'qty'			=> $value['qty'],
			'tax_class'		=> $value['tax_class'],
			'subtotal' 		=> $value['line_subtotal'],
			'qbo_product_id'=> isset( $value['variation_id'] ) ? get_post_meta( $value['variation_id'], 'qbo_product_id', true ) : isset( $value['variation_id'] ) ? get_post_meta( $value['variation_id'], 'qbo_product_id', true ) : false,
			
		);
		
	};
	$order_items				= json_encode( $new_items );
	$data = array(
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
			'taxes'					=> $taxes,
			'posting_type'			=> 'refund'
			
			
	);
	
	$qbo = CP()->client->qbo_add_order( $ref_id, cpencode( $data ) );
	if($qbo){
		update_post_meta( $ref_id , 'qbo_invoice_number', $qbo);
		wp_set_object_terms( $query->post->ID , 'success', 'queue_status'. false );
	}

	return $qbo;