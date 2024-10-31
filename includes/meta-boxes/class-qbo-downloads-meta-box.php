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
class CP_QBO_Download_Meta_Box extends CP_Meta_Boxes{

	
	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
		global $post;
		$product 	= edd_get_download($post->ID );
		$qbo_box 	= ''; 
		$cp_options = apply_filters( 'cp_product_options', array(
			'sync' => array(
				'id'            => '_qbo_sync',
				'wrapper_class' => '',
				'label'         => __( 'QuickBooks Sync', 'cartpipe' ),
				'description'   => __( 'Check this box to enable syncing with QuickBooks Online.', 'cartpipe' ),
				'default'       => 'yes'
			),
		) );
		foreach ( $cp_options as $key => $option ) {
			$selected_value = get_post_meta( $post->ID, '_' . $key, true );

			if ( '' == $selected_value && isset( $option['default'] ) ) {
				$selected_value = $option['default'];
			}

			$qbo_box .= '<label for="' . esc_attr( $option['id'] ) . '" class="'. esc_attr( $option['wrapper_class'] ) . ' tips" data-tip="' . esc_attr( $option['description'] ) . '">' . esc_html( $option['label'] ) . ': <input type="checkbox" name="' . esc_attr( $option['id'] ) . '" id="' . esc_attr( $option['id'] ) . '" ' . checked( $selected_value, 'yes', false ) .' /></label>';
		}
		$cartpipe_data_tabs = apply_filters( 'cartpipe_product_data_tabs', array(
						'quickbooks' => array(
							'label'  => __( 'QuickBooks Online', 'cartpipe' ),
							'target' => 'quickbooks_product_data',
						),
					));
		
		$queue = CP()->cp_lookup_queue_items( $post->ID );?>
		<div class="panel-wrap">
			<i class="cp-logo"></i>
			<span class="sync_box"> &mdash; <?php echo $qbo_box; ?></span>
			<ul class="cartpipe_data_tabs wc-tabs" style="display:block;">
			<?php 
				foreach ( $cartpipe_data_tabs as $key => $tab ) {
					?><li class="<?php echo $key; ?>_options <?php echo $key; ?>_tab ">
						<a href="#<?php echo $tab['target']; ?>"><?php echo esc_html( $tab['label'] ); ?></a>
					</li><?php
				}	do_action( 'cartpipe_product_write_panel_tabs' );
			?>
			</ul>
			<div id="quickbooks_product_data" class="cp_data panel" style="display:block;">
					<div class="qb_content">
					<?php 
					if(edd_get_download_sku( $post->ID ) != ''){
						$data = get_post_meta( $post->ID, 'qbo_data', true );
						
						$properties = array(
							'id'=>array(
									'can_edit'=>false,
									'type'		=> 'input'
								),
							'name'=>array(
									'can_edit'	=>	false,
									'type'		=> 'input'
								),
							'full_name'=>array(
									'can_edit'	=> false,
									'type'		=> 'input'
							),
							'taxable'=>array(
									'can_edit'	=> false,
									'options'	=> array('True', 'False'),
									'type'		=> 'select'
							),
							'price'=>array(
									'can_edit'	=> false,
									'type'		=> 'input'
							),
							'cost'=>array(
									'can_edit'	=> false,
									'type'		=> 'input'
							),
							
						);
							foreach($properties as $prop=>$prop_data){?>
							<p class="form-field <?php echo $prop;?>_field">
								
								<label for="qbo_product_<?php echo $prop;?>"><?php printf('%s %s', __('Item', 'cartpipe'), ucwords(str_replace('_', ' ', $prop ) ) );?></label>
								<?php switch ($prop_data['type']) {
									case 'input':?>
										<input type="text" 
											class="short <?php echo $prop_data['can_edit'] ? 'can_edit' : '';?>" 
											disabled="disabled" 
											name="qbo_product_<?php echo $prop;?>" 
											id="qbo_product_<?php echo $prop;?>" 
											value="<?php echo  cptexturize( wp_kses_post( ucwords(str_replace('_', ' ', isset($data->$prop) ?  $data->$prop : '') )  ) );?>"
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
											value="<?php echo  cptexturize( wp_kses_post( ucwords(str_replace('_', ' ', isset($data->$prop) ?  $data->$prop : '') )  ) );?>"
										></input>
										<?php break;
								}?>
								
							</p>
						<?php }
						}else{?>
							<p class="cp-notice"><?php _e('Please enter a sku for any download to sync it with QuickBooks', 'cartpipe');?></p>
						<?php }?>	
						
						<p class="form-field">
							<label for="sync">
								<?php _e( 'Sync Options', 'cartpipe' ); ?> 
								<img class="help_tip" data-tip='<?php esc_attr_e( 'Clicking the "Un-sync" button will delete the stored QBO data for this product', 'cartpipe' ); ?>' src="<?php echo CP()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
							</label>
							<a href="#" name="sync-from" class="button" style="display:none">
								<?php _e( 'Update Download\'s Data', 'cartpipe' ); ?>
							</a>
							<a href="#" name="break-sync" class="button">
								<?php _e( 'Un-sync', 'cartpipe' ); ?>
							</a>
							<a href="#" name="sync-to" class="button" style="display:none">
								<?php _e( 'Update QuickBooks Item', 'cartpipe' ); ?>
							</a>
						</p>					
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
