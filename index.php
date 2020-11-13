<?php

//ini_set("session.cookie_domain", ".church-news.co.uk");

# ERROR REPORTING
ini_set('display_errors','On');
error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Europe/London');

# HEADERS TO PREVENT CACHING
header("Expires: Thu, 22 Mar 1984 20:45:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

# START SESSION
session_start();
ob_start();

include('db.php');
include('lib.php');

# PARSE URL INTO $args, FIRST TWO POSITIONS ARE CLASS AND METHOD TO RUN
$path = parse_url($_SERVER['REQUEST_URI']);
if ($path['path'] == '/') $path['path'] = '/home/show';
$args = explode('/',$path['path']);
array_shift($args); # REMOVES FIRST ENTRY (BLANK)
if (!isset($args[0])) $args[0] = 'Home';
if (!isset($args[1])) $args[1] = 'show';
$class = ucwords(array_shift($args));
$method = array_shift($args);

# ROUTER
include("classes/Base.php");
if (($class != 'Base') && (file_exists("classes/$class.php"))) {
	include("classes/$class.php");
	if(method_exists($class, $method)) {
		if (count($args) == 0) $obj = new $class();
		elseif (count($args) == 1) $obj = new $class($args[0]);
		elseif (count($args) > 1) $obj = new $class($args);
		$obj->$method();
	}
	else {
		$obj = new Base();
		$obj->show404();
	}
}
else {
	$obj = new Base();
	$obj->show404();
}
