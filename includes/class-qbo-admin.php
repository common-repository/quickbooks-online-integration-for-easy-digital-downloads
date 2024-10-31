<?php
/**
 * WooCommerce Admin.
 *
 * @class 		WC_Admin
 * @author 		WooThemes
 * @category 	Admin
 * @package 	WooCommerce/Admin
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WC_Admin class.
 */
class QBO_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		// Functions
		include_once( 'qbo-admin-settings.php' );
		// Classes we only need during non-ajax requests
		if ( ! is_ajax() ) {
			//include( 'class-wc-admin-menus.php' );
			
		}

		
	}

	

	

}

return new QBO_Admin();
