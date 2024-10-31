<?php
/**
 * QBO Product Settings
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'QBO_Settings_Sales' ) ) :

/**
 * WC_Settings_Products
 */
class QBO_Settings_Sales extends QBO_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    			= 'payments';
		$this->label 			= __( 'Payments', 'cartpipe' );
		$taxes 					= get_option('qbo_sales_tax_info', false);
		if(isset($taxes->errors) || $taxes == '1' || !$taxes){
			delete_option('qbo_sales_tax_info');
		}
		
		$codes 				 	= get_option('qbo_sales_tax_codes', false);
		if(isset($codes->errors) || $codes == '1' || !$codes){
			delete_option('qbo_sales_tax_codes');
		}
		$payments 			 	= get_option('qbo_payment_methods', false);
		
		if(isset($payments->errors) || $payments == '1' || !$payments){
			
			delete_option('qbo_payment_methods');
		}
		
		$this->taxes 			= isset( $taxes ) && $taxes != '' && $taxes ? $taxes : CP()->needs['tax_rates'] = true;
		$this->tax_codes		= isset( $codes ) && $codes != '' && $codes ? $codes : CP()->needs['tax_codes'] = true ;
		$this->payment_methods	= isset( $payments ) && $payments != '' && $payments ? $payments : CP()->needs['payment_methods'] = true;
		
		if(sizeof(CP()->needs) > 0){
	    	foreach(CP()->needs as $key => $need){
	    		switch ($key) {
					case 'tax_rates':
						if($need){
							if(CP()->client):
								$this->taxes  = CP()->client->qbo_get_sales_tax_info( CP()->qbo->license ) ;
								update_option( 'qbo_sales_tax_info' , $this->taxes );
								$need = false;
							endif;
						}
						break;
					case 'tax_codes':
						if($need){
							if(CP()->client):
								$this->tax_codes = CP()->client->qbo_get_sales_tax_codes( CP()->qbo->license );
								update_option('qbo_sales_tax_codes', $this->tax_codes);
								$need = false;
							endif;
						}
						break;
					case 'payment_methods':
						if($need){
							if(CP()->client):
								$this->payment_methods = CP()->client->qbo_get_payment_methods( CP()->qbo->license );
								update_option('qbo_payment_methods', $this->payment_methods);
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
		//add_action( 'qbo_sections_' . $this->id, array( $this, 'output_sections' ) );
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
		//if ( $current_section == 'inventory' ) {
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
								if(CP()->client):
									$this->accounts  = CP()->client->qbo_get_accounts( CP()->qbo->license );
									if(sizeof($this->accounts) > 0 ){
										update_option( 'qbo_accounts' , $this->accounts );
										$need = false;
									}
								endif;
							}
							break;
					}
		    	}
			}
			
		
			$settings = apply_filters( 'qbo_sales_settings', 	$this->get_country_settings( edd_get_shop_country() ));
		//}

		return apply_filters( 'qbo_get_settings_' . $this->id, $settings, $current_section );
	}
	public function get_tax_rates(){
		$return = null;
		$tax_rates = edd_get_tax_rates(); 
		if($tax_rates){
			foreach($tax_rates as $key=>$rate){
				$state 										= isset($rate['state']) ? $rate['state'] : '';
				$country									= isset($rate['country']) ? $rate['country'] : '';
				$rate										= isset($rate['rate']) ? $rate['rate'] : 0;
				$return[strtolower($country . '_' . $state)] = sprintf('%s - %s (%s)', $country, $state, $rate.'%');
			}
		}
		return $return;
	}
	
	public function get_payment_methods(){
		$gateways 	= edd_get_enabled_payment_gateways( );
		$return 	= array();
		if($gateways){
		 	foreach ($gateways as $key=>$value){
				$return[$key] = $value['checkout_label'];
			} 
		}
		return $return;
	}
	public function get_country_settings( $country ){
		$tax_rates 				= self::get_tax_rates();
		$payment_methods		= self::get_payment_methods();
		switch ($country) {
			case 'US':
				$return = array(

				array( 'title' => __( 'QuickBooks Online Order Settings', 'cartpipe' ), 'type' => 'title', 'desc' => '', 'id' => 'qbo_orders' ),
				array(
					'title'             => __( 'Payment Status Trigger', 'cartpipe' ),
					'desc'              => __( 'Please select the Easy Digital Downloads payment status that will trigger the order to be sent to QuickBooks Online.', 'cartpipe' ),
					'id'                => 'qbo[order_trigger]',
					'type'              => 'select',
					'options'			=> edd_get_payment_statuses(),
					'css'               => '',
					'default'           => '',
					'autoload'          => false
				),
				array(
					'title'             => __( 'Payment Posting Type', 'cartpipe' ),
					'desc'              => __( 'Please select how you\'d like the Easy Digital Downloads payment to transfer to QuickBooks Online.', 'cartpipe' ),
					'id'                => 'qbo[order_type]',
					'type'              => 'select',
					'options'			=> array(
												'sales-receipt'	=>	'Sales Receipt',
												'invoice'		=>	'Invoice'
											),
					'css'               => '',
					'default'           => '',
					'autoload'          => false
				),
				array(
					'title'             => __( 'Create Payment in QuickBooks?', 'cartpipe' ),
					'desc'              => __( 'Would you like to receive a payment on account in QuickBooks once the payment has reached a \'completed\' status on the website', 'cartpipe' ),
					'id'                => 'qbo[create_payment]',
					'type'              => 'checkbox',
					'css'               => '',
					'default'           => '',
					'dependency'		=> array(
											'setting'	=> 'qbo[order_type]',
											'value'		=> 'invoice'
										),
					'autoload'          => false
				),
				array(
					'title'             => __( 'Deposit Account', 'cartpipe' ),
					'desc'              => __( 'Please select the Deposit Account to use for sales receipts and receipt of payments in QuickBooks Online.', 'cartpipe' ),
					'id'                => 'qbo[deposit_account]',
					'type'              => 'select',
					'options'			=> $this->accounts,
					'css'               => '',
					'default'           => '',
					'autoload'          => false
				),
					array(
					'title'			=> __( '', 'cartpipe' ),
					'desc'          => __( 'Refresh Accounts?', 'cartpipe' ),
					'label'			 => __( 'Refresh', 'cartpipe' ),
					//'id'            => 'qbo[sync_stock]',
					'type'          => 'button',
					'url'			=> '#',
					'data-type'		=> 'accounts',
					'linked'		=> 'qbo[deposit_account]',
					'class'			=> 'button refresh accounts	',
					'autoload'      => false
				),
				array(
					'title'             => __( 'Tax Rate Mappings', 'cartpipe' ),
					'desc'              => __( 'Please map your website tax rates to the corresponding tax rate in QuickBooks Online.', 'cartpipe' ),
					'id'                => 'qbo[taxes]',
					'type'              => 'mapping',
					'options'			=> $this->taxes,//wc_get_order_statuses(),
					'labels'			=> $tax_rates,
					'auto_create'		=> true, 
					'css'               => '',
					'default'           => '',
					'autoload'          => false
				),
					array(
					'title'			=> __( '', 'cartpipe' ),
					'desc'          => __( 'Refresh Tax Rates?', 'cartpipe' ),
					'label'			 => __( 'Refresh', 'cartpipe' ),
					//'id'            => 'qbo[sync_stock]',
					'type'          => 'button',
					'url'			=> '#',
					'data-type'		=> 'taxrates',
					'linked'		=> 'qbo[taxes]',
					'class'			=> 'button refresh taxrates',
					'autoload'      => false
				),
				array(
					'title'             => __( 'Payment Method Mappings', 'cartpipe' ),
					'desc'              => __( 'Please map your website payment methods to the corresponding payment methods in QuickBooks Online.', 'cartpipe' ),
					'id'                => 'qbo[payment_methods]',
					'type'              => 'mapping',
					'options'			=> $this->payment_methods,//wc_get_order_statuses(),
					'labels'			=> $payment_methods,
					'css'               => '',
					'default'           => '',
					'autoload'          => false
				),
				array(
					'title'			=> __( '', 'cartpipe' ),
					'desc'          => __( 'Refresh Payment Methods?', 'cartpipe' ),
					'label'          => __( 'Refresh', 'cartpipe' ),
					//'id'            => 'qbo[sync_stock]',
					'type'          => 'button',
					'url'			=> '#',
					'data-type'		=> 'payments',
					'linked'		=> 'qbo[payment_methods]',
					'class'			=> 'button refresh payments',
					'autoload'      => false
				),

				
				array( 'type' => 'sectionend', 'id' => 'qbo_orders'),

			);
				break;
			case 'CA':
				$return = array(

				array( 'title' => __( 'QuickBooks Online Order Settings', 'cartpipe' ), 'type' => 'title', 'desc' => '', 'id' => 'qbo_orders' ),
				array(
					'title'             => __( 'Payment Status Trigger', 'cartpipe' ),
					'desc'              => __( 'Please select the Easy Digital Downloads payment status that will trigger the order to be sent to QuickBooks Online.', 'cartpipe' ),
					'id'                => 'qbo[order_trigger]',
					'type'              => 'select',
					'options'			=> edd_get_payment_statuses(),
					'css'               => '',
					'default'           => '',
					'autoload'          => false
				),
				array(
					'title'             => __( 'Payment Posting Type', 'cartpipe' ),
					'desc'              => __( 'Please select how you\'d like the Easy Digital Downloads payment to transfer to QuickBooks Online.', 'cartpipe' ),
					'id'                => 'qbo[order_type]',
					'type'              => 'select',
					'options'			=> array(
												'sales-receipt'	=>	'Sales Receipt',
												'invoice'		=>	'Invoice'
											),
					'css'               => '',
					'default'           => '',
					'autoload'          => false
				),
				array(
					'title'             => __( 'Create Payment in QuickBooks?', 'cartpipe' ),
					'desc'              => __( 'Would you like to receive a payment on account in QuickBooks once the payment has reached a \'completed\' status on the website', 'cartpipe' ),
					'id'                => 'qbo[create_payment]',
					'type'              => 'checkbox',
					'css'               => '',
					'default'           => '',
					'dependency'		=> array(
											'setting'	=> 'qbo[order_type]',
											'value'		=> 'invoice'
										),
					'autoload'          => false
				),
				array(
					'title'             => __( 'Deposit Account', 'cartpipe' ),
					'desc'              => __( 'Please select the Deposit Account to use for sales receipts and receipt of payments in QuickBooks Online.', 'cartpipe' ),
					'id'                => 'qbo[deposit_account]',
					'type'              => 'select',
					'options'			=> $this->accounts,
					'css'               => '',
					'default'           => '',
					'autoload'          => false
				),
					array(
					'title'			=> __( '', 'cartpipe' ),
					'desc'          => __( 'Refresh Accounts?', 'cartpipe' ),
					'label'			 => __( 'Refresh', 'cartpipe' ),
					//'id'            => 'qbo[sync_stock]',
					'type'          => 'button',
					'url'			=> '#',
					'data-type'		=> 'accounts',
					'linked'		=> 'qbo[deposit_account]',
					'class'			=> 'button refresh accounts	',
					'autoload'      => false
				),
				array(
					'title'             => __( 'Tax Rate Mappings', 'cartpipe' ),
					'desc'              => __( 'Please map your website tax rates to the corresponding tax rate in QuickBooks Online.', 'cartpipe' ),
					'id'                => 'qbo[taxes]',
					'type'              => 'mapping',
					'options'			=> $this->taxes,//wc_get_order_statuses(),
					'labels'			=> $tax_rates,
					'auto_create'		=> true, 
					'css'               => '',
					'default'           => '',
					'autoload'          => false
				),
					array(
					'title'			=> __( '', 'cartpipe' ),
					'desc'          => __( 'Refresh Tax Rates?', 'cartpipe' ),
					'label'			 => __( 'Refresh', 'cartpipe' ),
					//'id'            => 'qbo[sync_stock]',
					'type'          => 'button',
					'url'			=> '#',
					'data-type'		=> 'taxrates',
					'linked'		=> 'qbo[taxes]',
					'class'			=> 'button refresh taxrates',
					'autoload'      => false
				),
				array(
						'title'             => __( 'Exempt Sales Tax Rate Mapping', 'cartpipe' ),
						'desc'              => __( 'Please map the QuickBooks Sales Tax Rate to use when tax wasn\'t collected, i.e. for foreign orders.', 'cartpipe' ),
						'id'                => 'qbo[zero_tax_code]',
						'type'              => 'select',
						'options'			=> $this->taxes,//wc_get_order_statuses(),
						'css'               => '',
						'default'           => '',
						'autoload'          => false
				),
						array(
						'title'			=> __( '', 'cartpipe' ),
						'desc'          => __( 'Refresh Tax Codes?', 'cartpipe' ),
						'label'         => __( 'Refresh', 'cartpipe' ),
						//'id'            => 'qbo[sync_stock]',
						'type'          => 'button',
						'url'			=> '#',
						'data-type'		=> 'taxcodes',
						'linked'		=> 'qbo[zero_tax_code]',
						'class'			=> 'button refresh taxcodes',
						'autoload'      => false
				),
				array(
						'title'             => __( 'In-Country Shipping Item Tax Code Mapping', 'cartpipe' ),
						'desc'              => __( 'Please map your shipping item tax code to the corresponding tax code in QuickBooks Online.', 'cartpipe' ),
						'id'                => 'qbo[shipping_item_taxcode]',
						'type'              => 'select',
						'options'			=> $this->tax_codes,//wc_get_order_statuses(),
						'css'               => '',
						'default'           => '',
						'autoload'          => false
				),
						array(
						'title'			=> __( '', 'cartpipe' ),
						'desc'          => __( 'Refresh Shipping Item Tax Codes?', 'cartpipe' ),
						'label'         => __( 'Refresh', 'cartpipe' ),
						//'id'            => 'qbo[sync_stock]',
						'type'          => 'button',
						'url'			=> '#',
						'data-type'		=> 'taxcodes',
						'linked'		=> 'qbo[shipping_item_taxcode]',
						'class'			=> 'button refresh taxcodes',
						'autoload'      => false
				),
				array(
						'title'             => __( 'Foreign Country Shipping Item Tax Code Mapping', 'cartpipe' ),
						'desc'              => __( 'Please map your shipping item tax code to the corresponding tax code in QuickBooks Online.', 'cartpipe' ),
						'id'                => 'qbo[foreign_shipping_item_taxcode]',
						'type'              => 'select',
						'options'			=> $this->tax_codes,//wc_get_order_statuses(),
						'css'               => '',
						'default'           => '',
						'autoload'          => false
				),
						array(
						'title'			=> __( '', 'cartpipe' ),
						'desc'          => __( 'Refresh Shipping Item Tax Codes?', 'cartpipe' ),
						'label'         => __( 'Refresh', 'cartpipe' ),
						//'id'            => 'qbo[sync_stock]',
						'type'          => 'button',
						'url'			=> '#',
						'data-type'		=> 'taxcodes',
						'linked'		=> 'qbo[foreign_shipping_item_taxcode]',
						'class'			=> 'button refresh taxcodes',
						'autoload'      => false
				),
				array(
					'title'             => __( 'Payment Method Mappings', 'cartpipe' ),
					'desc'              => __( 'Please map your website payment methods to the corresponding payment methods in QuickBooks Online.', 'cartpipe' ),
					'id'                => 'qbo[payment_methods]',
					'type'              => 'mapping',
					'options'			=> $this->payment_methods,//wc_get_order_statuses(),
					'labels'			=> $payment_methods,
					'css'               => '',
					'default'           => '',
					'autoload'          => false
				),
				array(
					'title'			=> __( '', 'cartpipe' ),
					'desc'          => __( 'Refresh Payment Methods?', 'cartpipe' ),
					'label'          => __( 'Refresh', 'cartpipe' ),
					//'id'            => 'qbo[sync_stock]',
					'type'          => 'button',
					'url'			=> '#',
					'data-type'		=> 'payments',
					'linked'		=> 'qbo[payment_methods]',
					'class'			=> 'button refresh payments',
					'autoload'      => false
				),

				
				array( 'type' => 'sectionend', 'id' => 'qbo_orders'),

			);
				break;
		}
		return $return;	
	}
}

endif;

return new QBO_Settings_Sales();
