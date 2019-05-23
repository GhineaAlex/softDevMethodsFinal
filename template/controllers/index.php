<?php 

use engine\user as __U ;
use engine\database as DB ;
use engine\core as core ;

if ( ! defined ( 'ALLOW' ) ) exit ;
if ( self::$_user_type != 0 ) __U::__update_user_information ( $_SESSION [ 'openid' ] ) ;

$results = DB::__db_query ( 
	core::$mysql_handle ,
	DB::$DB_FETCH ,
	DB::$DB_NONPROTECTED ,
	'SELECT * FROM `popup` ORDER BY `ID` DESC LIMIT 1'
) ;

if ( empty ( $results ) ) {
	self::$_page_array [ 'POPUP_TITLE' ] = "" ;
	self::$_page_array [ 'POPUP_CONTENT' ] = "" ;
}
else {
	self::$_page_array [ 'POPUP_TITLE' ] = $results [ 'Title' ] ;
	self::$_page_array [ 'POPUP_CONTENT' ] = $results [ 'Content' ] ;
}