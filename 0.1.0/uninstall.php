<?php
	defined( 'ABSPATH' ) OR exit;
	if ( ! current_user_can( 'activate_plugins' ) )
        return;
	global $wpdb;
	$wpdb -> query ( $wpdb -> prepare( 
			"DROP TABLE  wp_al_urls, wp_al_urls_index", '' ) );
	delete_option('anylink_options');
	flush_rewrite_rules();
?>