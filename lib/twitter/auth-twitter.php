<?php
session_start();
require_once ('codebird.php');
Codebird::setConsumerKey('wtRMmSKAGW9oLOunRRo0w', 'pRiHHoWrbD91aNSWyo60aV6xf1r52Wv0KSpVIfx4CA'); // static, see 'Using multiple Codebird instances'

$cb = Codebird::getInstance();

if (! isset($_GET['oauth_verifier'])) {
    // gets a request token
    $reply = $cb->oauth_requestToken(array(
        'oauth_callback' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
    ));
	
	print_r('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	
	print_r($reply);

    // stores it
    $cb->setToken($reply->oauth_token, $reply->oauth_token_secret);
    $_SESSION['oauth_token'] = $reply->oauth_token;
    $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;

	echo '<pre>';
	print_r($_SESSION);

    // gets the authorize screen URL
    $auth_url = $cb->oauth_authorize();
    header('Location: ' . $auth_url);
    die();

} else {
    // gets the access token
    $cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
    $reply = $cb->oauth_accessToken(array(
        'oauth_verifier' => $_GET['oauth_verifier']
    ));
    // store the authenticated token, which may be different from the request token (!)
    $_SESSION['oauth_token'] = $reply->oauth_token;
    $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;
}

print_r($_SESSION);