<?php

namespace engine ;

use engine\database as DB ;
use engine\view as view ;
use engine\hash as __H ;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class User extends view {

	static function __register ( $email , $password ) {
		DB::__db_query (
			core::$mysql_handle ,
			DB::$DB_FETCH_NONE ,
			DB::$DB_PROTECTED ,
			'INSERT INTO `registered_users` (`email`, `password`) VALUES (:email, :password)'  ,
			$email ,
			$password
		) ;
	}

	static function __check_logged ( ) {
		if ( isset ( $_SESSION [ 'openid' ] ) ) {
			return $_SESSION [ 'openid' ] ;
		}
		else return false ;
	}

	static function __user_information ( &$information = array ( ) ) {
		$openid = self::__check_logged ( ) ;
		if ( $openid !== false ) {
			$results = DB::__db_query (
				core::$mysql_handle ,
				DB::$DB_FETCH ,
				DB::$DB_PROTECTED ,
				'SELECT * FROM `accounts` WHERE `openid`=:openid'  ,
				$openid
			) ;
			$information = $results ;
		}
	}

	static function __update_user_information ( $openid ) {
		$identif = substr($openid, 0, 3);
		if ($identif == 765) {
			$file = file_get_contents ( 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=' . core::$global_configs [ 'API_KEY' ] . '&steamids=' . $openid ) ;
			$data = json_decode ( $file ) ;
			$data = $data -> { 'response' } -> { 'players' } [ 0 ] ;
			DB::__db_query (
				core::$mysql_handle ,
				DB::$DB_FETCH_NONE ,
				DB::$DB_PROTECTED ,
				'UPDATE `accounts` SET `personaname`=:pers, `profileurl`=:url, `avatar`=:a, `avatar_medium`=:am, `avatar_full`=:af, `chat_session`=:chat WHERE `openid`=:steamid' ,
				$data -> personaname ,
				$data -> profileurl ,
				$data -> avatar ,
				$data -> avatarmedium ,
				$data -> avatarfull ,
				__H::create ( 'md5' , $_SERVER [ 'REMOTE_ADDR' ] . '$' . $openid . '$' , core::$global_configs [ 'HASH_KEY' ] ) ,
				$openid
			) ;
		}
		else {
			DB::__db_query (
				core::$mysql_handle ,
				DB::$DB_FETCH_NONE ,
				DB::$DB_PROTECTED ,
				'UPDATE `accounts` SET `chat_session`=:chat WHERE `openid`=:steamid' ,
				__H::create ( 'md5' , $_SERVER [ 'REMOTE_ADDR' ] . '$' . $openid . '$' , core::$global_configs [ 'HASH_KEY' ] ) ,
				$openid
			) ;
			//google
		}
	}

	static function __basket_price ( ) {
		$result = DB::__db_query (
			core::$mysql_handle ,
			DB::$DB_FETCH ,
			DB::$DB_NONPROTECTED ,
			'SELECT `cart` FROM `accounts` WHERE `openid`=:sid'  ,
			parent::$_user_information [ 'openid' ]
		) ;
		$cart = json_decode ( $result [ 'cart' ] ) ;
		if ( empty ( $cart ) ) return 0 ;
		$price = 0 ;
		foreach ( $cart as $key => $value ) {
			$price += $value -> price ;
		}

		return $price ;
	}


	static function __add_item_cart ( $appid , $classid , $image , $name ) {
		if ( self::__check_logged ( ) == false ) return 0 ;
		if ( ! isset ( $_SESSION [ 'cart' ] ) ) $_SESSION [ 'cart' ] = array ( ) ;

		$result = DB::__db_query (
			core::$mysql_handle ,
			DB::$DB_FETCH ,
			DB::$DB_NONPROTECTED ,
			'SELECT i.`price`, a.`cart` FROM `items` i JOIN `accounts` a ON a.`openid`=:sid WHERE i.`classid`=:cls'  ,
			parent::$_user_information [ 'openid' ] ,
			$classid
		) ;
		$cart = ( ( json_decode ( $result [ 'cart' ] ) === NULL ) ? array ( ) : json_decode ( $result [ 'cart' ] ) ) ;
		$contor = 0 ;
		if ( !empty ( $cart ) ) {
			foreach ( $cart as $key => $value ) {
				if ( $value -> classid == $classid ) {
					$contor ++ ;
				}
			}
		}
		$pass = true ;
		if ( parent::__check_q_item ( $classid ) <= $contor ) $pass = false ;
		if ( $pass === true ) {
			if ( empty ( $result ) ) return 0 ;
			if ( $result [ 'price' ] + self::__basket_price ( ) > parent::$_user_information [ 'coins' ] ) return 3 ;

			array_push ( $cart , array ( "appid" => $appid , "classid" => $classid , "image" => $image , "name" => $name , "price" => $result [ 'price' ] ) ) ;

			DB::__db_query (
				core::$mysql_handle ,
				DB::$DB_FETCH ,
				DB::$DB_NONPROTECTED ,
				'UPDATE `accounts` SET `cart`=:cart WHERE `openid`=:sid'  ,
				json_encode ( $cart ) ,
				parent::$_user_information [ 'openid' ]

			) ;
			return 1 ;
		}
		else return 0 ;
	}

	static function __remove_item_cart ( $classid ) {
		if ( self::__check_logged ( ) == false ) return 0 ;
		$result = DB::__db_query (
			core::$mysql_handle ,
			DB::$DB_FETCH ,
			DB::$DB_NONPROTECTED ,
			'SELECT `cart` FROM `accounts` WHERE `openid`=:sid'  ,
			parent::$_user_information [ 'openid' ]
		) ;
		$cart = json_decode ( $result [ 'cart' ] ) ;
		if ( !empty ( $cart ) ) {
			if ( ( $key = array_search ( $classid , array_column ( $cart , 'classid' ) ) ) !== false ) {

				array_splice ( $cart , $key , 1 ) ;

				DB::__db_query (
					core::$mysql_handle ,
					DB::$DB_FETCH ,
					DB::$DB_NONPROTECTED ,
					'UPDATE `accounts` SET `cart`=:cart WHERE `openid`=:sid' ,
					json_encode ( $cart ) ,
					parent::$_user_information [ 'openid' ]
				) ;

			}
		}
	}

	static function __extract_cart ( ) {
		if ( self::__check_logged ( ) !== false ) {
			$result = DB::__db_query (
				core::$mysql_handle ,
				DB::$DB_FETCH ,
				DB::$DB_NONPROTECTED ,
				'SELECT `ID`, `personaname`, `trade_link`, `cart` FROM `accounts` WHERE `openid`=:sid'  ,
				parent::$_user_information [ 'openid' ]
			) ;
			$cart = json_decode ( $result [ 'cart' ] ) ;

			if ( ! empty ( $cart ) ) {
				if ( self::__basket_price ( ) <= parent::$_user_information [ 'coins' ] ) {

					$items = array ( ) ;
					$price = 0 ;
					foreach ( $cart as $key => $value ) {
						$price += $value -> price ;
						array_push ( $items ,  $value -> classid ) ;
					}

                    DB::__db_query (
                        core::$mysql_handle ,
                        DB::$DB_FETCH ,
                        DB::$DB_PROTECTED ,
                        'INSERT INTO `withdraw_request` (`content`) VALUES (:content)' ,
                        $result['ID'] . ' ' . $price . ' ' . json_encode ( $items )
                    ) ;
                    DB::__db_query (
                        core::$mysql_handle ,
                        DB::$DB_FETCH ,
                        DB::$DB_PROTECTED ,
                        'UPDATE `accounts` SET `coins`=`coins` - :coin, `cart`=\'\' WHERE `openid`=:openid' ,
                        $price,
                        parent::$_user_information [ 'openid' ]
                    ) ;
				}
			}
		}
	}

	static function __get_user_cart ( ) {
		$result = DB::__db_query (
			core::$mysql_handle ,
			DB::$DB_FETCH ,
			DB::$DB_NONPROTECTED ,
			'SELECT `cart` FROM `accounts` WHERE `openid`=:sid'  ,
			parent::$_user_information [ 'openid' ]
		) ;
		$cart = json_decode ( $result [ 'cart' ] ) ;

		if ( empty ( $cart ) ) return 'NULL' ;
		else return $cart ;
	}

	static function __get_user_items_cart ( ) {

		$result = DB::__db_query (
			core::$mysql_handle ,
			DB::$DB_FETCH ,
			DB::$DB_NONPROTECTED ,
			'SELECT `cart` FROM `accounts` WHERE `openid`=:sid'  ,
			parent::$_user_information [ 'openid' ]
		) ;
		if($result['cart'])
			{$cart = json_decode ( $result [ 'cart' ] ) ;}
		else $cart = array();

		return count ( $cart ) ;
	}

	static function __add_email ( $email ) {
		if ( strlen ( parent::$_user_information [ 'email' ] ) > 0 ) return 0 ;
		if ( ! filter_var ( $email , FILTER_VALIDATE_EMAIL ) ) return 0 ;

		$result = DB::__db_query (
			core::$mysql_handle ,
			DB::$DB_FETCH_NONE ,
			DB::$DB_PROTECTED ,
			'UPDATE `accounts` SET `coins`=`coins`+\'50\', `email`=:email WHERE `openid`=:sid;' ,
			$email ,
			parent::$_user_information [ 'openid' ]
		) ;
		return 1 ;

	}

}
