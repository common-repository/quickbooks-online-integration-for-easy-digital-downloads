<?php
/**
 * QBO Product Settings
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'QBO_Settings_Credentials' ) ) :

/**
 * WC_Settings_Products
 */
class QBO_Settings_Credentials extends QBO_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'credentials';
		$this->label = __( 'Cartpipe Settings', 'cartpipe' );

		add_filter( 'qbo_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'qbo_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'qbo_settings_save_' . $this->id, array( $this, 'save' ) );
		
		//add_action( 'qbo_sections_' . $this->id, array( $this, 'output_sections' ) );
		if(!CP()->qbo->license_info->status):
			//CP()->qbo->license_info = var_dump( CP()->client->check_service( $this->qbo->license, get_home_url()) );
			
		endif;
		
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
			
			// if(!CP()->qbo->license_info->status){
				// if(!CP()->client){
					// CP()->init_client();
				// }
				// CP()->client->check_service(CP()->qbo->license, get_home_url());
			// }
			//var_dump( date("Y-m-d H:i:s"));
			
			switch (CP()->qbo->license_info->status ){//CP()->qbo->license_info->status) {
				case 'site_inactive':
					if(CP()->qbo->license_info->activated_sites == 'none_activated'){
						$buttons = array( 
								array(
									'url'=> '#', 
									'class'=>'button activate',
									'label'=>'Activate',
								)
							);
					}
					if(CP()->qbo->license_info->free_trial_activated == 'yes'):
						$status = sprintf('You\'ve already activated a free trial on %s. If you\'d like to continue using cartpipe, signup for one of our services <a href="%s">here</a>.', get_home_url(), CP_URL );
					endif;
					break;
				case 'inactive':
					$buttons = array( 
									array(
										'url'=> '#', 
										'class'=>'button activate',
										'label'=>'Activate',
									)
								);
					$status = sprintf('Please activate Cartpipe for QuickBooks Online for %s. Your license expires %s.', get_home_url(), CP()->qbo->license_info->expires );		
					break;
				case 'expired':
					if(CP()->qbo->license_info->free_trial == 'yes'){
						$status = sprintf('You\'re free trial for Cartpipe has expired. If you\'d like to continue using cartpipe.com, please update your license here - <a href="%s">%s</a>', CP()->qbo->license_info->expires, CP_URL, CP_URL );
					}else{
						$status = sprintf('Your license expired on %s. If you\'d like to continue using cartpipe.com, please update your license here -<a href="%s">%s</a>', CP()->qbo->license_info->expires, CP_URL, CP_URL  );
					}
					$buttons = array();		
					break;
				case 'valid':
					
					if(CP()->qbo->license_info->activated_sites == 'none_activated'){
						$buttons = array( 
								array(
									'url'=> '#', 
									'class'=>'button activate',
									'label'=>'Activate',
								)
							);
						$status = sprintf('Please activate Cartpipe for QuickBooks Online for %s. Your license expires %s.', get_home_url(), CP()->qbo->license_info->expires );
					}else{
						$buttons = array( 
									array(
										'url'=> '#', 
										'class'=>'button deactivate',
										'label'=>'Deactivate',
									),
								);
						$status = sprintf('Cartpipe for QuickBooks Online has been activated for %s. Your license expires %s.', get_home_url(), CP()->qbo->license_info->expires );
					}
					break;
				case 'deactivated':
					$status = sprintf('Cartpipe for QuickBooks Online has been deactivated for %s. Please check your license and activate. Your license expires %s.', get_home_url(), CP()->qbo->license_info->expires );
					break;
				case 'invalid':
					if( CP()->qbo->license_info->expires !='1970-01-01 00:00:00'){
						$status = sprintf('Cartpipe for QuickBooks Online could not be activated for %s. Your license expired %s.', get_home_url(), CP()->qbo->license_info->expires );	
					}else{
						$status = sprintf('Cartpipe for QuickBooks Online could not be activated for %s because your license is invalid.', get_home_url() );
					}
					
					break;
				default:
					$buttons = array( 
									array(
										'url'=> '#', 
										'class'=>'button activate',
										'label'=>'Activate',
									)
								);
					$status = sprintf('Please activate Cartpipe for QuickBooks Online for %s. Your license expires %s.', get_home_url(), CP()->qbo->license_info->expires );
					break;
			}
			
			$settings = apply_filters( 'qbo_inventory_settings', array(

				array( 'title' => __( 'CartPipe Credentials', 'cartpipe' ), 'type' => 'title', 'desc' => '', 'id' => 'credentials_options' ),
				array(
					'title'             => __( 'Consumer Key', 'cartpipe' ),
					'desc'              => __( 'Enter the consumer key you received when signing up for the QuickBooks Online Integration.', 'cartpipe' ),
					'id'                => 'qbo[consumer_key]',
					'type'              => 'text',
					'css'               => '',
					'default'           => '',
					'autoload'          => false
				),
				array(
					'title'             => __( 'Consumer Secret', 'cartpipe' ),
					'desc'              => __( 'Enter the consumer secret you received when signing up for the QuickBooks Online Integration.', 'cartpipe' ),
					'id'                => 'qbo[consumer_secret]',
					'type'              => 'text',
					'css'               => '',
					'default'           => '',
					'autoload'          => false
				),
				array(
					'title'             => __( 'CartPipe License Key', 'cartpipe' ),
					'desc'              => __( 'Enter your license key for the CartPipe / QuickBooks Online Integration. Entering your license key will activate the cartpipe service for this site.', 'cartpipe' ),
					'id'                => 'qbo[license]',
					'type'              => 'text',
					'css'               => '',
					'default'           => '',
					'autoload'          => false
				),
				array(
					'title'             => __( 'Your Site', 'cartpipe' ),
					'desc'              => __( 'This is the site url that will be activated with cartpipe when the license is entered. This is a non-editable field', 'cartpipe' ),
					'id'                => 'qbo[site]',
					'type'              => 'text',
					'css'               => 'disabled',
					'disabled'			=>	false, 
					'default'           => get_home_url(),
					'autoload'          => false
				),
				array(
					'title'             => __( 'CartPipe API Url', 'cartpipe' ),
					'desc'              => __( 'This is a non-editable field.', 'cartpipe' ),
					'id'                => 'qbo[api]',
					'type'              => 'text',
					'css'               => '',
					'disabled'			=>	false,
					'default'           => CP_API,
					'autoload'          => false
				),
				

				
				array( 'type' => 'sectionend', 'id' => 'credentials_options'),
				
				array( 'title' => __( 'CartPipe Notifications', 'cartpipe' ), 'type' => 'title', 'desc' => '', 'id' => 'cp_notifications' ),
					array(
						'title'             => __( 'Enabled Messages?', 'cartpipe' ),
						'tip'              => __( 'Check here to enable / disable notifications from cartpipe regarding data usage and sync status', 'cartpipe' ),
						'desc'              => __( 'Check here to enable / disable notifications from cartpipe regarding data usage and sync status on the queue, products and orders pages.', 'cartpipe' ),
						'id'                => 'qbo[notifications]',
						'type'              => 'checkbox',
						'css'               => '',
						'checkboxgroup' => 'end',
						'default'           => 'yes',
						'autoload'          => false
					),
				array( 'type' => 'sectionend', 'id' => 'cp_notifications'),
				array( 'title' => __( 'CartPipe License Info', 'cartpipe' ), 'type' => 'title', 'desc' => '', 'id' => 'cp_license_info' ),
					array(
						'title'             => __( 'License for ' . get_home_url(), 'cartpipe' ),
						'desc'              => __( $status ),
						'id'                => '',
						'type'          	=> 'button_array',
						'buttons'			=> $buttons,
						'default'           => '',
						'autoload'          => false
					),
				array(
						'title'             => __( 'Your Subscribtion', 'cartpipe' ),
						'desc'              => __( 'Your current cartpipe subscription level ' ),
						'features'			=> CP()->qbo->license_info->caps,
						'cp_name'			=> CP()->qbo->license_info->name,
						'id'                => '',
						'type'          	=> 'content',
						'default'           => '',
						'autoload'          => false
					),
				array( 'type' => 'sectionend', 'id' => 'cp_license_info'),
				array( 'title' => __( 'Uninstall Options', 'cartpipe' ), 'type' => 'title', 'desc' => '', 'id' => 'uninstall_options' ),
				array(
						'title'             => __( 'Delete Data on Uninstall?', 'cartpipe' ),
						'tip'              	=> __( 'Check here delete Cartpipe and QuickBooks Online data when the plugin is uninstalled', 'cartpipe' ),
						'desc'              => __( 'Check here delete Cartpipe and QuickBooks Online data when the plugin is uninstalled', 'cartpipe' ),
						'id'                => 'qbo[delete_uninstall]',
						'type'              => 'checkbox',
						'css'               => '',
						'checkboxgroup' 	=> 'end',
						'default'           => 'no',
						'autoload'          => false
					),
				array( 'type' => 'sectionend', 'id' => 'uninstall_options'),
			));
			
		//}

		return apply_filters( 'qbo_get_settings_' . $this->id, $settings, $current_section );
	}
}

endif;

return new QBO_Settings_Credentials();
