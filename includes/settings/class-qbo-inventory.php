<?php
/**
 * QBO Product Settings
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'QBO_Settings_Downloads' ) ) :

/**
 * WC_Settings_Products
 */
class QBO_Settings_Downloads extends QBO_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'downloads';
		$this->label = __( 'Downloads', 'cartpipe' );
		
		$accounts 				 	= get_option('qbo_accounts', false);
		if(isset($accounts->errors) || $accounts == '1' || !$accounts){
			delete_option('qbo_accounts');
		}
		$this->accounts = isset( $accounts ) && $accounts != '' && $accounts ? $accounts : CP()->needs['accounts'] = true;
		if(sizeof(CP()->needs) > 0){
	    	foreach(CP()->needs as $key => $need){
	    		switch ($key) {
					case 'accounts':
						if($need){
							if( CP()->client ):
								$this->accounts  = CP()->client->qbo_get_accounts( CP()->qbo->license );
								update_option( 'qbo_accounts' , $this->accounts );
								$need = false;
							endif;
						}
						break;
				}
	    	}
		}
		
		add_filter( 'qbo_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'qbo_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'qbo_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'qbo_sections_' . $this->id, array( $this, 'output_sections' ) );
	}

	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			'sync'      		=> __( 'Syncing Options', 'cartpipe' ),
			'import_mappings'	=> __( 'Import Field Mappings', 'cartpipe' ),
			'export_mappings' 	=> __( 'Export Field Mappings', 'cartpipe' ),
		);

		return apply_filters( 'qbo_get_sections_' . $this->id, $sections );
	}

	/**
	 * Output the settings
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings( $current_section );

 		QBO_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		QBO_Admin_Settings::save_fields( $settings );
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
				
				$edd_fields = array(
					'name'			=> 'Download Name',
					'description'	=> 'Download Description',
				);
				$qbo_fields = array(
					'name'					=> 'Name',
					'sales_description'		=> 'Sales Description',
					'purchase_description'	=> 'Purchase Description',
				);
				if($current_section == 'sync' || $current_section == ''){
					$edd_product_fields 	= array('sku'=> 'SKU', 'name'=> 'Download Name'); 
					$qbo_product_field	= array('Item Name');
					$settings = apply_filters( 'qbo_product_sync_settings', array(
					
					array( 
						'title' => __( 'Product Settings', 'cartpipe' ), 
						'type' => 'title', 
						'desc' => '', 
						'id' => 'qbo_product_sync' 
					),
					array(
						'title'			=> __( 'Sync Frequency?', 'cartpipe' ),
						'tip'          => __( 'Check this box to have the website product prices be updated with prices from QuickBooks Online', 'cartpipe' ),
						'desc'          => __( 'Please select how frequently the products on the website should be synced with their counterparts in QuickBooks', 'cartpipe' ),
						'id'            => 'qbo[frequency]',
						'default'       => 'yes',
						'type'          => 'select',
						'options'		=> array(
											'14400' => 'Every 4 Hours',
											'43200' => 'Every 12 Hours',
											'86400' => 'Every 24 Hours'
											),
						'autoload'      => false
					),
					array(
						'title'			=> __( 'Sync Price?', 'cartpipe' ),
						'tip'          => __( 'Check this box to have the download\'s prices be updated with prices from QuickBooks Online', 'cartpipe' ),
						'desc'          => __( 'Check this box to have the download\'s prices be updated with prices from QuickBooks Online', 'cartpipe' ),
						'id'            => 'qbo[sync_price]',
						'default'       => 'yes',
						'type'          => 'checkbox',
						'checkboxgroup' => 'end',
						'autoload'      => false
					),
					array(
						'title'			=> __( 'Start Sync?', 'cartpipe' ),
						'label'			=> __('Sync', 'cartpipe'),
						'desc'          => __( 'Click to manually update website products pricing and quantities', 'cartpipe' ),
						//'id'            => 'qbo[sync_stock]',
						'type'          => 'button',
						'url'			=> '#',
						'class'			=> 'button sync',
						'autoload'      => false
					),
					array(
						'title'			=> __( 'Import QuickBooks Items into Easy Digital Downloads', 'cartpipe' ),
						'label'			=> __('Import', 'cartpipe'),
						'desc'          => __( 'Click to import QuickBooks Items into Easy Digital Downloads', 'cartpipe' ),
						//'id'            => 'qbo[sync_stock]',
						'type'          => 'button',
						'url'			=> '#',
						'class'			=> 'button import',
						'autoload'      => false
					),
					array(
						'title'			=> __( 'Export  Downloads into QuickBooks', 'cartpipe' ),
						'label'			=> __('Export', 'cartpipe'),
						'desc'          => __( 'Click to export downloads into QuickBooks', 'cartpipe' ),
						//'id'            => 'qbo[sync_stock]',
						'type'          => 'button',
						'url'			=> '#',
						'class'			=> 'button import',
						'autoload'      => false
					),
					array( 'type' => 'sectionend', 'id' => 'qbo_product_sync'),
	
				));
				}elseif($current_section == 'import_mappings'){
				
				$settings = apply_filters( 'import_mapping_options_settings', array(
					array( 
						'title' => __( 'Import Mapping Options', 'cartpipe' ), 
						'type' => 'title', 
						'desc' => '<p class="desc">Here you can define how the fields for products from QuickBooks Online can be imported as as downloads into Easy Digital Downloads.</p>
									<p class="desc">The column on the left is the Download\'s data field. Map that column to the QuickBooks Online item field on the right. </p>', 
						'id' => 'import_mapping_options' 
					),
					array(
						'title'             => __( 'Download Field Mappings', 'cartpipe' ),
						'desc'              => __( 'Please map a QuickBooks Online product field to a counterpart in Easy Digital Downloads.', 'cartpipe' ),
						'id'                => 'qbo[import_fields]',
						'type'              => 'mapping',
						'options'			=> $qbo_fields,//wc_get_order_statuses(),
						'labels'			=> $edd_fields,
						'auto_create'		=> true, 
						'css'               => '',
						'default'           => '',
						'autoload'          => false
					),
	
					array( 'type' => 'sectionend', 'id' => 'import_mapping_options'),
		
				));
			}elseif($current_section == 'export_mappings'){
				
				$settings = apply_filters( 'qbo_export_mappings_settings', array(
					array( 
						'title' => __( 'Export Product Settings', 'cartpipe' ), 
						'type' => 'title', 
						'desc' => '<p class="desc">Here you can define how the downloards from Easy Digital Downloads can be defined as Items in QuickBooks Online.</p>
									<p class="desc">The column on the left is the Easy Digital Downloads data field. Map that column to the QuickBooks product field on the right.</p>',
						'id' => 'export_mappings_options' 
					),
					array(
						'title'             => __( 'Download Field Mappings', 'cartpipe' ),
						'desc'              => __( 'Please map the a QuickBooks Online product field to a counterpart in Easy Digital Downloads.', 'cartpipe' ),
						'id'                => 'qbo[export_fields]',
						'type'              => 'mapping',
						'options'			=> $edd_fields,//wc_get_order_statuses(),
						'labels'			=> $qbo_fields ,
						'auto_create'		=> true, 
						'css'               => '',
						'default'           => '',
						'autoload'          => false
					),
				array( 'type' => 'sectionend', 'id' => 'export_mappings_options'),
				array( 
						'title' => __( 'Default Account Mappings', 'cartpipe' ), 
						'type' => 'title', 
						'desc' => '<p class="desc">Here you can define the default accounts to use if your plan includes exporting items to QuickBooks.</p>',
						'id' => 'export_account_mappings' 
				),	
				array(
					'title'             => __( 'Income Account', 'cartpipe' ),
					'desc'              => __( 'Please select the Income Account to use for auto-created items in QuickBooks Online.', 'cartpipe' ),
					'id'                => 'qbo[income_account]',
					'type'              => 'select',
					'options'			=> $this->accounts,
					'css'               => '',
					'default'           => '',
					'autoload'          => false
				),
				array(
					'title'             => __( 'Expense Account', 'cartpipe' ),
					'desc'              => __( 'Please select the Expense Account to use for auto-created items in QuickBooks Online.', 'cartpipe' ),
					'id'                => 'qbo[expense_account]',
					'type'              => 'select',
					'options'			=> $this->accounts,
					'css'               => '',
					'default'           => '',
					'autoload'          => false
				),
				array(
					'title'             => __( 'Asset Account', 'cartpipe' ),
					'desc'              => __( 'Please select the Asset Account to use for auto-created items in QuickBooks Online.', 'cartpipe' ),
					'id'                => 'qbo[asset_account]',
					'type'              => 'select',
					'options'			=> $this->accounts,
					'css'               => '',
					'default'           => '',
					'autoload'          => false
				),
				array(
					'title'			=> __( '', 'cartpipe' ),
					'desc'          => __( 'Refresh Accounts?', 'cartpipe' ),
					'label'          => __( 'Refresh', 'cartpipe' ),
					//'id'            => 'qbo[sync_stock]',
					'type'          => 'button',
					'url'			=> '#',
					'data-type'		=> 'accounts',
					'linked'		=> 'qbo[accounts]',
					'class'			=> 'button refresh accounts',
					'autoload'      => false
				),
					array( 'type' => 'sectionend', 'id' => 'export_account_mappings'),
	
				));
				}

		return apply_filters( 'qbo_get_settings_' . $this->id, $settings, $current_section );
	}
}

endif;

return new QBO_Settings_Downloads();
