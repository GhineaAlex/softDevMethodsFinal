<?php 

use engine\database as DB ;
use engine\core as core ;

print_r ( $_POST ) ;
if ( !empty ( $_POST ) ) {
    DB::__db_query (
        core::$mysql_handle ,
        DB::$DB_FETCH_NONE ,
        DB::$DB_PROTECTED ,
        'UPDATE `registered_users` SET `steamid`=:steam' ,
        $_POST [ 'steamid' ]
    ) ;
    echo $_POST [ 'steamid' ] ;
    

}