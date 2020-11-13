<?php
include_once("facebook.php"); 

$app_id = "";
$app_secret = "";

$fb = new Facebook(array(
'appId' => $app_id,
'secret' => $app_secret,
'cookie' => true
));

$params = array(
scope => 'read_stream,publish_stream,offline_access,manage_pages',
redirect_uri => 'http://stanleyroad.goldcms.co.uk/lib/facebook/index.php'
);

$loginUrl = $fb->getLoginUrl($params);

if(($fb->getUser())==0)
{
header("Location:{$loginUrl}");
exit;
}
else { echo "Connected to Facebook";

file_put_contents('at.txt',$fb->getAccessToken());

}