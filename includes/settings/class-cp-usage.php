<?php
/**
 * QBO Product Settings
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'CP_Usage' ) ) :

/**
 * WC_Settings_Products
 */
class CP_Usage extends QBO_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->show_usage 		= get_transient('cp_show_usage');
		if($this->show_usage == 'yes'){
			$this->id    			= 'usage';
			$this->label 			= __( 'CartPipe Usage', 'cartpipe' );
			$this->usage 			= get_transient('cp_chart_usage');
			//var_dump(CP()->client->get_cp_usage());
			 if($this->usage == false){
			 	if(CP()->client):
					$this->usage = CP()->client->get_cp_usage( CP()->qbo->license );
					// var_dump($this->usage);
					set_transient( 'cp_chart_usage', $this->usage, 43200 );
				endif; 
			 }
			
			add_filter( 'qbo_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'qbo_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'qbo_settings_save_' . $this->id, array( $this, 'save' ) );
		}elseif($this->show_usage == false){
			if(CP()->client):
				$this->usage = CP()->client->get_should_show_usage( CP()->qbo->license );
				set_transient( 'cp_show_usage', $this->usage, 43200 );
			endif;
		}
		//add_action( 'qbo_sections_' . $this->id, array( $this, 'output_sections' ) );
	}

	

	/**
	 * Output the settings
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		QBO_Admin_Settings::output_fields( $settings );
		if(isset($this->usage)){
			echo $this->usage;
		}
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
		//if ( $current_section == 'inventory' ) {
			
			
			
			 $settings = apply_filters( 'qbo_sales_settings', array(
// 
				array( 'title' => __( 'CartPipe Data Usage', 'sod-qbo' ), 'type' => 'title', 'desc' => '', 'id' => 'cartpipe_usage' ),
				
				// array(
					// 'title'             => __( 'Order Posting Type', 'sod-qbo' ),
					// 'desc'              => __( 'Please select how you\'d like the order to transfer to QuickBooks Online.', 'sod-qbo' ),
					// 'id'                => 'qbo[order_type]',
					// 'type'              => 'select',
					// 'options'			=> array(
												// 'sales-receipt'	=>	'Sales Receipt',
												// 'invoice'		=>	'Invoice'
											// ),
					// 'css'               => '',
					// 'default'           => '',
					// 'autoload'          => false
				// ),
				// array(
					// 'title'             => __( 'Tax Rate Mappings', 'sod-qbo' ),
					// 'desc'              => __( 'Please map your website tax rates to the corresponding tax rate in QuickBooks Online.', 'sod-qbo' ),
					// 'id'                => 'qbo[taxes]',
					// 'type'              => 'mapping',
					// 'options'			=> $this->taxes,//wc_get_order_statuses(),
					// 'labels'			=> $tax_rates,
					// 'css'               => '',
					// 'default'           => '',
					// 'autoload'          => false
				// ),
				// array(
					// 'title'             => __( 'Tax Code Mappings', 'sod-qbo' ),
					// 'desc'              => __( 'Please map your website tax classes to the corresponding tax code in QuickBooks Online.', 'sod-qbo' ),
					// 'id'                => 'qbo[tax_codes]',
					// 'type'              => 'mapping',
					// 'options'			=> $this->tax_codes,//wc_get_order_statuses(),
					// 'labels'			=> $tax_classes,
					// 'css'               => '',
					// 'default'           => '',
					// 'autoload'          => false
				// ),
				// array(
					// 'title'             => __( 'Payment Method Mappings', 'sod-qbo' ),
					// 'desc'              => __( 'Please map your website payment methods to the corresponding payment methods in QuickBooks Online.', 'sod-qbo' ),
					// 'id'                => 'qbo[payment_methods]',
					// 'type'              => 'mapping',
					// 'options'			=> $this->payment_methods,//wc_get_order_statuses(),
					// 'labels'			=> $wc_payment_methods,
					// 'css'               => '',
					// 'default'           => '',
					// 'autoload'          => false
				// ),
				

				
				array( 'type' => 'sectionend', 'id' => 'cartpipe_usage'),

			));
		//}

		return apply_filters( 'qbo_get_settings_' . $this->id, $settings, $current_section );
	}
	
}

endif;

return new CP_Usage();
