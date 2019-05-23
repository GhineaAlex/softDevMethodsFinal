<?php
if ( ! defined ( 'ALLOW' ) ) exit ;

use engine\core as core ;
use engine\database as DB ;
use engine\user as __U ;
use engine\functions as __F ;

define ( 'IS_XML' , isset ( $_SERVER [ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower ( $_SERVER [ 'HTTP_X_REQUESTED_WITH' ] ) == 'xmlhttprequest' ) ;
if ( ! IS_XML ) exit ( 'No direct script access allowed!' ) ;
if ( ! isset ( $configs [ 1 ] ) ) exit ;


if ( $configs [ 1 ] === 'add_item' ) {

	if ( empty ( $_POST ) ) exit ;
	if ( self::$_user_type == 0 ) exit ( 'Nop' ) ;


	$result = __U::__add_item_cart ( __F::__protected_string_header ( 'appid' , INPUT_POST ) , __F::__protected_string_header ( 'item_id' , INPUT_POST ) , __F::__protected_string_header ( 'image' , INPUT_POST ) , __F::__protected_string_header ( 'name' , INPUT_POST ) ) ;


	if ( $result == 0 ) echo 'Nu mai poti adauga mai multe produse.' ;
	else if ( $result == 3 ) echo 'Nu ai suficiente fonduri.' ;
	else if ( $result == 1 ) {
		echo 'Item adaugat' ;
		$price = __U::__basket_price ( ) ;
?>
	<script>
		$ ( '#items_count' ) . html ( '<?= __U::__get_user_items_cart ( ) ?>' ) ;
		$ ( '#cart_price' ) . html ( '<?= $price ; ?>/<?= self::$_user_information [ 'coins' ] ; ?> LEI' ) ;
	</script>
<?php
	}
}
else if ( $configs [ 1 ] === 'remove_item' ) {

	if ( empty ( $_POST ) ) exit ;
	if ( self::$_user_type == 0 ) exit ( 'Denied' ) ;

	__U::__remove_item_cart ( __F::__protected_string_header ( 'item_id' , INPUT_POST ) ) ;
	echo 'Removed item.' ;
	$price = __U::__basket_price ( ) ;
?>
	<script>
		$ ( '#items_count' ) . html ( '<?= __U::__get_user_items_cart ( ) ?>' ) ;
		$ ( '#cart_price' ) . html ( '<?= $price ; ?>/<?= self::$_user_information [ 'coins' ] ; ?> LEI' ) ;
	</script>
<?php

}
else if ( $configs [ 1 ] === 'withdraw_items' ) {
	if ( self::$_user_type == 0 ) exit ( 'Trebuie sa te loghezi' ) ;
	if ( self::$_user_cart_count == 0 ) exit ;


	$result = DB::__db_query (
		core::$mysql_handle ,
		DB::$DB_FETCH ,
		DB::$DB_NONPROTECTED ,
		'SELECT `cart`, `trade_link`, `restricted_withdraw` FROM `accounts` WHERE `openid`=:openid' ,
		self::$_user_information [ 'openid' ]
	) ;
	$bot_trade = '<div id="withdraw_message_content" style="background: #61d334 ;">
	<i class="fa fa-times" aria-hidden="true" id="close_wi" style="position: absolute ;cursor: pointer ;top: 10px ;right: 10px ;">
	</i><h1 style="padding: 0 ;">Informatii comanda</h1>Cererea ta a fost inregistrata. Asteapta un mesaj pe email cu livrarea.
	</div><script>$ ( "#close_wi" ) . click ( function ( ) { $ ( "#withdraw_message_content" ) . fadeOut ( ) ; } ) ; </script>' ;

	if ( empty ( $result ) ) exit ;
	else {
         if (strlen($result ['cart']) == 0) echo '<div id="withdraw_message_content" style="background: #61d334 ;">
								<i class="fa fa-times" aria-hidden="true" id="close_wi" style="position: absolute ;cursor: pointer ;top: 10px ;right: 10px ;"></i>
								<h1 style="padding: 0 ;">Bot information</h1>The cart is empty.</div><script>$ ( "#close_wi" ) . click ( function ( )
								{ $ ( "#withdraw_message_content" ) . fadeOut ( ) ; } ) ; </script>';
					__U::__extract_cart ( ) ;
					echo $bot_trade ;

	}
}
else if ( $configs [ 1 ] === 'cart_drop' ) {
	if ( self::$_user_type == 0 ) exit ( 'Denied' ) ;
	$cart = __U::__get_user_cart ( ) ;
	if ( $cart !== 'NULL' ) {
		if ( ! empty ( $cart ) ) {
?>
	<div class="block">
		<table>
<?php
			foreach ( $cart as $key => $value ) {
?>
			<tr id="i_<?php echo $value -> classid ; ?>">
				<td>
					<img style="height: 50px ;" src="<?php echo $value -> image ; ?>"/>
				</td>
				<td>
					<?php echo $value -> name ; ?><br><?php echo $value -> price ; ?> LEI
				</td>
				<td>
					<img class="remove_item" item_id="<?php echo $value -> classid ; ?>" src="<?php echo TEMPLATE_URL ; ?>styles/images/remove.png" />
				</td>
			</tr>
<?php
			}
?>
		</table>
	</div>
			<script>
				$ ( '.remove_item' ) . click ( function ( ) {
					var item_id = $ ( this ) . attr ( 'item_id' ) ;
					$ ( '#i_' + item_id ) . remove ( ) ;
					$.post ( BOARD_AJAX + '/remove_item' , { 'item_id' : item_id } , function ( data ) {
						$ ( '#response' ) . fadeIn ( ) ;
						setInterval ( function ( ) { $ ( '#response' ) . fadeOut ( ) ; } , 5000 ) ;
					 	$ ( '#response' ) . html ( data ) ;
					} ) ;
				} ) ;
				$ ( '#withdraw_items' ) . click ( function ( ) {
					$.post ( BOARD_AJAX + '/withdraw_items' , { } , function ( data ) {
						$ ( '#withdraw_message' ) . html ( data ) ;
					} ) ;
				} ) ;
			</script>
			<div id="withdraw_items" style="cursor: pointer ;">Creeaza comanda</div>
<?php
		}
	}
	else {
		echo 'Empty basket.' ;
	}
}
else if ( $configs [ 1 ] === 'support' ) {
	if ( empty ( $_POST ) ) exit ;

	$support = __F::__protected_string_header ( 'support' , INPUT_POST ) ;
	if ( $support == 'Introducere Adresa' ) {
		echo '<div style="text-align: left ;padding: 5px ;font-size: 20px ; color: black;"><h1>Cum iti poti introduce adresa?</h1>Foarte usor.<br/>Dupa ce te loghezi, tot ce trebuie sa faci este sa accesezi pagina de profil din dreapta sus si sa completezi datele necesare.<br/>Dupa poti da save, iar datele tale sunt salvate.
			 <br/></div>' ;
	}
	if ( $support == 'Cum iti adaugi bani pe cont?' ) {
		echo '<div style="text-align: left ;padding: 5px ;font-size: 20px; color: black;"><h1>Cum poti adauga bani pe cont?</h1>Intra pe pagina de profil, adauga suma pe care o doresti si salveaza suma respectiva in contul tau.<br/>Este atat de simplu.<br/>
			 <br/></div>' ;
	}
	if ( $support == 'Cum poti plati?' ) {
		echo '<div style="text-align: left ;padding: 5px ;font-size: 20px; color: black;"><h1>Cum poti plati?
		</h1>Poti adauga bani pe cont sau poti plati ramburs la livrare.<br/><br/>
			 <br/></div>' ;
	}
}
else if($configs [ 1 ] === 'address'){
	if ( self::$_user_type == 0 ) exit ( 'Denied' ) ;
	if ( empty ( $_POST ) ) exit ;


	$content = __F::__protected_string ( $_POST [ 'address' ] ) ;

			DB::__db_query (
				core::$mysql_handle ,
				DB::$DB_FETCH_NONE ,
				DB::$DB_PROTECTED ,
				'UPDATE `accounts` SET `address`=:addr WHERE `openid`=:sid' ,
				$content,
				self::$_user_information [ 'openid' ]
			) ;

}
else if ($configs [1] === 'coins'){
	if( self::$_user_type == 0) exit('Denied');
	if(empty($_POST)) exit;

	$content = __F::__protected_string($_POST['coins']);
	echo $content;
	DB::__db_query(
		core::$mysql_handle,
		DB::$DB_FETCH_NONE,
		DB::$DB_PROTECTED,
		'UPDATE `accounts` SET `coins`=:coinsamount WHERE `openid`=:sid',
		$content,
		self::$_user_information['openid']
	);
}

else if ( $configs [ 1 ] === 'emailurl' ) {
	if ( self::$_user_type == 0 ) exit ( 'Trebuie sa te loghezi.' ) ;
	if ( empty ( $_POST ) ) exit ;


	$content = $_POST [ 'url' ] ;
	if ( strlen ( $content ) == 0 ) echo 'Invalid email.' ;
	else {
		$result = __U::__add_email ( $content ) ;
		if ( $result == false ) echo 'Invalid email.' ;
		else echo '<script>location.reload();</script>Succes.' ;
	}

}
