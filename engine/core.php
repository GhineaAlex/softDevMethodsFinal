<?php 

namespace engine ;

if ( ! defined ( 'ALLOW' ) ) exit ;

use engine\functions as __F ;
use engine\database as DB ;


class core {

	static $query_string ;
	static $global_configs = array ( ) ;
	static $mysql_handle = null ;

	function __construct ( ) {
		self::__global_config ( ) ;
	    $this -> __init_folders (
	      self::$global_configs [ 'ENGINE_FOLDER' ] ,
	      self::$global_configs [ 'TEMPLATE_FOLDER' ] ,
	      self::$global_configs [ 'OS_SEPARATOR' ]
	    ) ;
	    $this -> __connect_to_mysql ( ) ;
	    self::__init_constants ( ) ;
	    self::__url_string_query ( ) ;
	    ( new view ( ) ) -> __render ( self::$query_string ) ;

		
		
	}

	public function __connect_to_mysql ( ) {

		try {
			self::$mysql_handle = new DB (
				self::$global_configs [ 'DB_TYPE' ] ,
				self::$global_configs [ 'DB_HOST' ] ,
				self::$global_configs [ 'DB_USER' ] ,
				self::$global_configs [ 'DB_PASSWORD' ] ,
				self::$global_configs [ 'DB_DB' ]
			) ;
			self::$mysql_handle -> exec ( "set names utf8" ) ;
		}
		catch ( PDOException $e ) {
			print "A aparut o eroare: " . $e -> getMessage ( ) . "<br/>" ;
			die ( ) ;
		}
	}

	public function __init_folders ( $engine , $template , $delimiter ) {

		if ( realpath ( $engine ) && realpath ( $template ) ) {
			$engine = realpath ( $engine ) . $delimiter ;
			$engine = rtrim ( $engine , $delimiter ) . $delimiter ;
			if ( ! is_dir ( $engine ) ) exit ( "O problema cu folderul 'engine' a intervenit." . "<br/>" ) ;
			define ( 'ENGINE_PATH' , $engine ) ;

			$template = realpath ( $template ) . $delimiter ;
			$template = rtrim ( $template , $delimiter ) . $delimiter ;
			if ( ! is_dir ( $template ) ) exit ( "O problema cu folderul 'template' a intervenit." . "<br/>" ) ;
			define ( 'TEMPLATE_PATH' , $template ) ;

		}
		else exit ( "O problema cu folderul 'engine' sau 'template' a intervenit." . "<br/>" ) ;
	  }

	static function __url_string_query ( ) {
		if ( self::$global_configs [ 'USE_HTACCESS' ] == true )
			$uri = substr ( $_SERVER [ 'REQUEST_URI' ] , strlen ( strstr ( $_SERVER [ 'SCRIPT_NAME' ] , 'index.php' , true ) ) ) ;
		else {
			$uri = substr ( $_SERVER [ 'REQUEST_URI' ] , strlen ( strstr ( $_SERVER [ 'SCRIPT_NAME' ] , 'index.php' , true ) ) ) ;
			$uri = substr ( $uri , 10 ) ;
		}
		self::$query_string = explode (
			self::$global_configs [ 'SPLIT_CHAR_URL' ] ,
		  	rtrim ( ltrim ( __F::__protected_string ( $uri ) , self::$global_configs [ 'SPLIT_CHAR_URL' ] ) , self::$global_configs [ 'SPLIT_CHAR_URL' ] )
		) ;
	}

	static function __init_constants ( ) {
	    define ( 'URL' ,
			'' . ( ( self::$global_configs [ 'USE_HTTPS' ] ) ? 'https' : 'http' ) . '://' .
			__F::__protected_string ( $_SERVER [ 'SERVER_NAME' ] ) .
			substr ( __F::__protected_string ( $_SERVER [ 'SCRIPT_NAME' ] ) , 0 , strpos ( __F::__protected_string ( $_SERVER [ 'SCRIPT_NAME' ] ) , 'index' ) ) .
			( ( self::$global_configs [ 'USE_HTACCESS' ] ) ? '' : 'index.php?' )
	    ) ;
	    define ( 'REAL_URL' ,
			'' . ( ( self::$global_configs [ 'USE_HTTPS' ] ) ? 'https' : 'http' ) . '://' .
			__F::__protected_string ( $_SERVER [ 'SERVER_NAME' ] ) .
			substr ( __F::__protected_string ( $_SERVER [ 'SCRIPT_NAME' ] ) , 0 , strpos ( __F::__protected_string ( $_SERVER [ 'SCRIPT_NAME' ] ) , 'index' ) )
	    ) ;
	    define ( 'ERRORS_URL_PATH' , REAL_URL . self::$global_configs [ 'ENGINE_FOLDER' ] . '/view_core/errors'  ) ;
	    define ( 'TEMPLATE_URL' , REAL_URL . self::$global_configs [ 'TEMPLATE_FOLDER' ] . '/'  ) ;
    	define ( 'STYLE_URL' , REAL_URL . self::$global_configs [ 'TEMPLATE_FOLDER' ] . '/style'  ) ;
  	}

	static function __global_config ( ) {
		require_once 'global_config.php' ;
		self::$global_configs = $GLOBAL_CONFIG ;
  	}
}
