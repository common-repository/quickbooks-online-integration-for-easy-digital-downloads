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
class CP_QBO_Fallout_Meta_Box {

		

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
		global $post;
		$types = wp_get_object_terms( $post->ID, 'fallout_type' );
		switch ($types[0]->slug) {
			case 'product':
				$fields = array('qb_product');
				foreach ($fields as $value) {
					$qb[$value] = get_post_meta($post->ID, $value, true);
				}
				echo '<ul class="fallout_data">';
					foreach( $qb as $key=>$data ) {	?>
						<li><?php printf('QuickBooks Product ID - %s', cptexturize($data->id));?></li>
						<li><?php printf('QuickBooks Name - %s', $data->name);?></li>
						<li><?php printf('QuickBooks Description - %s', $data->description);?></li>
						<li><?php printf('Taxable in QuickBooks? - %s', $data->taxable);?></li>
						<li><?php printf('QuickBooks Price - %s', $data->price);?></li>
						<li><?php printf('QuickBooks Item Type - %s', $data->type);?></li>
					<?php
				}
				echo '</ul>';
				break;
			
			case 'order':
				$fields = array();
				break;
		} 
		
		
		//$queue = CP()->cp_lookup_queue_items( $post->ID );
		
		
		?>
		<h4><?php _e('Fallout Actions', 'cartpipe');?></h4>
		<div class="add_note">
			<h4><?php _e( 'Sync Product', 'cartpipe' ); ?> <img class="help_tip" data-tip='<?php esc_attr_e( 'Clicking the "Sync" button will check the current product against QuickBooks to try and locate it.', 'cartpipe' ); ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" /></h4>
			<p>
				<a href="#" class="sync button"><?php _e( 'Sync', 'cartpipe' ); ?></a>
			</p>
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
