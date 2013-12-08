<?php
/*
Plugin Name: anyLink
Plugin URI: http://dudo.org/anylink
Description: anyLink is an external links management tool. It help you to covert all the external links in your posts into internal links automatically. It can prevent the website weight flow outside to others. It's absolutely SEO friendly.
Version: 0.1.3
Author: dudo
Author URI: http://dudo.org/about
License: GPL2 or later
*/
defined( 'ABSPATH' ) OR exit;
require_once( 'config.php' );
require_once( ANYLNK_PATH . '/classes/al_covert.php' );
require_once( ANYLNK_PATH . '/classes/al_filter.php' );
require_once( ANYLNK_PATH . '/classes/al_slug.php' );
require_once( 'functions.php' );
require_once( ANYLNK_PATH . '/classes/al_option.php' );
register_activation_hook( __FILE__, 'anylnkInstall' );
$covert = new al_covert();
$filter = new al_filter();
$alOption = new al_option();
add_action( 'publish_post', array( &$covert, 'covertURLs' ) );
add_filter( 'the_content',  array( &$filter, 'applyFilter' ) );
add_filter('query_vars', array( &$filter, 'addQueryVars' ) );
add_action( 'parse_request', array( &$filter, 'alter_the_query' ) );
add_action( 'plugins_loaded', 'al_load_textdomain' );
?>