<?php
defined( 'ABSPATH' ) OR exit;
?>
<div class="wrap">
	<div id="icon-link-manager" class="icon32"></div><h2>anyLink 插件配置</h2>
	<form action="options.php" method="post">
	<?php settings_fields( 'anylink_options_group' ); ?>
	<?php do_settings_sections( 'anyLinkSetting' ); ?>
	<?php submit_button() ;?>
	</form>
		<h3>建立索引</h3>
	<form action="<?php echo admin_url( 'options-general.php?page=anyLinkSetting' ); ?>" method="post">
	<div id="anylink_index">
		<span class="plain">在第一次安装 andlink 时你需要对所有文章进行重新索引。 该过程是对原有文章中的外链进行索引。在安装anylink之后新发表/更新的文章anylink会自动完成索引，你不需要手动进行引操作。</span>
		<div id="anylink_bar">
			<div id="anylink_proceeding"> </div>
		</div>
		<input name="action" value="anylink_scan" type="hidden" />
		<?php submit_button( '新建索引', 'secondary' ) ;?>
	</div>
	</form>
	<form action="<?php echo admin_url( 'options-general.php?page=anyLinkSetting' ); ?>" method="post">
		<span class="plain">允许用户多次生成slug（链接后面的随机字母）。不过需要提醒的是，除非您修改了slug的样式，请尽量不要重新生成slug，这会改变您页面中的链接，对于搜索引擎来说可能会认为您的文章进行了修改。<br /><b>注意：只有在“基本设置”中修改了slug样式时该功能才能使用。</b></span>
		<div id="slug_bar">
			<div id="anylink_slug_proceeding"> </div>
		</div>
		<input name="action" value="anylink_regnerate" type="hidden" />
		<?php submit_button( '重新生成slug', 'secondary' ) ;?>
	</form>
</div>
<?php
if( isset( $_POST['action'] ) && $_POST['action'] == 'anylink_scan' ) {
	flush();
	set_time_limit( 0 );
	require_once( ANYLNK_PATH . "/classes/al_covert.php" );
	$objAllPost = new al_covert();
	$arrPostTypes = array('post', 'page');
	$arrPostID = array();
	$arrPostID = $objAllPost -> arrGetPostIDsByType( $arrPostTypes );
	$j = count( $arrPostID );
	$k = 0;
	foreach( $arrPostID as $ID ) {
		$objAllPost -> covertURLs( $ID );
		$k = $k + 1;
?>
<script type="text/javascript">setDivStyle( "anylink_proceeding", <?php echo round( $k / $j, 4 ); ?> ); </script> 
<?php
	flush();
	}
}
if( isset( $_POST['action'] ) && $_POST['action'] == 'anylink_regnerate' ) {
	$alOption = get_option( 'anylink_options' );
	if( $alOption['slugNum'] != $alOption['oldSlugNum'] || $alOption['slugChar'] != $alOption['oldSlugChar'] ) { 
		flush();
		set_time_limit( 0 );
		require_once( ANYLNK_PATH . "/classes/al_covert.php" );
		$objAllSlug = new al_covert();
		$arrSlugID = $objAllSlug -> getAllSlugID();
		$all = count( $arrSlugID );
		if( $all == 0 )
			$all = 1;
		$p = 0;
		$alOption['oldSlugNum'] = $alOption['slugNum'];
		$alOption['oldSlugChar'] = $alOption['slugChar'];
		update_option( 'anylink_options', $alOption );
		foreach( $arrSlugID as $slugID ) {
			$objAllSlug -> regenerateSlugByID( $slugID );
			$p = $p +1;
	?>
	<script type="text/javascript">setDivStyle( "anylink_slug_proceeding", <?php echo round( $p / $all, 4 ); ?> ); </script> 
	<?php
		flush();
		}
	}
}
?>