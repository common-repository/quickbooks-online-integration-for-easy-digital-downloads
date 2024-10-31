<?php 
	global $wpdb;
	$prod = get_product( $ref_id );
	$prods['prods'][$prod->get_sku()] = $ref_id;
	$qbo = maybe_unserialize(  CP()->client->qbo_sync_item( $prods ) );
	if($qbo){
		foreach($qbo as $key=>$product){
			$product_id = $product->web_item;
			if ($product_id) {
				$wc_prod = get_product($product_id);
				$wc_prod->set_stock( $product->qty );
				update_post_meta( $product_id, '_price', $product->price );
				update_post_meta( $product_id, '_regular_price', $product->price );
				update_post_meta( $product_id, 'qbo_product_id', $product->id );
				update_post_meta( $product_id, 'qbo_data', $product );
				
			}else{
				if(isset($product->full_name) && $product->full_name != ''){
					$id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $product->full_name	 ) );
				}else{
					$id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $product->name ) );	
				}
    		 	
				
    		   //If the subscription supports it, non-website items are returned to be imported.
    		  	 if( $id == false || $id =='' ){
    		  		$fallout_id = CP()->cp_insert_fallout(cptexturize($product->name), $error = 'QB Item Not Found in Website', 'review', 'product' );
					update_post_meta( $fallout_id, 'qb_product', $product );	 
    		  	 	//CP()->cp_qbo_import_item( $product );
			 	}
			}
		}
		wp_set_object_terms( $query->post->ID , 'success', 'queue_status'. false );
		return $qbo;
	}
	
	
	