<?php

# INCLUDE ADODB LIBRARY
require_once('adodb/adodb.inc.php');

# SET THE FETCH MODE TO ONLY RETURN COLUMN NAMES FOR ARRAY INDEXES.
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

# SET DB CONNECTION DETAILS
$dbinfo['server']	= 'localhost';
$dbinfo['type']		= 'mysql';
$dbinfo['db']		= '';
$dbinfo['user']		= '';
$dbinfo['password']	= '';

# CREATE A NEW CONNECTION
$db = ADONewConnection($dbinfo['type']);
$db->PConnect($dbinfo['server'],$dbinfo['user'],$dbinfo['password'],$dbinfo['db']);