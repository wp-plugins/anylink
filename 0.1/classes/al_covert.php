<?php
class slug {
	public $numSlugChar;
	public $slugChar;
	function __construct() {
		$arrOptions = get_option( 'anylink_options' );
		$this -> numSlugChar = $arrOptions['slugNum'];
		$this -> slugChar = $arrOptions['slugChar'];
	}
	//generate 4 characters randomly
	public function generate4Chars() {
		$slugChar = $this -> slugChar;
		switch( $slugChar ) {
			case 0:
				$chars = '0123456789';
				break;
			case 1:
				$chars = 'abcdefghijklmnopqrstuvwxyz';
				break;
			case 2:
				$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
				break;
			default:
				$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
		}
		$str = '';
		$num = $this -> numSlugChar;
		$length = strlen( $chars ) - 1;
		for( $i = 0; $i < $num; $i++ )
			$str .= $chars[mt_rand( 0,$length )];
		return $str;
	}
	//get a slug
	public function generateSlug() {
		global $wpdb;
		global $ANYLNK_DBTB;
		$slug = $this -> generate4Chars();
		$query = "SELECT al_slug FROM " . ANYLNK_DBTB;
		$slugs = $wpdb->get_col( $query , 0 );
		while( in_array( $slug,$slugs ) )
			$slug = $this -> generateSlug();
		return $slug;
	}
	//generate a slug array by the array given
	public function generateSlugArr( $arr ) {
		if ( ! is_array( $arr ) )
			return null;
		$slugArr = array();
		foreach ( $arr as $value ) {
			$slug = $this -> generateSlug();
			$slugArr["$slug"] = $value;
		}
		return $slugArr;
	}
}
class al_covert {
	//get all the posts/pages' id
	//I don't use the get_posts() method, coz I needn't that much vars
	//return an array
	public function arrGetPostIDsByType( $arrPostType ) {
		global $wpdb;
		$arrPostIDs = array();
		if( is_array( $arrPostType ) ) {
			foreach( $arrPostType as $postType ) {
				$arrPostIDs += $wpdb -> get_col( $wpdb -> prepare( 
					"SELECT ID 
					FROM " . $wpdb -> prefix . "posts 
					WHERE post_type = %s",
					$postType 
					) );
			}
		}
		return $arrPostIDs;
	}
	public function arrGetAllLnks( $content ) {
		$pattern = '/(?<=href=["\'])https?:\/\/[^"\']+/i';
		preg_match_all($pattern, $content, $matches);
		$arrAllLnks = array_unique( $matches[0] );
		return $arrAllLnks;
	}
	//site URL should not be pull out
	private function filterLocalURL( array $arrURL ) {
		$siteURL = home_url();
		$result = array();
		foreach( $arrURL as $URL ) {
			if ( index2Of( $siteURL, $URL ) )
				continue;
			$result[] = $URL;	
		}
		return $result;
	}
	private function storeExtLnks($arrURLs ) {
		global $wpdb;
		$urlIDs = array();
		$slugs = new slug();
		foreach( $arrURLs as $URL ) {
			//if the URL to be stored is already in DB then return its ID
			$idInTb = $wpdb -> get_var( $wpdb -> prepare( 
								"SELECT al_id
								FROM " . ANYLNK_DBTB . "
								WHERE al_origURL = %s",
								$URL 
								) );
			if( ! is_null( $idInTb ) ){
				$urlIDs[] = $idInTb;
				continue;
			}
			//if not, generate a slug and insert
			$slug = $slugs -> generateSlug();
			$wpdb -> insert( ANYLNK_DBTB, 
							array( 
								'al_slug'    => $slug,
								'al_origURL' => $URL,
								'al_crtime'  => '',
								),
							array( 
								'%s',
								'%s',
								'NOW()',
								)
							);
			$urlIDs[] = $wpdb -> insert_id;
		}
		return $urlIDs;
	}
	private function storeRel ( $post_id, $arrUrlIDs ) {
		global $wpdb;
		$arrOldIndex = array();
		//some links may be deleted after your editing the post
		//the relationship should be deleted as well
		//pull out all IDs form data table as the old entries
		$arrOldRel = $wpdb -> get_results( $wpdb -> prepare( 
						"SELECT al_url_id
						FROM " . ANYLNK_DBINDEX . " 
						WHERE al_post_id = %d",
						$post_id 
						), ARRAY_A );
		foreach( $arrOldRel as $oldRel ){
			foreach( $oldRel as $urlID ){
				$arrOldIndex[] = $urlID; 
			}
		}
		//$arrUrlIDs is an array of news entries
		//compare both arrays with each other
		//if one record is found in new array but not in old array it means we add an new URL
		//on the contrary, a record if found if old array but not in new array. It means we deleted a URL
		$arrToAdd = array_diff( $arrUrlIDs, $arrOldIndex );
		$arrToDel = array_diff( $arrOldIndex, $arrUrlIDs );
		if( ! empty( $arrToAdd ) ) {
			foreach( $arrToAdd as $urlID ) {
				$wpdb -> insert( ANYLNK_DBINDEX,
								array( 
									'al_url_id'   => $urlID,
									'al_post_id'  => $post_id,
									),
								array( 
									'%d',
									'%d',
									)
								);
			}
		}
		if ( ! empty( $arrToDel ) ) {
			foreach( $arrToDel as $urlID ) {
				$wpdb -> delete( ANYLNK_DBINDEX,
								array(
									'al_url_id'  => $urlID,
									'al_post_id' => $post_id,
									),
								array(
									'%d',
									'%d',
									)
								);
			}
		}
	}
	//get all URL's ID
	public function getAllSlugID() {
		$arrSlugID = array();
		global $wpdb;
		$arrSlugID = $wpdb -> get_col( $wpdb -> prepare( 
								"SELECT al_id
								FROM " . ANYLNK_DBTB
								, '' ) );
		return $arrSlugID;
	} //end getAllSlugID
	//regenerate slugs
	public function regenerateSlugByID( $slugID ) {
		$slugs = new slug();
		global $wpdb;
		$newSlug = $slugs -> generateSlug();
		$wpdb -> update( ANYLNK_DBTB, 
						array( 'al_slug' => $newSlug ),
						array( 
							'al_id' => $slugID,
							'al_isAuto' => 1
							),
						array('%s'),
						array('%s', '%d')
						);
	} //end regenerateSlugByID
	public function covertURLs( $id ) {
		$thePost = get_post( $id,  ARRAY_A );
		$content = $thePost['post_content']; //get post content
		$arrURLs = array();
		$arrIDs  = array();
		$arrURLs = $this -> arrGetAllLnks( $content );
		$arrURLs = $this -> filterLocalURL( $arrURLs );
		if( empty( $arrURLs ) )
			return;
		$arrIDs  = $this -> storeExtLnks( $arrURLs );
		$this -> storeRel( $id, $arrIDs );
	}
}
?>