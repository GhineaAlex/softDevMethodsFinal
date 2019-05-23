<?php

if ( ! defined ( 'ALLOW' ) ) exit ;

use engine\user as __U ;
use engine\functions as __F ;

if ( self::$_user_type == 0 ) exit ( 'Denied' ) ;

$inventory = array ( ) ;

foreach ((array)self::__get_boot_inventory() as $key => $value) {
    array_push($inventory, array('appid' => $value ['appid'], 'image' => $value ['icon_url'], 'classid' => $value ['classid'], 'name' => $value ['name'], 'name_color' => $value ['name_color'], 'price' => $value ['price'], 'exterior' => ((strlen($value ['exterior']) > 1) ? ("(" . $value ['exterior'] . ")") : (""))));

}

$appid = "all" ;
if ( isset ( $configs [ 1 ] ) ) {
    if ( $configs [ 1 ] === 'all' ) {
		$appid = $configs [ 1 ] ;
    }
    else if ($configs [1] === 'items'){
      foreach($inventory as $key => $value){
        
      }
      $appid = $configs[1];
    }

}


$sort = "descending" ;
usort ( $inventory , function ( $a , $b ) { return ( $a [ "price" ] < $b [ "price" ] ) ; } ) ;
if ( isset ( $configs [ 2 ] ) ) {
	if ( $configs [ 2 ] === 'A-Z' ) {
		usort ( $inventory , function ( $a , $b ) { return strcmp ( $a [ "name" ] , $b [ "name" ] ) ; } ) ;
		$sort = $configs [ 2 ] ;
	}
	else if ( $configs [ 2 ] === 'Z-A' ) {
		usort ( $inventory , function ( $a , $b ) { return strcmp ( $b [ "name" ] , $a [ "name" ] ) ; } ) ;
		$sort = $configs [ 2 ] ;
	}
	else if ( $configs [ 2 ] === 'descending' ) {
		usort ( $inventory , function ( $a , $b ) { return ( $a [ "price" ] < $b [ "price" ] ) ; } ) ;
		$sort = $configs [ 2 ] ;
	}
	else usort ( $inventory , function ( $a , $b ) { return ( $a [ "price" ] > $b [ "price" ] ) ; } ) ;
}


$search = "none" ;
if ( isset ( $configs [ 3 ] ) ) {

	$search = __F::__protected_string ( $configs [ 3 ] ) ;
	if ( $search !== "none" ) {
		foreach ( $inventory as $key => $value ) {
			$pos = stripos ( $value [ 'name' ] , urldecode ( $search ) ) ;
			if ( $pos === false ) {
				unset ( $inventory [ $key ] ) ;
			}
		}
	}
}
$contor = 0 ;
foreach ( $inventory as $key => $value ) {
	$contor ++ ;
}

$page = 1 ;
$number_onpage = 18 ;
if ( isset ( $configs [ 4 ] ) ) $page = intval ( __F::__protected_string ( $configs [ 4 ] ) ) ;
$num_pag = ceil ( $contor / $number_onpage ) ;
$page_end = $number_onpage * $page ;
$page_start = $page_end - $number_onpage ;

foreach ( $inventory as $key => $value ) {
	if ( $key < $page_start || $key >= $page_end ) {
		unset ( $inventory [ $key ] ) ;
	}
}
$inventory = array_values ( $inventory ) ;

self::$_page_array [ 'PAGINATE' ] = __F::__paginate ( URL . 'withdraw_steam' . '/' . $appid . '/' . $sort . '/' . $search , $page , $num_pag ) ;
self::$_page_array [ 'PAGE' ] = $page ;
self::$_page_array [ 'SEARCH' ] = $search ;
self::$_page_array [ 'SORT' ] = $sort ;
self::$_page_array [ 'APPID' ] = $appid ;
self::$_page_array [ 'INVENTORY' ] = $inventory ;
