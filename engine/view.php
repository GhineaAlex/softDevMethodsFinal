<?php

namespace engine ;

if ( ! defined ( 'ALLOW' ) ) exit ;

use engine\session as __S ;
use engine\user as __U ;
use engine\functions as __F ;
use engine\database as DB ;
use engine\error as Error ;

class view {
	public $_main_page ;
  	static $_page_array = array ( ) ;
  	static $_user_type ,
  		   $_user_cart_price ,
  		   $_user_cart_count ,
  		   $_user_information = array ( ) ;


	function __render ( $configs = array ( ) , $hhf = true ) {

		__S::init ( ) ;

		$this -> _main_page = ( empty ( $configs [ 0 ] ) ) ? ( 'index' ) : ( $configs [ 0 ] ) ;

		self::$_user_type = ( __U::__check_logged ( ) === false ) ? 0 : 1 ;
		__U::__user_information ( self::$_user_information ) ;

		self::__global_vars ( ) ;
		self::$_page_array [ 'PAGE' ] = $this -> _main_page ;

		if ( $this -> _main_page === 'xmlhttprequest' ) {
			require_once TEMPLATE_PATH . 'xmlhttprequests' . core::$global_configs [ 'OS_SEPARATOR' ] . 'index.php' ;
		}
		else {
	  		$controller = TEMPLATE_PATH .  'controllers' . core::$global_configs [ 'OS_SEPARATOR' ] . $this -> _main_page . '.php' ;

	      	if ( file_exists ( $controller ) )
	        	require_once ( $controller ) ;

	  		if ( file_exists ( TEMPLATE_PATH . 'pages' . core::$global_configs [ 'OS_SEPARATOR' ] . $this -> _main_page . '.html' ) ) {
	    		if ( $hhf == true ) $this -> __html ( TEMPLATE_PATH . 'hhf' . core::$global_configs [ 'OS_SEPARATOR' ] . 'header.html' ) ;
				$this -> __html ( TEMPLATE_PATH . 'pages' . core::$global_configs [ 'OS_SEPARATOR' ] . $this -> _main_page . '.html' ) ;
	    		if ( $hhf == true ) $this -> __html ( TEMPLATE_PATH . 'hhf' . core::$global_configs [ 'OS_SEPARATOR' ] . 'footer.html' ) ;

			}
			else new Error ( '404' ) ;
		}
	}

	static function __global_vars ( ) {
		self::$_user_cart_price = ( self::$_user_type == 1 ) ? __U::__basket_price ( ) : 0 ;
		self::$_user_cart_count = ( self::$_user_type == 1 ) ? __U::__get_user_items_cart ( ) : 0 ;

        $results = DB::__db_query (
            core::$mysql_handle ,
            DB::$DB_FETCH_ALL ,
            DB::$DB_PROTECTED ,
            'SELECT `personaname`, `avatar_medium`, `coins` FROM `accounts` ORDER BY `coins` DESC LIMIT 10'
        );

		self::$_page_array = array (
			'TITLE' => core::$global_configs [ 'WEB_NAME' ] ,
			'REAL_URL' => REAL_URL ,
			'URL' => URL ,
			'CHAT_URL' => core::$global_configs [ 'CHAT_URL' ] ,
			'STYLES' => self::__load_styles ( ) ,
			'JAVASCRIPTS' => self::__load_javascripts ( ) ,
			'USER_LOGGED' => self::$_user_type ,
			'USER_INFORMATION' => self::$_user_information ,
			'USER_ITEMS_CART' => self::$_user_cart_count ,
			'USER_CART_PRICE' => self::$_user_cart_price ,
            'TOP10' => $results
	    ) ;

	}

	public function __html ( $template_name ) {
	    if ( isset ( $template_name ) && strlen ( $template_name ) && is_string ( $template_name ) ) {
			$html_content = file_get_contents ( $template_name ) ;
	      	self::__check_for_variables ( $html_content ) ;
	      	self::__check_for_foreach ( $html_content ) ;
	      	self::__check_for_condition ( $html_content ) ;
	      	$html_content = str_replace ( PHP_EOL , '' , preg_replace ( '/(\>)\s*(\<)/m' , '$1$2' , $html_content ) );
			echo $html_content ;
			return true ;
		} else return false ;
  	}

