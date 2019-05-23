<?php 
//error_reporting ( E_ALL ) ;
define ( 'ALLOW' , true ) ;

spl_autoload_register ( function ( $object ) {
    require_once str_replace ( "\\" , "/" , $object ) . '.php';
} ) ;

new engine\core ( ) ;

exit ;