<?php
/*
Plugin Name: anyLink
Plugin URI: http://dudo.org/
Description: anyLink是一款外部链接管理工具。它可以帮你把网站中指向外部的链接以“内部链接”的形式封装。这既可以规避搜索引擎对淘宝客类网站的识别，也可以防止网站权重对外传递，有利于网站SEO。
Version: 0.1
Author: dudo
Author URI: http://dudo.org/
License: GPL2 or later
*/
defined( 'ABSPATH' ) OR exit;
require_once( 'config.php' );
require_once( ANYLNK_PATH . '/classes/al_covert.php' );
require_once( ANYLNK_PATH . '/classes/al_filter.php' );
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
?>