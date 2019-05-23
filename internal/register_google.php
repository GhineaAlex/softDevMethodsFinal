<?php 

define ( 'ALLOW' , true ) ;

$path = substr ( $_SERVER [ 'SCRIPT_FILENAME' ] , 0 , strpos ( $_SERVER [ 'SCRIPT_FILENAME' ] , '/internal/register.php' ) ) ;

require_once '../engine/lightopenid.php' ;
require_once '../engine/session.php' ;
require_once '../engine/functions.php' ;
require_once '../engine/hash.php' ;
require_once '../engine/google_login.php';
require_once '../global_config.php' ;


engine\session::init ( ) ;
if ( empty ( engine\session::get ( 'loggedin' ) ) ) {
    try {
		if ( isset ( $_GET [ 'code' ] ) ) {
			$gClient -> authenticate ( $_GET [ 'code' ] ) ;
			engine\session::set ( 'token' , $gClient -> getAccessToken ( ) ) ;
			header ( 'Location: ' . filter_var ( $redirectURL , FILTER_SANITIZE_URL ) ) ;
		}
		if ( !empty ( engine\session::get ( 'token' ) ) ) {
			$gClient -> setAccessToken ( engine\session::get ( 'token' ) ) ;
		}
		if ( $gClient -> getAccessToken ( ) ) {
			$gpUserProfile = $google_oauthV2 -> userinfo -> get ( ) ;

			$openid = $gpUserProfile [ 'id' ] ;
			engine\session::set ( 'provider' , 'Google' ) ;
			engine\session::set ( 'openid' , $openid ) ;
			engine\session::set ( 'personaname' , $gpUserProfile [ 'given_name' ] . ' ' . $gpUserProfile [ 'family_name' ] ) ;
			engine\session::set ( 'email' , $gpUserProfile [ 'email' ] ) ;
			engine\session::set ( 'loggedin' , 1 ) ;
			try {

                $handle = new PDO ( $GLOBAL_CONFIG [ 'DB_TYPE' ] . ':host=' . $GLOBAL_CONFIG [ 'DB_HOST' ] . ';dbname=' . $GLOBAL_CONFIG [ 'DB_DB' ] , $GLOBAL_CONFIG [ 'DB_USER' ] , $GLOBAL_CONFIG [ 'DB_PASSWORD' ] ) ;
            
                $sth = $handle -> prepare ( "SELECT `ID` FROM `accounts` WHERE `openid`=:google AND `provider`='Google'" ) ;
                $sth -> execute ( array ( 
                    ':google' => $openid
                ) ) ;
                $count = $sth -> rowCount ( ) ;
                $sth = null ;
                
                if ( $count == 0 ) {
                    $sth = $handle -> prepare ( 'INSERT INTO `accounts` (`openid`, `provider`, `email`, `personaname`, `trade_link`, `profileurl`, `avatar`, `avatar_medium`, `avatar_full`, `cart`, `chat_session`, `referral_code`, `referral_ip`) VALUES (:openid, \'Google\', :email, :pers, \'\' , :url, :a, :am, :af, \'\' , \'\', :ref_c, \'\')' ) ;
                    $sth -> execute ( array ( 
                        ':openid' => ''.$openid.'',
                        ':pers' => ( $gpUserProfile [ 'given_name' ] . ' ' . $gpUserProfile [ 'family_name' ] ) ,
                        ':email' => $gpUserProfile [ 'email' ] ,
                        ':url' => ( 'https://plus.google.com/u/0/' . $openid ) ,
                        ':a' => 'https://i.pinimg.com/originals/c9/b1/6e/c9b16eceedd12986cd5b762474103507.webp' ,
                        ':am' => 'https://i.pinimg.com/originals/c9/b1/6e/c9b16eceedd12986cd5b762474103507.webp' ,
                        ':af' => 'https://i.pinimg.com/originals/c9/b1/6e/c9b16eceedd12986cd5b762474103507.webp' ,
                        ':ref_c' => engine\Hash::create ( 'crc32' , '$' . $openid . '$' , $GLOBAL_CONFIG [ 'HASH_KEY' ] )

                    ) ) ;
					
                }
                else {
                    $sth = $handle -> prepare ( 'UPDATE `accounts` SET `personaname`=:pers, `profileurl`=:url, `avatar`=:a, `avatar_medium`=:am, `avatar_full`=:af WHERE `openid`=:openid' ) ;
                    $sth -> execute ( array ( 
                        ':pers' => $gpUserProfile [ 'given_name' ] . ' ' . $gpUserProfile [ 'family_name' ] ,
                        ':url' => 'https://plus.google.com/u/0/' . $openid ,
                        ':a' => 'https://i.pinimg.com/originals/c9/b1/6e/c9b16eceedd12986cd5b762474103507.webp' ,
                        ':am' => 'https://i.pinimg.com/originals/c9/b1/6e/c9b16eceedd12986cd5b762474103507.webp' ,
                        ':af' => 'https://i.pinimg.com/originals/c9/b1/6e/c9b16eceedd12986cd5b762474103507.webp' ,
                        ':openid' => $openid
                    ) ) ;
                }

                $handle = null ;

            }
            catch ( PDOException $e ) {
                exit ;
            }

		} else {
			$authUrl = $gClient -> createAuthUrl();
			header ( "Location: " . filter_var ( $authUrl , FILTER_SANITIZE_URL ) ) ;
		}
    }
    catch ( ErrorException $e ) {
        echo $e -> getMessage ( ) ;
    }
}
else {
    header ( "Location: " . $GLOBAL_CONFIG [ 'OPEN_URL' ] ) ;
    exit ;
}