	static function __check_for_variables ( &$html_content ) {

    	if ( preg_match ( '/\{([0-9A-Za-z_-]*)\}/is' , $html_content ) ) {
			preg_match_all ( '/\{([0-9A-Za-z_-]*)\}/' , $html_content , $results ) ;

			foreach ( $results [ 1 ] as $key => $value ) {
				if ( is_array ( self::$_page_array [ $value ] ) ) {
					preg_match_all ( '/\{' . $value . '\}\[([0-9A-Za-z_-]*)\]/' , $html_content , $array_results ) ;
					foreach ( $array_results [ 1 ] as $array_key => $array_value ) {
	            		if ( array_key_exists ( $array_value , self::$_page_array [ $value ] ) ) {
	              			$html_content = str_replace ( '{' . $value . '}[' . $array_value . ']' , self::$_page_array [ $value ] [ $array_value ] , $html_content ) ;
		            	}
		            	else {
		              		$html_content = str_replace ( '{' . $value . '}[' . $array_value . ']' , '' , $html_content ) ;
		            	}
	      			}
	        	}
	        	else {
					if ( isset ( self::$_page_array [ $value ] ) ) {
        				$html_content = str_replace ( '{' . $value . '}' , self::$_page_array [ $value ] , $html_content ) ;
	          		}
	        	}
	      	}
	    }
	}

	static function __check_for_foreach ( &$html_content , $tree = 'tree' ) {
	    if ( preg_match ( '/<\!--\s*FOREACH\s*\(\s*([^<>]*?)\s*\)\s*\(\s*([^<>]*?)\s*\)\s*\[\s*([^<>]*?)\s*\]\s*\[\s*([^<>]*?)\s*\]\s*-->/is' , $html_content ) ) {

      		preg_match_all ( '/<\!--\s*FOREACH\s*\(\s*([^<>]*?)\s*\)\s*\(\s*([^<>]*?)\s*\)\s*\[\s*([^<>]*?)\s*\]\s*\[\s*([^<>]*?)\s*\]\s*-->/is' , $html_content , $page_results ) ;


			foreach ( $page_results [ 1 ] as $key => $value ) {
	        	if ( $page_results [ 4 ] [ $key ] === $tree ) {
	          		if ( preg_match ( '/<\!--\s*FOREACH\s*\(\s*([^<>]*?)\s*\)\s*\(\s*([^<>]*?)\s*\)\s*\[\s*' . $page_results [ 3 ] [ $key ] . '\s*\]\s*\[\s*' . $page_results [ 4 ] [ $key ] . '\s*\]\s*-->(.*?)<\!--\s*END_FOREACH\s*\[\s*' . $page_results [ 3 ] [ $key ] . '\s*\]\s*-->/is' , $html_content ) ) {
	            		preg_match_all ( '/<\!--\s*FOREACH\s*\(\s*([^<>]*?)\s*\)\s*\(\s*([^<>]*?)\s*\)\s*\[\s*' . $page_results [ 3 ] [ $key ] . '\s*\]\s*\[\s*' . $page_results [ 4 ] [ $key ] . '\s*\]\s*-->(.*?)<\!--\s*END_FOREACH\s*\[\s*' . $page_results [ 3 ] [ $key ] . '\s*\]\s*-->/is' , $html_content , $results ) ;


	            		if ( is_array ( self::$_page_array [ __F::__remove_square_bracket ( $results [ 1 ] [ 0 ] ) ] ) ) {
		              		$const = $results [ 3 ] [ 0 ] ;
		              		$foreach_string = null ;
		              		$results [ 3 ] [ 0 ] = null ;

							$foreach_key = strstr ( $results [ 2 ] [ 0 ] , ' AS' , true ) ;
							$foreach_value = substr ( strstr ( $results [ 2 ] [ 0 ] , 'AS' ) , 3 ) ;

	              			foreach ( self::$_page_array [ __F::__remove_square_bracket ( $results [ 1 ] [ 0 ] ) ] as $key_array => $value_array ) {
	                			$foreach_string = str_replace ( '[' . $foreach_key . ']' , $key_array , $const ) ;
	                			if ( is_array ( $value_array ) ) {
	                  				preg_match_all ( '/\[' . $foreach_value . '\]\(([0-9A-Za-z_-]*)\)/' , $foreach_string , $array_results ) ;
	                  				foreach ( $array_results [ 1 ] as $array_key => $array_value ) $foreach_string = str_replace ( '[' . $foreach_value . '](' . $array_value . ')' , htmlspecialchars_decode ( $value_array [ $array_value ] ) , $foreach_string ) ;
                				}
	                			else $foreach_string = str_replace ( '[' . $foreach_key . ']' , $value_array , $foreach_string ) ;

	                			self::__check_for_foreach ( $foreach_string , 'sub' ) ;
	                			self::__check_for_condition ( $foreach_string ) ;

	                			$results [ 3 ] [ 0 ] .= $foreach_string ;
	              			}
	            		}
	            		$html_content = preg_replace ( '/<\!--\s*FOREACH\s*\(\s*\[' . __F::__remove_square_bracket ( $results [ 1 ] [ 0 ] ) . '\]\s*\)\s*\(\s*' . $results [ 2 ] [ 0 ] . '\s*\)\s*\[\s*' . $page_results [ 3 ] [ $key ] . '\s*\]\s*\[\s*' . $page_results [ 4 ] [ $key ] . '\s*\]\s*-->.*?<\!--\s*END_FOREACH\s*\[\s*' . $page_results [ 3 ] [ $key ] . '\s*\]\s*-->/is' , $results [ 3 ] [ 0 ] , $html_content ) ;
	          		}
	        	}
	      	}
    	}
  	}

