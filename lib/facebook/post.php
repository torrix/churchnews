<?php
require_once('facebook.php');

$app_id = "";
$app_secret = "";
$your_page_id = "";

$fb = new Facebook(array(
	'appId' => $app_id,
	'secret' => $app_secret,
	'cookie' => true
));

$at = file_get_contents('at.txt');

$fb->setAccessToken($at);

//get the access token to post to your page via the graph api
$accounts = $fb->api("/me/accounts");
foreach ($accounts['data'] as $account) {
	if ($account['id'] == $your_page_id) {
		//found the access token, now we can break out of the loop
		$page_access_token = $account['access_token'];
		break;
	}
}

try {
	//publish a story to the page's wall (as the page)
	$post_id = $fb->api("/{$your_page_id}/feed", "POST", array(
		'message' => 'TEST MESSAGE 2',
		'link' => 'http://stanleyroad.org.uk/',
		'access_token' => $page_access_token
	));
	print_r($post_id);
}
catch (Exception $e) {
	echo '<pre>';
	var_dump($e);
	echo '</pre>';
}
