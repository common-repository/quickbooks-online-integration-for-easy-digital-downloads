<?php 
	global $wpdb;
	$number_to_send = 20;
	$download 	= wp_count_posts( 'download' )->publish;
	$sum 		= $download;
	$prods 		= array('prods'=>array());
	$args 		= array( 
		'post_type' => 
		array(
			'download', 
		),
		'posts_per_page' => -1,
		'post_status' => array('publish')  
	);
	$download_query = new WP_Query( $args );
	if( $download_query->have_posts() ):
	    while ( $download_query->have_posts() ) : $download_query->the_post();
			$download	= edd_get_download( get_the_ID() );
			$cost 		= get_post_meta( get_the_ID(), '_qb_cost',  true);
			$expense	= get_post_meta( get_the_ID(), '_qb_product_expense_accout',  true);
			$income		= get_post_meta( get_the_ID(), '_qb_product_income_accout',  true);
			$asset 		= get_post_meta( get_the_ID(), '_qb_product_asset_accout',  true);
			$sku 		= edd_get_download_sku( get_the_ID() );
			$prods['prods'][$sku] = array(
						'id' 				=> get_the_ID(),
						'price'				=> edd_price(get_the_ID()),
						'sku'				=> $sku,
						'description'		=> sanitize_text_field(substr( get_the_content(), 0, 1000 ) ),
						'name'				=> sanitize_text_field(substr( get_the_title(), 0, 100) ),
						'taxable'			=> edd_download_is_tax_exclusive(get_the_ID()),
						'active'			=> true,
						'cost' 				=> $cost,
					);
			if(isset( CP()->qbo->asset_account) ){
				if(isset($asset) && $asset != CP()->qbo->asset_account){
					$prods['prods'][$sku]['asset_account'] = $asset;
				}
			}else{
				if(isset($asset)){
					$prods['prods'][$sku]['asset_account'] = $asset;
				}
			}
			if(isset( CP()->qbo->income_account)){
				if(isset($income) && $income != CP()->qbo->income_account){
					$prods['prods'][$sku]['income_account'] = $income;
				}
			}else{
				if(isset($income)){
					$prods['prods'][$sku]['income_account'] = $income;
				}
			}
			if(isset( CP()->qbo->expense_account )){
				if(isset($expense) && $expense != CP()->qbo->expense_account){
					$prods['prods'][$sku]['expense_account'] = $expense;
				}
			}else{
				if(isset($expense)){
					$prods['prods'][$sku]['expense_account'] = $expense;
				}
			}
	    endwhile;
	endif;
	$prods['prods']['export_mapping'] 						= 	isset( CP()->qbo->export_fields ) ? CP()->qbo->export_fields : '';
	$prods['prods']['export_mapping']['income_account'] 	=  	isset( CP()->qbo->income_account ) ? CP()->qbo->income_account : '';
	$prods['prods']['export_mapping']['asset_account'] 		=  	isset( CP()->qbo->asset_account ) ? CP()->qbo->asset_account : '';
	$prods['prods']['export_mapping']['expense_account'] 	=  	isset( CP()->qbo->expense_account ) ? CP()->qbo->expense_account : '';
	
	$qbo = maybe_unserialize(  CP()->client->qbo_get_items( cpencode( $prods ), CP()->qbo->license ) );
	
	if($qbo){
		
		foreach($qbo as $key=>$download){
			if($key == 'cp_messages' || $key == 'messages'){
				CPM()->add_message($download);
			}elseif($key == 'not_in' ) {
				if(sizeof($download) > 0 ){
					foreach ($download as $value) {
						wp_set_object_terms( $value , 'not-in-quickbooks', 'qb_status'. false );	
					}
				}	
			}else{
				if(isset($download->web_item->id)){
					$download_id = $download->web_item->id;
				}else{
					$download_id = false;
				}
				
				if ($download_id) {
					$edd_download = edd_get_download( $download_id );
					
					if(CP()->qbo->sync_price == 'yes'){
						if($download->price &&  $download->price != ''):
							update_post_meta( $download_id , 'edd_price', $download->price );
						endif;
					}
					if(CP()->qbo->store_cost == 'yes'){
						if($product->cost && $product->cost != ''):
							update_post_meta( $download_id , '_qb_cost', $download->cost);
						endif;
					}
					update_post_meta( $download_id, 'qbo_product_id', $download->id );
					update_post_meta( $download_id, 'qbo_data', $download );
					update_post_meta( $download_id, 'qbo_last_updated', current_time('timestamp') );
					wp_set_object_terms( $download_id , 'in-quickbooks', 'qb_status'. false );
					
				}else{
					if(isset($download->full_name) && $download->full_name != ''){
						$id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $product->full_name	 ) );
					}elseif(isset($download->name)){
						$id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $product->name ) );
					}else{
						$id = false;	
					}
					
	    		   //If the subscription supports it, non-website items are returned to be imported.
	    		   if( $id == false || $id =='' ){
	    		  	 	wp_set_object_terms( $download_id , 'not in quickbooks', 'qb_status'. false );
					   	if(isset($download->name) && $download->name != ''){
	    		  			$fallout_id = CP()->cp_insert_fallout(cptexturize( $download->name ), $error = 'QB Item Not Found in Website', 'review', 'product' );
					   	}
						update_post_meta( $fallout_id, 'qb_product', $download );	 
	    		  	 	CP()->cp_qbo_import_item( $download );
				 	}
				}
			}
		}
	}
	wp_set_object_terms( $query->post->ID , 'success', 'queue_status'. false );
	
