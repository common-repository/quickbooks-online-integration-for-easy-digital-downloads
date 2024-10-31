<?php
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) 
	exit();
$options 	= get_option('qbo') ? maybe_unserialize( get_option('qbo') ) : array();
if(isset($options['delete_uninstall'])){
	if($options['delete_uninstall'] == 'yes'){
		global $wpdb;
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'qbo_%';" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'qbo';" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_qbo_%';" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_qb_%';" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'cp_%';" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_cp_%';" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_quickbooks_%';" );
		$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ( 'cp_queue', 'cp_fallout' );" );
		$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );
		$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta WHERE meta.meta_key = '_quickbooks_data'" );
		$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta WHERE meta.meta_key = '_cp_is_queued'" );
	}
}