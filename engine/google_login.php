<?php
	include_once 'src/Google_Client.php';
	include_once 'src/contrib/Google_Oauth2Service.php';

	$clientId = '657723902870-2qoln15936tq5294o2p4kko8up4n47um.apps.googleusercontent.com';
	$clientSecret = 'X_Sl3HnOqBocAyEKzOmfNse8';
	$redirectURL = 'http://localhost/html/internal/register_google.php';

	$gClient = new Google_Client();
	$gClient->setApplicationName('Antsy.xyz');
	$gClient->setClientId($clientId);
	$gClient->setClientSecret($clientSecret);
	$gClient->setRedirectUri($redirectURL);
	$google_oauthV2 = new Google_Oauth2Service($gClient);
?>
