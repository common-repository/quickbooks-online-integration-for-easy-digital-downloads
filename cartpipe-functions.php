<?php
	function cartpipe_request( $slug, $name = '', $params='' ) {
		$template = false;
		
		// Look in yourtheme/slug-name.php and yourtheme/woocommerce/slug-name.php
		if ( $name )
			$template = locate_template( array ( "/qbo/{$slug}-{$name}.php" ) );
	// 		
		// Get default slug-name.php
		if ( !$template && $name && file_exists( cartpipe_plugin_path() . "/requests/{$slug}-{$name}.php" ) )
			$template = cartpipe_plugin_path() . "/requests/{$slug}-{$name}.php";
		
		// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/woocommerce/slug.php
		if ( !$template )
			$template = locate_template( array ( "{$slug}.php" ) );
		
		if ( $template ){
			$template = load_cartpipe_template( $template, $params );
			return $template;
		}else{
			return false;
		}
	}
	function cartpipe_plugin_path() {
	  // gets the absolute path to this plugin directory
	  return untrailingslashit( plugin_dir_path( __FILE__ ) );
	 
	}
	function load_cartpipe_template($template, $params){
		extract($params);
		$template = include_once( $template );
		return $template;
	}
	function cptexturize($string){
		$new_string = str_replace(array('{', '}', '-'), '', $string);
		return $new_string;
	}
	function cpencode($data){
		foreach($data as $key=>$value){
			if(is_array($value)){
				foreach($value as $v_key=> $v_value){
					if(!is_array($v_value)){
						$value[$v_key] = htmlspecialchars( $v_value, ENT_QUOTES );
					}
				}
			}else{
				$data[$key] = htmlspecialchars( $value , ENT_QUOTES);	
			}
		}
		return $data;
	}
function cp_download_has_unique_sku( $product_id, $sku ) {
	global $wpdb;
 
     $sku_found = $wpdb->get_var( $wpdb->prepare( "
     SELECT $wpdb->posts.ID
     FROM $wpdb->posts
     LEFT JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
     WHERE $wpdb->posts.post_type IN ( 'download' )
     AND $wpdb->posts.post_status = 'publish'
     AND $wpdb->postmeta.meta_key = 'edd_sku' AND $wpdb->postmeta.meta_value = '%s'
    AND $wpdb->postmeta.post_id <> %d LIMIT 1
  ", $sku, $product_id ) );
 
    if ( $sku_found ) {
         return false;
    } else {
         return true;
	}
}