  	static function __check_for_condition ( &$html_content ) {
		if ( preg_match ( '/<\!--\s*IF\s*\(\s*(.*?)\s*\)\s*\[\s*([^<>]*?)\s*\]\s*-->/is' , $html_content ) ) {
			preg_match_all ( '/<\!--\s*IF\s*\(\s*(.*?)\s*\)\s*\[\s*([^<>]*?)\s*\]\s*-->/is' , $html_content , $results ) ;

			foreach ( $results [ 1 ] as $key => $value ) {
	        	if ( preg_match ( '/<\!--\s*END_IF\s*\[\s*' . $results [ 2 ] [ $key ] . '\s*\]\s*-->/is' , $html_content ) ) {
	          		$exist_else = false ;
	          		if ( preg_match ( '/<\!--\s*IF\s*\(\s*(.*?)\s*\)\s*\[\s*' . $results [ 2 ] [ $key ] . '\s*\]\s*-->.*?<\!--\s*ELSE\s*\[\s*' . $results [ 2 ] [ $key ] . '\s*\]\s*-->.*?<\!--\s*END_IF\s*\[\s*' . $results [ 2 ] [ $key ]  . '\s*\]\s*-->/is' , $html_content ) )
	            		$exist_else = true ;

	          		try {
	            		if ( eval ( 'return ' . $results [ 1 ] [ $key ] . ' ;' ) ) {

	              			$html_content = preg_replace ( '/<\!--\s*IF\s*\(\s*' . $results [ 1 ] [ $key ] . '\s*\)\s*\[\s*' . $results [ 2 ] [ $key ]  . '\s*\]\s*-->/is' , '' , $html_content ) ;

	              			if ( $exist_else == false ) $html_content = preg_replace ( '/<\!--\s*END_IF\s*\[\s*' . $results [ 2 ] [ $key ] . '\s*\]\s*-->/is' , '' , $html_content ) ;

		              		else $html_content = preg_replace ( '/<\!--\s*ELSE\s*\[' . $results [ 2 ] [ $key ] . '\]\s*-->.*?<\!--\s*END_IF\s*\[' . $results [ 2 ] [ $key ] . '\]\s*-->/is', '', $html_content ) ;
			            }
			            else {
	              			if ( $exist_else == false ) $html_content = preg_replace ( '/<\!--\s*IF\s*\(\s*' . $results [ 1 ] [ $key ] . '\s*\)\s*\[\s*' . $results [ 2 ] [ $key ]  . '\s*\]\s*-->.*?<\!--\s*END_IF\s*\[\s*' . $results [ 2 ] [ $key ] . '\s*\]\s*-->/is' , '' , $html_content ) ;

	              			else {
	                			$html_content = preg_replace ( '/<\!--\s*IF\s*\(.*?\)\s*\[' . $results [ 2 ] [ $key ] . '\]\s*-->.*?<\!--\s*ELSE\s*\[' . $results [ 2 ] [ $key ] . '\]\s*-->/is', '', $html_content );
	                			$html_content = preg_replace ( '/<\!--\s*END_IF\s*\[' . $results [ 2 ] [ $key ] . '\]\s*-->/is', '', $html_content );
	              			}
	        			}
	      			}
	          		catch ( Exception $e ) {
	            		exit ( 'Unknown error on template render.' ) ;
	          		}
	        	}
	      	}
		}
	}

