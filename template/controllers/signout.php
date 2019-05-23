<?php 

if ( ! defined ( 'ALLOW' ) ) exit ;

use engine\session as __S ;
use engine\functions as __F ;

__S::init ( ) ;

__S::destroy ( 'loggedin' ) ;
__S::set ( 'loggedin' , NULL ) ;
session_unset ( ) ;

__F::__redirect ( '' ) ;