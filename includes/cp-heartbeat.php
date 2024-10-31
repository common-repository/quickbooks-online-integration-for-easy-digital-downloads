<?php 
/*
 * This is my heartbeat 
 */
 if(!class_exists('CP_HeartBeat')){
 	class CP_HeartBeat{
 		protected static $_instance = null;
		
 		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			
			return self::$_instance;
			
		}
		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'cartpipe' ), '2.1' );
		}
	
		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'cartpipe' ), '2.1' );
		}
		/**
		 * Constructor
		 *
		 * @since 1.0
		 */
		function __construct(){
			//$this->includes();
			//$this->init();
		
			add_filter( 'heartbeat_received',  array( $this, 'cp_qbo_heartbeat_received'), 10, 2  );
			add_filter( 'heartbeat_nopriv_received', array( &$this, 'cp_qbo_heartbeat_received' ), 10, 2 );
			add_action( 'admin_enqueue_scripts',  array( $this, 'cp_qbo_heartbeat_enqueue' ) );
			add_action( 'enqueue_scripts',  array( $this, 'cp_qbo_heartbeat_enqueue' ) );
		}
		function cp_qbo_heartbeat_received( $response, $data ) {
		    // Make sure we only run our query if the edd_heartbeat key is present
		  // if( $data['cp_qbo_heartbeat'] == 'cp_queue' ) {
		  //	if(CP()->qbo->sync_price == 'yes' || CP()->qbo->sync_stock == 'yes' || CP()->store_cost == 'yes'){
		  	if( CP()->qbo->consumer_key && CP()->qbo->consumer_secret && CP()->qbo->license && CP()->qbo->license_info->status=="valid"){
		  		if(CP()->qbo->frequency):
		   			if(false == ( $value = get_transient( 'cp_last_sync' ))){
		   				CP()->cp_queue_inventory();
						set_transient( 'cp_last_sync', current_time('timestamp'), CP()->qbo->frequency );
					}
				endif;
			//}
		    		$working = get_option('cp_is_working');
					if(!$working){
						$working = 'no';
					}			   
		        	//if($working == 'no'):
				        $args = array(
							'post_type'		=> 'cp_queue',
							'posts_per_page'=> 1,
							'tax_query' => array(
								//'relation' => 'AND',
								array(
									'taxonomy' => 'queue_status',
									'field'    => 'slug',
									'terms'    => array( 'queued' ),
								),
							),
							'orderby'=> array(
								'date'
							)
						);
						$query = new WP_Query( $args );
						if ( $query->have_posts() ):
							 while ( $query->have_posts() ) : 
								update_option('cp_is_working', 'yes');
							 	$query->the_post();
								$ref_id 		= get_post_meta( $query->post->ID, 'reference_post_id', true );
								$post_type 		= get_post_type( $ref_id );
								$actions 		= get_the_terms( $query->post->ID, 'queue_action' );
								//take it out of the loop
								wp_set_object_terms( $query->post->ID, 'in-process', 'queue_status' );
								$act 			= '';
								foreach($actions as $action){
									$act = $action->slug;
								}
									
								$params['cp'] = $this;
								$params 	= get_defined_vars();
								switch ($act) {
									case 'sync-item':
										$response['sync-item'] 				= cartpipe_request('sync', 'item', $params); 
										break; 
									case 'sync-inventory':
										$response['sync-inventory'] 		= cartpipe_request('sync', 'inventory', $params);
										break; 
									case 'sync-inventory':
										$response['sync-inventory'] 		= cartpipe_request('sync', 'inventory', $params);
										break;
									case 'check-service':
										$response['check-service'] 			= cartpipe_request('check', 'service', $params);
										break; 
									case 'update-invoice':
										$response['update-invoice'] 		= cartpipe_request('update', 'invoice', $params);
										break; 
									case 'create-invoice':
										$response['create-invoice'] 		= cartpipe_request('create', 'invoice', $params);
										break; 
									case 'create-sales-receipt':
										$response['create-sales-receipt'] 	= cartpipe_request('create', 'sales-receipt', $params);
										break;
									case 'create-refund':
										$response['create-refund'] 			= cartpipe_request('create', 'refund', $params);
										break;  
									case 'create-payment':
										$response['create-payment'] 		= cartpipe_request('create', 'payment', $params);
										break;
									case 'check-customer':
										$response['check-customer'] 		= cartpipe_request('check', 'customer', $params);
										break;
								}
							endwhile;
							
						endif; 
	 					// wp_reset_postdata();
						// wp_reset_query();
 					//endif;
// 
		     //}
		     }else{
		     	
		     	if(CP()->qbo->license_info->expires < date("Y-m-d H:i:s") ){
		     		//CPM()->add_message('Your Cartpipe free trial has expired. If you liked what you saw, click the button to signup. <a href="http://www.cartpipe.com/downloads/cartpipe-online/" target="_blank" class="cp-renew">Renew</a><a href="#" class="cp-recheck-license">Recheck</a>');
		     	}else{
		     		//CPM()->add_message('Please make sure your Cartpipe Consumer Key, Cartpipe Consumer Secret and Cartpipe License are entered correctly and that your license has been activated and is still valid. Click <a href="'.get_admin_url() .'admin.php?page=qbo-settings">here</a> to check that they are.');
				}
		     }
		    return $response;
		}
		function cp_qbo_heartbeat_enqueue( $hook_suffix ) {
		    // Make sure the JS part of the Heartbeat API is loaded.
		    wp_enqueue_script( 'heartbeat' );
			add_action( 'admin_print_footer_scripts', array($this,'cp_qbo_heartbeat_footer_js'), 20 );
			do_action( 'cp_enqueue' );
		}
		function cp_qbo_heartbeat_footer_js() {
		    global $pagenow;
		 
		    // Only proceed if on the dashboard
		    // if( 'index.php' != $pagenow )
		        // return;
		?>
		    <script>
		    (function($){
		 
		        // Hook into the heartbeat-send
		        $(document).on('heartbeat-send', function(e, data) {
		        	
		        	data['cp_qbo_heartbeat'] = 'cp_queue';
		        	
		        });
		 
		        // Listen for the custom event "heartbeat-tick" on $(document).
		        $(document).on( 'heartbeat-tick', function(e, data) {
		 			
		            // Only proceed if our EDD data is present
		          // if ( ! data['qbo-queue'] )
		           //    return;
		         
		           	if ( data['sync-item']){
		           		
		           	}
		           	else if ( data['create-payment']){
		           		
		           	}
		           	else if ( data['sync-inventory']){
		           		
		           	}
		           	else if ( data['create-invoice']){
		           		
		           	}
		           	else if ( data['create-sales-receipt']){
		           		
		           	}
		           	else if ( data['create-refund']){
		           		
		           	}
		           	else if ( data['check-customer']){
		           		
		           	}
		 		
		        });
		    }(jQuery));
		    </script>
		<?php
		}
		
 	}
	 	
 }
function CP_Pulse() {
	return CP_HeartBeat::instance();
}

// Global for backwards compatibility.
$GLOBALS['CP_Pulse'] = CP_Pulse();