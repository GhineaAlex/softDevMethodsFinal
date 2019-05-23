<?php 

namespace engine ;

if ( ! defined ( 'ALLOW' ) ) exit ;

use engine\core as core ;

class Error { 



	public function __construct ( $page_error ) {


		require_once ( TEMPLATE_PATH . 'errors' . core::$global_configs [ 'OS_SEPARATOR' ] . $page_error . '.html' ) ;
		exit ;


	}


}

