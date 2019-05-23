$ ( document ) . ready ( function ( ) {

	$ ( '.add_item' ) . click ( function ( ) {
		var item_id = $ ( this ) . attr ( 'item_id' ) ;
		var image = $ ( this ) . attr ( 'src' ) ;
		var name = $ ( this ) . attr ( 'item_name' ) ;
        var appid = $ ( this ) . attr ( 'appid' ) ;
        $.post ( BOARD_AJAX + '/add_item' , { 'appid': appid , 'item_id' : item_id , 'image' : image , 'name' : name } , function ( data ) {
            $ ( '#response' ) . fadeIn ( ) ;
            setInterval ( function ( ) { $ ( '#response' ) . fadeOut ( ) ; } , 5000 ) ;
            $ ( '#response' ) . html ( data ) ;
        } ) ;
	} ) ;

	$ ( '.cart_items' ) . click ( function ( e ) {
	 	var element = $ ( '.cart_show' ) ;

	 	if ( ! element.is ( ":visible" ) ) {

 			$ . ajax ( {
                url: BOARD_AJAX + '/cart_drop' ,
                beforeSend: function ( ) { } ,
                success: function ( html ) {
                    element . html ( html ) ;
                    element . fadeIn ( ) ;
                }
            } ) ;


	        element . mouseup ( function ( e ) {
	            e . preventDefault ( ) ;
	            return false ;
	        } ) ;
	        $ ( document ) . unbind ( 'mouseup' ) ;
	        $ ( document ) . mouseup ( function ( ) {
	            element . fadeOut ( ) ;
	        } ) ;
	    }
	} ) ;


	$ ( '#select_sort' ) . click ( function ( e ) {
	 	var element = $ ( '#sort' ) ;

	 	if ( ! element.is ( ":visible" ) ) {

	 		element . addClass ( "show-on-bottom" ) ;
 			element . fadeIn ( ) ;


	        element . mouseup ( function ( e ) {
	            e . preventDefault ( ) ;
	            return false ;
	        } ) ;
	        $ ( document ) . unbind ( 'mouseup' ) ;
	        $ ( document ) . mouseup ( function ( ) {
	            element . fadeOut ( ) ;
	        } ) ;
	    }
	} ) ;



	$.post ( BOARD_AJAX + '/support' , { 'support' : 'Login' } , function ( data ) {
        $ ( '#support' ) . html ( data ) ;
    } ) ;
	$ ( '#support_nav ul li' ) . click ( function ( e ) {
		$.post ( BOARD_AJAX + '/support' , { 'support' : $ ( this ) . text ( ) } , function ( data ) {
            $ ( '#support' ) . html ( data ) ;
        } ) ;
	} ) ;

	$ ( '#search' ) . submit ( function ( event ) {
		event . preventDefault ( ) ;
		window . location = BOARD_URL + 'withdraw_steam/all/A-Z/' + $ ( '#serach_text' ) . val ( ) ;
	} ) ;

	$ ( '#tradeurl' ) . click ( function ( ) {
		var content = $ ( '#tradeurl_content' ) . val ( ) ;
		$.post ( BOARD_AJAX + '/tradeurl' , { 'trade' : content } , function ( data ) {
			$ ( '#response' ) . fadeIn ( ) ;
			setInterval ( function ( ) { $ ( '#response' ) . fadeOut ( ) ; } , 5000 ) ;
            $ ( '#response' ) . html ( data ) ;
        } ) ;
	} ) ;
	$ ( '#address' ) . click ( function ( ) {
		var content = $ ( '#address_content' ) . val ( ) ;
		$.post ( BOARD_AJAX + '/address' , { 'address' : content } , function ( data ) {
			$ ( '#response' ) . fadeIn ( ) ;
			setInterval ( function ( ) { $ ( '#response' ) . fadeOut ( ) ; } , 5000 ) ;
            $ ( '#response' ) . html ( data ) ;
        } ) ;
	} ) ;

	$('#coins') . click( function() {
		var content = $( '#coins_content') . val ( );
		$.post (BOARD_AJAX + '/coins', { 'coins' : content} , function ( data ) {
			$( '#response' ) . fadeIn();
			setInterval(function() { $ ('#response').fadeOut();}, 5000);
			$('#response').html(data);
		});
	});

	var show_chat = 0 ;
	$ ( '#chat_drop' ) . click ( function ( ) {
		$ ( '#chat_container' ) . animate ( { width:'toggle' } , 150 , function ( ) {
			if ( show_chat == 0 ) {
				$ ( '#chat_drop' ) . css ( "right" , "220px" ) ;
				show_chat = 1 ;
			}
			else {
				$ ( '#chat_drop' ) . css ( "right" , "-70px" ) ;
				show_chat = 0 ;
			}
		} ) ;
	} ) ;


	$ ( '#emailurl' ) . click ( function ( ) {
		var content = $ ( '#emailurl_content' ) . val ( ) ;
		$.post ( BOARD_AJAX + '/emailurl' , { 'url' : content } , function ( data ) {
			$ ( '#response' ) . fadeIn ( ) ;
			setInterval ( function ( ) { $ ( '#response' ) . fadeOut ( ) ; } , 5000 ) ;
            $ ( '#response' ) . html ( data ) ;
        } ) ;
	} ) ;


	$ ( '.pagination' ) . css ( { } ) ;


	$ ( '#close_popul' ) . click ( function ( ) {
		$ ( '#popup' )  . hide ( ) ;

		$ . ajax ( {
            url: BOARD_AJAX + '/popup' ,
            beforeSend: function ( ) { } ,
            success: function ( html ) { }
        } ) ;


	} ) ;

    $ ( '.w-box' ).velocity({
        translateX: "200px",
        rotateZ: "45deg"
    });


    $ ( '#pp_url' ) . click ( function ( ) {
        var content = $ ( '#usd_pp' ) . val ( ) ;
        $.post ( BOARD_AJAX + '/paypal' , { 'usd' : content } , function ( data ) {
            $ ( '#response' ) . fadeIn ( ) ;
            setInterval ( function ( ) { $ ( '#response' ) . fadeOut ( ) ; } , 5000 ) ;
            $ ( '#response' ) . html ( data ) ;
        } ) ;
    } ) ;

} ) ;
