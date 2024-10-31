<?php
/**
 * CP QBO Order Data
 *
 * Functions for displaying the qbo order data meta box.
 *
 * @author 		CartPipe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WC_Meta_Box_Order_Data Class
 */
class CP_QBO_Order_Meta_Box {

		
	
	/**
	 * Output the metabox
	 */
	public static function output( $payment_id ) {
		$cartpipe_data_tabs = apply_filters( 'cartpipe_product_data_tabs', array(
						'quickbooks' => array(
							'label'  => __( 'QuickBooks Online', 'cartpipe' ),
							'target' => 'quickbooks_order_data',
						),
						'queue' => array(
							'label'  => __( 'Cartpipe Activity', 'cartpipe' ),
							'target' => 'queue_product_data',
						),
						'fallout' => array(
							'label'  => __( 'Cartpipe Error Log', 'cartpipe' ),
							'target' => 'fallout_order_data',
						),
						// 'sync' => array(
							// 'label'  => __( 'Sync Options', 'cartpipe' ),
							// 'target' => 'sync_product_data',
						// ),
						
					));
		$fields = array('_cp_invoice_id', '_cp_customer_id', '_cp_errors');
		foreach ($fields as $value) {
			
			$qb[$value] = get_post_meta( $payment_id, $value, true);
		}
		$queue 		= CP()->cp_lookup_queue_items( $payment_id );
		$fallout 	= CP()->cp_lookup_fallout_items( $payment_id );
		
		?>
		<div class="clear"></div>
		<div id="qbo-order-data" class="postbox">
					<h3 class="hndle">
						<i class="cp-logo"></i>
						<span><?php _e('Cartpipe', 'cartpipe');?></span>
					</h3>
					<div class="panel-wrap">
					<div class="inside">
						<ul class="cartpipe_data_tabs cp-tabs wc-tabs">
							<?php 
								foreach ( $cartpipe_data_tabs as $key => $tab ) {
									?><li class="<?php echo $key; ?>_options <?php echo $key; ?>_tab ">
										<a href="#<?php echo $tab['target']; ?>"><?php echo esc_html( $tab['label'] ); ?></a>
									</li><?php
								}	do_action( 'cartpipe_product_write_panel_tabs' );
							?>
						</ul>
				
						<div id="quickbooks_order_data" class="cp_data panel" >
						<div class="qb_content">
				<?php 
								$data = maybe_unserialize( get_post_meta( $payment_id , '_quickbooks_data', true ) );
								
								if($data){
									foreach($data as $key => $item){
										$properties = array(
											'cust_id'=>array(
													'can_edit'=>false,
													'type'		=> 'input',
													'label'		=> 'QB Customer ID'
												),
											'data'=>array(
													'can_edit'=>false,
													'type'		=> 'input',
													'label'		=> 'Transaction Ref #'
												),
											'type'=>array(
													'can_edit'	=>	false,
													'type'		=> 'input',
													'label'		=> 'Transaction Type'
												),
										);
								if($key){?>
								<h4 class="type-heading"><?php _e('QuickBooks ' . ucwords( str_replace('_',' ', $key ) ) . ' Details', 'cartpipe');?></h4>
								<?php };?>
								<?php 
								foreach($properties as $prop=>$prop_data){
									
									if(isset($item->$prop)){
									?>
									<p class="form-field <?php echo $prop;?>_field">
										
										<label for="qbo_product_<?php echo $prop;?>"><?php echo isset($prop_data['label']) ? $prop_data['label'] : sprintf('%s %s', __('Order', 'cartpipe'), ucwords(str_replace('_', ' ', $prop ) ) );?></label>
										<?php switch ($prop_data['type']) {
											case 'input':?>
												<input type="text" 
													class="short <?php echo $prop_data['can_edit'] ? 'can_edit' : '';?>" 
													disabled="disabled" 
													name="qbo_product_<?php echo $prop;?>" 
													id="qbo_product_<?php echo $prop;?>" 
													value="<?php echo  cptexturize( wp_kses_post( ucwords(str_replace('_', ' ', $item->$prop) )  ) );?>"
												></input>
												
												<?php break;
											case 'select':?>
												<select 
													class="short <?php echo $prop_data['can_edit'] ? 'can_edit' : '';?>" 
													disabled="disabled" 
													name="qbo_product_<?php echo $prop;?>" 
													id="qbo_product_<?php echo $prop;?>">
													<?php foreach($prop_data['options'] as $option){?> 
														<option value="<?php echo  cptexturize( wp_kses_post( ucwords(str_replace('_', ' ', $option) )  ) );?>"><?php echo  cptexturize( wp_kses_post( ucwords(str_replace('_', ' ',$option) )  ) );?></option>
													<?php }?>
												</select>
												<?php break;
											case 'textarea':?>
												<input type="textarea" 
													class="short <?php echo $prop_data['can_edit'] ? 'can_edit' : '';?>" 
													disabled="disabled" 
													name="qbo_product_<?php echo $prop;?>" 
													id="qbo_product_<?php echo $prop;?>" 
													value="<?php echo  cptexturize( wp_kses_post( ucwords(str_replace('_', ' ', $item->$prop) )  ) );?>"
												></input>
												<?php break;
										}?>
										
									</p>
								<?php }
									}
									}
								}	?>
								<p class="form-field">
									<label for="sync">
										<?php _e( 'Sync Options', 'cartpipe' ); ?> 
										<img class="help_tip" data-tip='<?php esc_attr_e( 'Clicking the "Update EDD Data" button will send the order to QuickBooks. If the order has already been sent to QuickBooks, clicking will re-send and re-create the order in QuickBooks.', 'cartpipe' ); ?>' src="<?php echo CP()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
									</label>
									<?php if($data!=''):
											if(!$data['has_transferred']):?>
												<a href="#" class="transfer-to button">
													<?php _e( 'Transfer To QuickBooks Online', 'cartpipe' ); ?>
												</a>
											<?php else:?>
												<p class="form-field">
													<?php _e('Enable Re-Sending?', 'cartpipe');?>
													<input type="checkbox" name="qb_resend" id="qb_resend"/>
												</p>
												<p class="form-field">
													<a href="#" class="transfer-to button hide">
														<?php _e( 'Transfer To QuickBooks Online', 'cartpipe' ); ?>
													</a>
												</p>
											<?php endif;?>
										<?php else:?>
												<a href="#" class="transfer-to button">
													<?php _e( 'Transfer To QuickBooks Online', 'cartpipe' ); ?>
												</a>
									<?php endif;?>
								</p>					
							</div>
						</div>
						<div id="queue_product_data" class="cp_data queue panel" style="display:none;">
						<div class="queue_content">
							<h4><?php _e('Queue Actions', 'cartpipe');?></h4>
							<table class="queue-actions">
							<?php 
							if(sizeof( $queue > 0 ) ){	
								foreach($queue as $queue_item){
									$id 	= $queue_item->ID;
									$args 	= array('fields'=>'names');
									$action = wp_get_object_terms( $id, 'queue_action', $args);
									$status = wp_get_object_terms( $id, 'queue_status', $args);
									
									?>
									<tr>
										<td class="date">
											<?php echo ucwords( $queue_item->post_date ); ?>
										</td>
										<td class="action">
											<?php echo ucwords( $action[0] ); ?>
										</td>
										<td class="status">
											<?php echo ucwords( $status[0] ); ?>
										</td>
									</tr>	
								<?php } ?>
								
							<?php } ?>
							</table>
						</div>
					</div>	
						<div id="fallout_order_data" class="cp_data queue panel" style="display:none;">
						<div class="queue_content">
							<h4><?php _e('Fallout Data', 'cartpipe');?></h4>
							<table class="queue-actions">
							<?php 
							if(sizeof( $fallout > 0 ) ){	
								foreach($fallout as $fallout_item){
									$id 	= $fallout_item->ID;
									$args 	= array('fields'=>'names');
									$action = wp_get_object_terms( $id, 'fallout_action', $args);
									$status = wp_get_object_terms( $id, 'error_code', $args);
									
									?>
									<tr>
										<td class="date">
											<?php echo ucwords( $queue_item->post_date ); ?>
										</td>
										<td class="action">
											<?php echo ucwords( $status[0] ); ?>
										</td>
										<td class="status">
											<?php echo ucwords( $action[0] ); ?>
											
										</td>
									</tr>	
								<?php } ?>
								
							<?php } ?>
							</table>
						</div>
						</div>	
				</div>
			</div>
		</div>
		
		<?php
	}
	/**
	 * Save meta box data
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;

		
	}
}
