<?php

namespace engine ;

if ( ! defined ( 'ALLOW' ) ) exit ;

use \PDO ;
use engine\functions as __F ;

class database extends PDO {

	static $DB_FETCH_NONE = 0 ;
	static $DB_FETCH = 1 ;
	static $DB_FETCH_ALL = 2 ;
	static $DB_COUNT = 3 ;
	static $DB_LAST_ID = 4 ;

	static $DB_PROTECTED = 0xFFF ;
	static $DB_NONPROTECTED = null ;

	function __construct ( $type , $host , $user , $password , $database ) {
		return parent::__construct ( $type . ':host=' . $host . ';dbname=' . $database , $user , $password ) ;
	}

	static function __destroy_mysql ( $handle ) {
		$handle = null ;
	}

	static function __db_query ( $handle , $type_of_fetch , $protected = null , $sql ) {
		$execute_array = array ( ) ;
		if ( preg_match ( '/[:]\w*\b/' , $sql ) ) {
			preg_match_all ( '/[:]\w*\b/' , $sql , $result ) ;

			foreach ( func_get_args ( ) as $key => $value )
				if ( ! ( $key >= 0 && $key <= 3 ) )
					$execute_array [ $result [ 0 ] [ $key - 4 ] ] = $value ;

		}

		$sth = $handle -> prepare ( $sql ) ;
		$sth -> execute ( $execute_array ) ;

		switch ( $type_of_fetch ) {
			case self::$DB_FETCH:
				$fet = $sth -> fetch ( PDO::FETCH_ASSOC ) ;
				if ( is_array ( $fet ) || is_object ( $fet ) ) {
					foreach ( $fet as $key => $value ) {  
						$fet [ $key ] = ( ( $protected != self::$DB_NONPROTECTED ) ? ( $fet [ $key ] ) : ( $fet [ $key ] ) ) ;
					}
				}
				return $fet ;
			break ;

			case self::$DB_FETCH_ALL:
				$fet = $sth -> fetchAll ( PDO::FETCH_ASSOC ) ;

				foreach ( $fet as $re_key => $result ) {  
					foreach ( $result as $key => $value ) {
						$fet [ $re_key ] [ $key ] = $fet [ $re_key ] [ $key ] ;
					}
				}
				return $fet ;
			break ;

			case self::$DB_COUNT: {
				return $count = $sth -> rowCount ( ) ;
				break ;
			}

			case self::$DB_LAST_ID: {
				return $last = $handle -> lastInsertId ( ) ;
				break ;
			}

			case self::$DB_FETCH_NONE:
				return ( true ) ;
			break ;
		}
	}
}
