<?php
class al_filter {
	public $arrU2S = array();
	public $redirectCat;
	public $redirectType;
	function __construct() {
		$anylinkOptions = get_option( 'anylink_options' );
		$this -> redirectCat  = $anylinkOptions['redirectCat'];
		$this -> redirectType = $anylinkOptions['redirectType'];
	}
	//@post_id: get all links and slugs of a specified post
	public function getAllLnks( $post_id ) {
		$arrURL = array();
		global $wpdb;
		$arrURL = $wpdb -> get_results( $wpdb -> prepare( 
			"
			SELECT U.al_id, U.al_slug,U.al_origURL 
			FROM " . ANYLNK_DBINDEX . " I
			LEFT JOIN " . ANYLNK_DBTB . " U
			ON I.al_url_id = U.al_id
			WHERE I.al_post_id = %d",
			$post_id
		), ARRAY_A );
		return $arrURL;
	}
	//restore all URL
	private function replaceURL( $matches ) {
		$U2S = $this -> arrU2S;
		$siteURL = home_url();
		if( index2Of( $siteURL, $matches[2] ) )
			return $matches[1] . $matches[2] . $matches[3];
		//only replace the links which have slugs, or return the original URL
		elseif( array_key_exists( "$matches[2]", $U2S ) )
			return $matches[1] . $U2S[$matches[2]] . $matches[3];
		//if one post or link isn't indexed, just return the original URL
		else
			return $matches[1] . $matches[2] . $matches[3];
	}
	public function applyFilter( $content ) {
		global $wp_query, $wp_rewrite;
		$post_id = get_the_id();
		$arrUrlSlug = $this -> getAllLnks( $post_id );
		if( $arrUrlSlug ) {
			foreach( $arrUrlSlug as $arrSlugs ) {
				$this -> arrU2S[$arrSlugs['al_origURL']] = $this -> getInternalLinkBySlug( $arrSlugs['al_slug'] );
			}
		}
		$pattern  = '/(<a\s*?.*?\s*?';
		$pattern .= 'href=[\'"]\s*?)(?P<URL>[^>]+?)([\'"]';
		$pattern .= '.*?>)/i';
		//covert ALL URLs, we can't just use str_replace, 
		//coz a post may contain a plain URL, 
		//even some text like this: href="http://dudo.org"
		//Or, this plain text will be replaced as well
		$content  = preg_replace_callback( $pattern, array( $this, 'replaceURL' ), $content );
		return $content;
	}
	public function addQueryVars( $qvars ) {
		array_push( $qvars, $this -> redirectCat );
		return $qvars;
	}
	public function alter_the_query( $wp ) {
		global $wp_query, $wp_rewrite;
		$gotoURL = '';
		if( $wp_rewrite -> using_permalinks() )
			if ( array_key_exists( $this -> redirectCat, $wp -> query_vars ) && $wp -> query_vars[$this -> redirectCat] != '')  
				$gotoURL = $wp -> query_vars[$this -> redirectCat];
			else
				return;
		elseif( isset( $_GET[$this -> redirectCat] ))
			$gotoURL = $_GET[$this -> redirectCat];
		if( ! empty( $gotoURL ) ) {
			wp_redirect( htmlspecialchars_decode( $this -> getUrlBySlug( $gotoURL ) ), ( int )$this -> redirectType );
			exit;
		}	
	}
	public function getUrlBySlug( $slug ) {
		global $wpdb;
		$URL = $wpdb -> get_var( $wpdb -> prepare( 
			"
			SELECT al_origURL
			FROM " . ANYLNK_DBTB . " 
			WHERE al_slug = %s",
			$slug
		) );
		return $URL;
	}
	
	public function getSlugById( $id ) {
		global $wpdb;
		$arrSlug = $wpdb -> get_row( $wpdb -> prepare( 
			"SELECT * 
			FROM " . ANYLNK_DBTB . "
			WHERE al_id = %s", 
			$id
		), ARRAY_A );
		return $arrSlug;
	}
	
	public function getInternalLinkBySlug( $slug ) {
		global $wp_rewrite;
		$siteURL = home_url();
		if( $wp_rewrite -> using_permalinks() )
			$internalLink = $siteURL . '/'  . $this -> redirectCat . '/' . $slug;
		else
			$internalLink = $siteURL . '/?' . $this -> redirectCat . '=' . $slug;
		return $internalLink;
	}
}
?>