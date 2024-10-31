<?php 
	global $wpdb;
	$prods = array('prods'=>array());
	$args = array( 
		'post_type' => 
		array(
			'product', 
			'product_variation'
		),
		'posts_per_page' => -1 
	);
	query_posts( $args );
	if( have_posts() ):
	    while ( have_posts() ) : the_post();
			$prod = get_product(get_the_ID());
			$prods['prods'][$prod->get_sku()] = get_the_ID();
	    endwhile;
	endif;
	
	$qbo = maybe_unserialize( $sass_api->qbo_get_items( $prods ) );
	if($qbo){
		foreach($qbo as $key=>$product){
			$product_id = $product->web_item;
			if ($product_id) {
				$wc_prod = get_product($product_id);
				$wc_prod->set_stock( $product->qty );
				update_post_meta( $product_id, '_price', $product->price );
				update_post_meta( $product_id, '_regular_price', $product->price );
				update_post_meta( $product_id, 'qbo_product_id', $product->id );
    		 }else{
    		 	$id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $product->name ) );
				
    		   //If the subscription supports it, non-website items are returned to be imported.
    		  	 if( $id == false || $id =='' ){
    		  	 	//$this->sod_qbo_import_item( $product );
			   	}
    		   	
    		 }
		}
	}
	wp_set_object_terms( $query->post->ID , 'success', 'queue_status'. false );