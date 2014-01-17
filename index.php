<?php
if (!ini_get('display_errors')) ini_set('display_errors', 1);

// parse the url to figure out which app to load
$tokens = explode('/',$_SERVER['REQUEST_URI']);
array_shift($tokens);  // remove first blank token 
array_shift($tokens); // remove app dir name ( e.g. mds )

$app = count($tokens) ? array_shift($tokens) : FALSE;

if ( $app == 'admin' ) require_once('protected/viewAdmin.php');
else require_once('protected/viewDocs.php');
?>