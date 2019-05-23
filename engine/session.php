<?php

namespace engine ;

if ( ! defined ( 'ALLOW' ) ) exit ;

class session {

	public static function init ( ) {
		ini_set ( 'session.cookie_httponly' , true ) ;

		@session_start ( ) ;

		if ( isset ( $_SESSION [ 'last_ip' ] ) === false ) {
			$_SESSION [ 'last_ip' ] = $_SERVER [ 'REMOTE_ADDR' ] ;
		}

		if ( $_SESSION [ 'last_ip' ] !== $_SERVER [ 'REMOTE_ADDR' ] ) {
			session_unset ( ) ;
			session_destroy ( ) ;
		}
	}

	public static function set ( $key , $value ) {
		$_SESSION [ $key ] = $value ;
	}

	public static function get ( $key ) {
		if ( isset ( $_SESSION [ $key ] ) ) {
			return $_SESSION [ $key ] ;
		}
	}

	public static function destroy ( $key ) {
		//session_destroy ( ) ;
		if ( isset ( $_SESSION [ $key ] ) )
			unset ( $_SESSION [ $key ] ) ;
	}
}
