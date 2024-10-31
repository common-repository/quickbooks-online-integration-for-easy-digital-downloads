<?php 
	$labels = array(
		'name'               => _x( 'Queue',  'cartpipe'),
		'singular_name'      => _x( 'Queue Item',  'cartpipe'),
		'menu_name'          => _x( 'Queue', 'cartpipe'),
		'name_admin_bar'     => _x( 'Queue',  'cartpipe'),
		'add_new'            => _x( 'Add New',  'cartpipe'),
		'add_new_item'       => __( 'Add New Queue Item', 'cartpipe'),
		'new_item'           => __( 'New Queue Item', 'cartpipe'),
		'edit_item'          => __( 'Edit Queue Item', 'cartpipe'),
		'view_item'          => __( 'View Queue Item', 'cartpipe'),
		'all_items'          => __( 'Queue Items', 'cartpipe'),
		'search_items'       => __( 'Search Queue Items', 'cartpipe'),
		'parent_item_colon'  => __( 'Parent Queue Item:', 'cartpipe'),
		'not_found'          => __( 'No queued items found.', 'cartpipe'),
		'not_found_in_trash' => __( 'No queued items found in Trash.', 'cartpipe')
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => 'cartpipe',
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'queue' ),
		'capability_type'    => 'post',
		'capabilities' 		 => array(
    		'create_posts' => false, // Removes support for the "Add New" function
  		),
  		'map_meta_cap' 		 => false,
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( )
	);

	register_post_type( 'cp_queue', $args );
	$labels = array(
		'name'              => _x( 'Status', 'taxonomy general name' ),
		'singular_name'     => _x( 'Status', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Statuses' ),
		'all_items'         => __( 'All Statuses' ),
		'parent_item'       => __( 'Parent Status' ),
		'parent_item_colon' => __( 'Parent Status:' ),
		'edit_item'         => __( 'Edit Status' ),
		'update_item'       => __( 'Update Status' ),
		'add_new_item'      => __( 'Add New Status' ),
		'new_item_name'     => __( 'New Status' ),
		'menu_name'         => __( 'Status' ),
	);

	$args = array(
		'hierarchical'      => false,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'queue_status' ),
	);

	register_taxonomy( 'queue_status', array( 'cp_queue' ), $args );
	
	$labels = array(
		'name'              => _x( 'QuickBooks Status', 'cartpipe'  ),
		'singular_name'     => _x( 'QuickBooks Status', 'cartpipe'  ),
		'search_items'      => __( 'Search QuickBooks Statuses', 'cartpipe' ),
		'all_items'         => __( 'All QuickBooks Statuses', 'cartpipe' ),
		'parent_item'       => __( 'Parent QuickBooks Status', 'cartpipe' ),
		'parent_item_colon' => __( 'Parent QuickBooks Status:', 'cartpipe' ),
		'edit_item'         => __( 'Edit QuickBooks Status' , 'cartpipe'),
		'update_item'       => __( 'Update QuickBooks Status' , 'cartpipe'),
		'add_new_item'      => __( 'Add New QuickBooks Status' , 'cartpipe'),
		'new_item_name'     => __( 'New QuickBooks Status' , 'cartpipe'),
		'menu_name'         => __( 'QuickBooks Status' , 'cartpipe'),
	);

	$args = array(
		'hierarchical'      => false,
		'labels'            => $labels,
		'show_ui'           => false,
		'show_admin_column' => false,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'qb_status' ),
	);

	register_taxonomy( 'qb_status', array( 'product', 'shop_order' ), $args );
	$labels = array(
		'name'              => _x( 'Action', 'cartpipe' ),
		'singular_name'     => _x( 'Action', 'cartpipe' ),
		'search_items'      => __( 'Search Actions' , 'cartpipe'),
		'all_items'         => __( 'All Actions', 'cartpipe' ),
		'parent_item'       => __( 'Parent Action', 'cartpipe' ),
		'parent_item_colon' => __( 'Parent Action:', 'cartpipe' ),
		'edit_item'         => __( 'Edit Action', 'cartpipe' ),
		'update_item'       => __( 'Update Action' , 'cartpipe'),
		'add_new_item'      => __( 'Add New Action', 'cartpipe' ),
		'new_item_name'     => __( 'New Action', 'cartpipe' ),
		'menu_name'         => __( 'Action', 'cartpipe' ),
	);

	$args = array(
		'hierarchical'      => false,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'queue_action' ),
	);

	register_taxonomy( 'queue_action', array( 'cp_queue' ), $args );
	$actions = array(
			'queue_action'=>array(
				'sync inventory', 
				'sync item',
				'create invoice', 
				'create sales receipt', 
				'create payment',
				'create creditmemo',
				'import inventory',
				'check customer'
			),
			'queue_status'=> array(
				'in process',
				'error',
				'queued',
				'success'
			),
			'qb_status'=> array(
				'In QuickBooks',
				'Not In QuickBooks'
			)
	);
	foreach($actions as $tax=>$action){
		foreach($action as $term){
			if(!term_exists( $term, $tax )){
				 wp_insert_term( $term, $tax );
			}	
		}
	}
	$labels = array(
		'name'               => _x( 'Fallout',  'cartpipe' ),
		'singular_name'      => _x( 'Fallout Item', 'cartpipe' ),
		'menu_name'          => _x( 'Fallout', 'cartpipe' ),
		'name_admin_bar'     => _x( 'Fallout',  'cartpipe' ),
		'add_new'            => _x( 'Add New',  'cartpipe' ),
		'add_new_item'       => __( 'Add New Fallout Item', 'cartpipe' ),
		'new_item'           => __( 'New Fallout Item', 'cartpipe' ),
		'edit_item'          => __( 'Edit Fallout Item', 'cartpipe' ),
		'view_item'          => __( 'View Fallout Item', 'cartpipe' ),
		'all_items'          => __( 'Fallouts', 'cartpipe' ),
		'search_items'       => __( 'Search Fallout Items', 'cartpipe' ),
		'parent_item_colon'  => __( 'Parent Fallout Item:', 'cartpipe' ),
		'not_found'          => __( 'No fallout items found.', 'cartpipe' ),
		'not_found_in_trash' => __( 'No fallout items found in Trash.', 'cartpipe' )
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => 'cartpipe',
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'fallout' ),
		'capability_type'    => 'post',
		'capabilities' 		 => array(
    		'create_posts' => false, // Removes support for the "Add New" function
  		),
  		'map_meta_cap' 		 => false,
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array('title')
	);

	register_post_type( 'cp_fallout', $args );
	$labels = array(
		'name'              => _x( 'Error Code', 'cartpipe'),
		'singular_name'     => _x( 'Error Code', 'cartpipe' ),
		'search_items'      => __( 'Search Error Codes' , 'cartpipe'),
		'all_items'         => __( 'All Error Codes' , 'cartpipe'),
		'parent_item'       => __( 'Parent Error Code', 'cartpipe' ),
		'parent_item_colon' => __( 'Parent Error Code:', 'cartpipe' ),
		'edit_item'         => __( 'Edit Error Code', 'cartpipe' ),
		'update_item'       => __( 'Update Error Code' , 'cartpipe'),
		'add_new_item'      => __( 'Add New Error Code', 'cartpipe' ),
		'new_item_name'     => __( 'New Error Code' , 'cartpipe'),
		'menu_name'         => __( 'Error Code', 'cartpipe' ),
	);

	$args = array(
		'hierarchical'      => false,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'error_code' ),
	);

	register_taxonomy( 'error_code', array( 'cp_fallout' ), $args );
	$labels = array(
		'name'              => _x( 'Fallout Type', 'cartpipe' ),
		'singular_name'     => _x( 'Fallout Type', 'cartpipe' ),
		'search_items'      => __( 'Search Fallout Types', 'cartpipe' ),
		'all_items'         => __( 'All Fallout Types', 'cartpipe'),
		'parent_item'       => __( 'Parent Fallput Type', 'cartpipe' ),
		'parent_item_colon' => __( 'Parent Fallout Type:', 'cartpipe' ),
		'edit_item'         => __( 'Edit Fallout Type', 'cartpipe' ),
		'update_item'       => __( 'Update Fallout Type', 'cartpipe' ),
		'add_new_item'      => __( 'Add New Fallout Type','cartpipe' ),
		'new_item_name'     => __( 'New Fallout Type', 'cartpipe'),
		'menu_name'         => __( 'Fallout Type', 'cartpipe' ),
	);

	$args = array(
		'hierarchical'      => false,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'fallout_type' ),
	);

	register_taxonomy( 'fallout_type', array( 'cp_fallout' ), $args );
	$labels = array(
		'name'              => _x( 'Action', 'cartpipe' ),
		'singular_name'     => _x( 'Action', 'cartpipe' ),
		'search_items'      => __( 'Actions', 'cartpipe' ),
		'all_items'         => __( 'All Actions', 'cartpipe'),
		'parent_item'       => __( 'Parent Action', 'cartpipe' ),
		'parent_item_colon' => __( 'Parent Action:', 'cartpipe' ),
		'edit_item'         => __( 'Edit Action', 'cartpipe' ),
		'update_item'       => __( 'Update Action', 'cartpipe' ),
		'add_new_item'      => __( 'Add New Action','cartpipe' ),
		'new_item_name'     => __( 'New Action', 'cartpipe'),
		'menu_name'         => __( 'Action', 'cartpipe' ),
	);

	$args = array(
		'hierarchical'      => false,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'fallout_action' ),
	);

	register_taxonomy( 'fallout_action', array( 'cp_fallout' ), $args );
	$actions = array(
			'error_code'=>array(
				
			),
			'fallout_type'=>array(
				'product',
				'order'
			),
			'fallout_action'=>array(
				'resend',
				'ignore',
				'hold',
				'review'
			),
	);
	foreach($actions as $tax=>$action){
		foreach($action as $term){
			if(!term_exists( $term, $tax )){
				 wp_insert_term( $term, $tax );
			}	
		}
	}
	function restrict_queue_by_queue_status() {
		global $typenow;
		
		$post_types = array(
				'cp_fallout'=> array('fallout_type', 'fallout_action') , 
				'cp_queue'	=> array('queue_status', 'queue_action'),
				'product'	=> 'qb_status',
				'shop_order'=> 'qb_status',
				
		); // change 
		foreach($post_types as $post_type => $taxonomy){
			if ($typenow == $post_type) {
				if(is_array($taxonomy)){
					foreach ($taxonomy as $tax) {
						$selected = isset($_GET[$tax]) ? $_GET[$tax] : '';
						$info_taxonomy = get_taxonomy($tax);
						
						wp_dropdown_categories(array(
							'show_option_all' => __("Show All {$info_taxonomy->label}"),
							'taxonomy' => $tax,
							'name' => $tax,
							'orderby' => 'name',
							'selected' => $selected,
							'show_count' => false,
							'hide_empty' => false,
						));		
					}
				}else{
					$selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
					$info_taxonomy = get_taxonomy($taxonomy);
					
					wp_dropdown_categories(array(
						'show_option_all' => __("Show All {$info_taxonomy->label}"),
						'taxonomy' => $taxonomy,
						'name' => $taxonomy,
						'orderby' => 'name',
						'selected' => $selected,
						'show_count' => false,
						'hide_empty' => false,
					));
				}		
				
			};
		}
	}

	add_action('restrict_manage_posts', 'restrict_queue_by_queue_status');

	function convert_id_to_term_in_queue_query($query) {
		global $pagenow;
		$post_types = array(
				'cp_fallout'=> array('fallout_type', 'fallout_action') ,  
				'cp_queue'	=> array('queue_status', 'queue_action'),
				'product'	=> 'qb_status',
				'shop_order'=> 'qb_status',
		); // change HERE
		
		$q_vars = &$query->query_vars;
		
		foreach($post_types as $post_type => $taxonomy){
			
			if(is_array($taxonomy)){
				foreach($taxonomy as $tax){
					if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($_GET[$tax]) && is_numeric($_GET[$tax]) && $_GET[$tax] != 0) {
						$term = get_term_by('id', $_GET[$tax], $tax);
						$q_vars[$tax] = $term->slug;
					}
				}
			}else{
				
				if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($_GET[$taxonomy]) && is_numeric($_GET[$taxonomy]) && $_GET[$taxonomy] != 0) {
					
					$term = get_term_by('id', $_GET[$taxonomy], $taxonomy);
					
					$q_vars[$taxonomy] = $term->slug;
				}
			}
		}
	}

	add_filter('parse_query', 'convert_id_to_term_in_queue_query', 99);