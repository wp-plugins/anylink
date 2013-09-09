<?php
class al_option {
	public $anylinkOptions;
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'addMenu' ) );
		add_action( 'admin_init', array( &$this, 'alAdminInit' ) );
		add_action( 'updated_option', array( &$this, 'flushRules' ) );
		$this -> anylinkOptions = get_option( 'anylink_options' );
	}
	public function addMenu() {
		$al_option_page = add_submenu_page( 'options-general.php', 'anyLink插件配置', 'anyLink配置', 'manage_options', 'anyLinkSetting', array( &$this, 'anyLinkSettingPage' ) );
		add_action( 'admin_print_scripts-' . $al_option_page, array( &$this, 'anylinkAdminScripts' ) );
		add_action( 'admin_print_styles-' . $al_option_page, array( &$this, 'anylinkAdminScripts' ) );
	}
	//output scripts and styles
	public function anylinkAdminScripts() {
		wp_enqueue_script( 'anylink_script' );
		wp_enqueue_style( 'anylink_style' );
	}
	public function anyLinkSettingPage() {
		if( ! current_user_can( 'manage_options' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		include_once( ANYLNK_PATH . '/al_setting.php' );
	}
	public function alAdminInit() {
		//register a javascript and css files to output later
		wp_register_script( 'anylink_script', plugins_url( '/images/al_script.js', dirname( __FILE__ ) ) );
		wp_register_style( 'anylink_style', plugins_url( '/images/al_style.css', dirname( __FILE__ ) ) );
		register_setting( 'anylink_options_group', 'anylink_options', array( &$this, 'alCatValidate' ) );
		add_settings_section( 'al_general_settings', '基本设置', array( &$this, 'alGeneralDisp' ), 'anyLinkSetting' );
		add_settings_field( 'al_redirect_cat', '跳转目录名称', array( &$this, 'dispRedirectCat' ), 'anyLinkSetting', 'al_general_settings' );
		add_settings_field( 'al_redirect_type', '链接跳转类型', array( &$this, 'dispRedirctType' ), 'anyLinkSetting', 'al_general_settings' );
		add_settings_field( 'al_slug_num', '自动生产链接slug的长度', array( &$this, 'dispSlugNum' ), 'anyLinkSetting', 'al_general_settings' );
		add_settings_field( 'al_slug_char', '自动生产链接slug的样式', array( &$this, 'dispSlugChar' ), 'anyLinkSetting', 'al_general_settings' );
		add_settings_field( 'al_form_identify', '', array( &$this, 'hiddenFormIdentify' ), 'anyLinkSetting', 'al_general_settings' );
	}
	public function alGeneralDisp() {
		echo "";
	}
	public function dispRedirectCat() {
		$cat = $this -> anylinkOptions['redirectCat'];
		echo site_url() . "/<input id='anylink_options' name='anylink_options[redirectCat]' value='{$cat}' class='small-text' type='text' size='8' />/ab12<br />请确保以字母开头，且仅含有字母、数字、下划线、连接符，最大长度不超过12个";
	}
	public function dispRedirctType() {
		$type = ( int )$this -> anylinkOptions['redirectType'];
		//determine which radio button should be checked
		$checked301 = ' ';
		$checked307 = ' ';
		$checked200 = ' ';
		switch( $type ) {
			case 301:
				$checked301 = 'checked="checked"';
				break;
			case 307:
				$checked307 = 'checked="checked"';
				break;
			case 200;
				$checked200 = 'checked="checked"';
				break;
		}
		$redirectType  = "<input type='radio' id='al_redirect_type_301' name='anylink_options[redirectType]' value='301' $checked301 />";
		$redirectType .= "<label for='al_redirect_type_301'>301永久性转移</label><br />";
		$redirectType .= "<input type='radio' id='al_redirect_type_307' name='anylink_options[redirectType]' value='307' $checked307 />";
		$redirectType .= "<label for='al_redirect_type_307'>307临时性转移</label><br />";
		$redirectType .= "<input type='radio' id='al_redirect_type_200' name='anylink_options[redirectType]' value='200' $checked200 disabled='true' />";
		$redirectType .= "<label for='al_redirect_type_200'>javascript中间页跳转</label><br />";		
		echo $redirectType;
	}
	public function dispSlugNum() {
		$num = $this ->anylinkOptions['slugNum'];
		echo "<input type='text' id='slugNum' name='anylink_options[slugNum]' value='{$num}' class='small-text' size='4' maxlength='2' /><br />最小值为4，最大值为12";
	}
	public function dispSlugChar() {
		$chars = $this -> anylinkOptions['slugChar'];
		$htmlChar  = "<input type='radio' id='slugCharNum' name='anylink_options[slugChar]' value='0' ";
		$htmlChar .= $chars == 0 ? 'checked' : '';
		$htmlChar .= " /><label for='slugCharNum'>纯数字</label><br />";
		$htmlChar .= "<input type='radio' id='slugCharChar' name='anylink_options[slugChar]' value='1' ";
		$htmlChar .= $chars == 1 ? 'checked' : '';
		$htmlChar .= " /><label for='slugCharChar'>纯字母</label><br />";
		$htmlChar .= "<input type='radio' id='slugCharNumchar' name='anylink_options[slugChar]' value='2' ";
		$htmlChar .= $chars == 2 ? 'checked' : '';
		$htmlChar .= " /><label for='slugCharNumchar'>字母与数字混合</label>";
		$htmlChar .= "<br /><b>建议slug设置为4位字母与数字混合。单纯使用数字时长度最好设置为6位以上。</b>";
		echo $htmlChar;
	}
	/*  I should put some validations here
	 *  a filter named "sanitize_option_$optionname" is applied when you can update_option
	 *  so we need an identify key to determine which form the data come form
	 */ 
	public function alCatValidate( $input ) {
		if( ! array_key_exists( 'identify', $input ) )
			return $input;
		$oldOptions = $this -> anylinkOptions;
		$input = array_map( "trim", $input );
		if( preg_match( '/^[a-z][a-z0-9_-]{0,11}/', $input['redirectCat'] ) )
			$oldOptions['redirectCat'] = $input['redirectCat'];
		if( $input['redirectType'] == 301 || $input['redirectType'] == 307 || $input['redirectType'] == 200 )
			$oldOptions['redirectType'] = $input['redirectType'];
		if( is_int( ( int )$input['slugNum'] ) && $input['slugNum'] < 13 && $input['slugNum'] > 3 )
			$oldOptions['slugNum'] = $input['slugNum'];
		$oldOptions['slugChar'] = $input['slugChar'];
		return $oldOptions;
	}
	//out put a hidden field to identify the form
	public function hiddenFormIdentify() {
		$hiddenHtml = "<input type='hidden' name='anylink_options[identify]' id='al_identify' value='anylink' />";
		echo $hiddenHtml;
	}
	//flush the rewrite rules
	public function flushRules() {
		$alOptions = get_option( 'anylink_options' );
		if( $alOptions['redirectCat'] != $alOptions['oldCat'] ) {
			$cat = $alOptions['redirectCat'];
			add_rewrite_rule( "$cat/([0-9a-z]{4,})", 'index.php?' . $cat . '=$matches[1]', 'top' );
			flush_rewrite_rules();
			$alOptions['oldCat'] = $alOptions['redirectCat'];
			update_option( 'anylink_options', $alOptions );
		}
	}
}
?>