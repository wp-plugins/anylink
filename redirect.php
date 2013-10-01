<?php
require_once( '../.../../wp-load.php' );
require_once( dirname(__FILE__) . '/config.php' );
require_once( ANYLNK_PATH . '/classes/al_covert.php' );
require_once( ANYLNK_PATH . '/classes/al_filter.php' );
require_once( ANYLNK_PATH . '/classes/al_slug.php' );
require_once( dirname(__FILE__) . '/functions.php' );
require_once( ANYLNK_PATH . '/classes/al_option.php' );
$pluginDir = basename( dirname( __FILE__ ) );
load_plugin_textdomain( 'anylink', false, $pluginDir . '/i18n/' );
$al_slug = $_GET['slug'];
$objSlug = new al_slug();
$link = $objSlug -> getLinkBySlug( $al_slug );
$al_option = get_option( 'anylink_options' );
if( $al_option['redirectType'] != 200 ) {
	_e( "This page is not availabe at the moment!", 'anylink' );
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php _e( "Redirecting ...",'anylink') ?></title>
<style>
#wrap {width:750px; height:150px; margin:0 auto;border:1px solid #fcd;line-height:150px;font-size:2em;padding:5px;}
</style>
<script type="text/javascript">
setInterval( function(){top.location = "<?php echo $link ?>";} , 3000)
</script>
</head>
<body>
<div id="wrap"><?php _e( "Page is redirecting. Please wait...", 'anylink' )?></div>
</body>
</html>