	static function __load_styles ( ) {
		$string = '' ;

	    $dir = TEMPLATE_PATH . 'styles' ;
	    $files = scandir ( $dir ) ;

	    foreach ( $files as $key => $value )
			if ( ! in_array ( $value , array ( "." , ".." ) ) )
				if ( strpos ( $files [ $key ] , '.css' ) !== false )
					$string .= "<link rel='stylesheet' href='" . TEMPLATE_URL . "styles/" . $files [ $key ] . "" . ( ( $files [ $key ] === 'style.css' ) ? ( "?ver=" . core::$global_configs [ 'VERSION' ] ) : ( "" ) ) .  "' type='text/css' media='screen'>" . PHP_EOL . "\t" ;

	    return $string ;

	}

	static function __load_javascripts ( ) {
		$string = '' ;

    	$dir = TEMPLATE_PATH . 'javascripts' ;
    	$files = scandir ( $dir ) ;

    	foreach ( $files as $key => $value )
			if ( ! in_array ( $value , array ( "." , ".." ) ) )
				if ( strpos ( $files [ $key ] , '.js' ) !== false )
					$string .= "<script src=\"" . TEMPLATE_URL . "javascripts/" . $files [ $key ] . "" . ( ( $files [ $key ] === 'main.js' ) ? ( "?ver=" . core::$global_configs [ 'VERSION' ] ) : ( "" ) ) .  "\"></script>" . PHP_EOL . "\t" ;

		return $string ;

	}

	static function __get_boot_inventory ( ) {

		$boot_inventory = array ( ) ;
				$content = file_get_contents ( 'C:/xampp/htdocs/bot/inventory_items.json' ) ;
				$content = json_decode ( $content ) ;

			foreach ( $content as $key => $value ) {
					$results = DB::__db_query (
						core::$mysql_handle ,
						DB::$DB_FETCH ,
						DB::$DB_PROTECTED ,
						'SELECT `price` FROM `items` WHERE `classid`=:classid'  ,
						$value -> classid
					) ;
					if ( !empty ( $results ) ) {
						array_push ( $boot_inventory , array ( 'appid' => 12345 , 'classid' => $value -> classid , 'name' => $value -> name , 'icon_url' => $value -> icon_url , 'name_color' => $value -> name_color , 'price' => $results [ 'price' ] , 'exterior' => $value -> description ) ) ;
					}
		        }

		return $boot_inventory ;
	}


	static function __get_item_description ( $json_data_csgo , $json_data_pubg , $classid ) {

		if ( property_exists ( $json_data_csgo , 'descriptions' ) && property_exists ( $json_data_pubg , 'descriptions' ) ) {
			$json_array = array_merge ( $json_data_csgo -> descriptions , $json_data_pubg -> descriptions ) ;
		}
		else if ( !property_exists ( $json_data_csgo , 'descriptions' ) ) {
			$json_array = $json_data_pubg -> descriptions ;
		}
		else if ( !property_exists ( $json_data_pubg , 'descriptions' ) ) {
			$json_array = $json_data_csgo -> descriptions ;
		}
		$key = array_search ( $classid , array_column ( $json_array , 'classid' ) ) ;
		return $json_array [ $key ] ;

	}

	static function __check_q_item ( $classid ) {
		$boot_inventory = self::__get_boot_inventory ( ) ;
		$contor = 0 ;
		foreach ( $boot_inventory as $key => $value ) {
			if ( $value [ 'classid' ] == $classid ) {
				$contor ++ ;
			}
		}
		return $contor ;
	}

}
