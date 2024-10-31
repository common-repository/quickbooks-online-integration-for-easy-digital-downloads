<?php 
	$service 			= CP()->client->check_service( CP()->qbo->license, get_home_url() );
	$qbo 				= maybe_unserialize( get_option('qbo') );
	$qbo['license_info']	= $service;
	update_option('qbo', $qbo);
	wp_set_object_terms( $query->post->ID , 'success', 'queue_status'. false );