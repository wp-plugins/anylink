<?php
defined( 'ABSPATH' ) OR exit;
function indexOf( $substr, $str ) {
	if( strpos( $str, $substr ) === 0 )
		return true;
	else
		return false;
}
function index2Of( $str1, $str2 ) {
	if( indexOf( $str1, $str2 ) || indexOf( $str2, $str1 ) )
		return true;
	else
		return false;
}
//Install
function anylnkInstall() {
	global $wpdb;
	//create tables
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$charset_collate = '';
	if( $wpdb->has_cap( 'collation' ) ){
		if( ! empty( $wpdb->charset ) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if( ! empty( $wpdb->collate ) )
		$charset_collate .= " COLLATE $wpdb -> collate";
	}
	$sqlAnylnk = "CREATE TABLE " . ANYLNK_DBTB . " (
		al_id mediumint(8) NOT NULL AUTO_INCREMENT,
		al_slug varchar(255) NOT NULL,
		al_crtime datetime NOT NULL,
		al_origURL text NOT NULL,
		al_count mediumint(8) DEFAULT 0 NOT NULL,
		al_isAuto boolean DEFAULT 1,
		PRIMARY KEY  (al_id),
		KEY al_count (al_count),
		KEY al_slug (al_slug)
	) {$charset_collate};";
	$sqlIndex = "CREATE TABLE " . ANYLNK_DBINDEX. " (
		al_index_id mediumint(8) NOT NULL AUTO_INCREMENT,
		al_url_id mediumint(8) NOT NULL,
		al_post_id mediumint(8) NOT NULL,
		al_comm_id mediumint(8) DEFAULT 0 NOT NULL,
		PRIMARY KEY  (al_index_id),
		KEY al_url_id (al_url_id),
		KEY al_post_id (al_post_id),
		KEY al_comm_id (al_comm_id)
	) {$charset_collate};";
	dbDelta( $sqlAnylnk );
	dbDelta( $sqlIndex );
	//add options
	if( ! get_option( 'anylink_options' ) ) {
		add_option( 'anylink_options', 
			array( 
					'version' => 14,
					'redirectCat' => 'goto', 
					'oldCat' => 'goto',
					'redirectType' => '307',
					'oldRedirectType' => '307',
					'slugNum' => '4',
					'oldSlugNum' => '4',
					'slugChar' => '2',
					'oldSlugChar' => '2',
					'postType' => array(
										'post',
										'page',
										),
					),
			'', 'no' );
		//add and flush rewrite rule
		add_rewrite_rule( "goto/([0-9a-z]{4,})", 'index.php?goto=$matches[1]', 'top' );
		flush_rewrite_rules();
	} else {
		/* update option */
		$al_option = get_option( 'anylink_options' );
		if( ! isset( $al_option['version'] ) ) {
			$al_option['version'] = '0.12';
			$al_option['oldRedirectType'] = $al_option['redirectType'];
		} 
		if ( $al_option['version'] < 14 ) {
			$al_option['postType'] = array( 'post', 'page' );
		}
		$al_option['version'] = 14;
		update_option( 'anylink_options', $al_option );
	}		
}
function al_load_textdomain() {
	$pluginDir = basename( dirname( __FILE__ ) );
	load_plugin_textdomain( 'anylink', false, $pluginDir . '/i18n' );
}
?>