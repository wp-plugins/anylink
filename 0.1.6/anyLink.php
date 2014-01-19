<?php
/**
Plugin Name: anyLink
Plugin URI: http://dudo.org/anylink
Description: anyLink is an external links management tool. It help you to covert all the external links in your posts into internal links automatically. It can prevent the website weight flow outside to others. It's absolutely SEO friendly.
Version: 0.1.6
Author: dudo
Author URI: http://dudo.org/about
License: GPL2 or later
*/
defined( 'ABSPATH' ) OR exit;
require_once( 'config.php' );
require_once( ANYLNK_PATH . '/classes/al_covert.php' );
require_once( ANYLNK_PATH . '/classes/al_filter.php' );
require_once( ANYLNK_PATH . '/classes/al_slug.php' );
require_once( ANYLNK_PATH . '/functions.php' );
require_once( ANYLNK_PATH . '/classes/al_option.php' );

$filter = new al_filter();
$alOption = new al_option();

register_activation_hook( __FILE__, 'anylnkInstall' );
add_action( 'transition_post_status', 'post_published', 10, 3 );
add_action( 'wp_loaded','checkFlush' );
add_filter( 'query_vars', array( $filter, 'addQueryVars' ) );
add_action( 'parse_request', array( &$filter, 'alter_the_query' ) );
add_action( 'plugins_loaded', 'al_load_textdomain' );
add_filter( 'the_content', 'filterByType' );
add_filter( 'rewrite_rules_array','anylink_rewrite_rules' );

/**
 * Check rewrite rules and flush
 *
 * Checking if rewrite rules are included, if not we should re-flush it;
 *
 * @see http://codex.wordpress.org/Class_Reference/WP_Rewrite#Examples
 * @since version 0.1.5
 */
function checkFlush() {
	$rules = get_option( 'rewrite_rules' );
	$alOption = get_option( 'anylink_options' );
	$cat = $alOption['redirectCat'];
	if( ! isset( $rules[$cat . '/([0-9a-z]{4,})/?$'] ) ){
		global $wp_rewrite;
		$wp_rewrite -> flush_rules();
	}
}

//add rewrite rules
function anylink_rewrite_rules( $rules ) {
	$alOption = get_option( 'anylink_options' );
	$cat = $alOption['redirectCat'];
	$newrules = array();
	$newrules[$cat . '/([0-9a-z]{4,})/?$'] = 'index.php?' . $cat . '=$matches[1]';
	return $newrules + $rules;
}

/**
 * This function is to replace old ACTTION hook 'publish_post'
 *
 * This change can filter and covert all post types besides
 * post itself.
 *
 * @param string $newStatus the new status of a post
 * @param string $oldStatus the old status of a post, if new to publish, it's new
 * @param string $post the post to add action
 *
 * @since version 0.1.4
 */ 
function post_published( $newStatus, $oldStatus, $post) {
	if( $newStatus == 'publish' ) {
		$covert = new al_covert();
		$covert -> covertURLs( $post -> ID );
	}
}

/**
 * This function is to filter the post whose post type is specified
 *
 * Anylink covert all external links at all time, 
 * but only filter the specified ones, the benefit of this is you needn't
 * regenerate slug any time you changed the post type
 *
 * @param object $content provided by hook
 * @return object $content
 *
 * @since version 0.1.4
 */
function filterByType( $content ) {
	$type  = get_post_type();
	$types = get_option( 'anylink_options' );
	$types = $types['postType'];
	if( empty( $types ) ){
		return $content;
	}
	if( ( is_string( $types ) && ( $types == $type ) ) || ( is_array( $types ) && array_search( $type, $types ) !== false ) ){
		$filter = new al_filter();
		return $filter -> applyFilter( $content );
		
	} else {
		return $content;
	}
}
?>