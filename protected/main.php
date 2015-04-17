<?php

define('MDS_VERSION','1.2');
define('MDS_LIBRARIES_XML',MDS_CLIENT_BASE_PATH.'/config.xml');

require_once('mdsCurl.php'); 

if ( isset($_SERVER['REDIRECT_URL']) ) {  // url must contain information
	$tokens = explode('/',$_SERVER['REDIRECT_URL']);
	$baseUrlTokens = explode('/',MDS_CLIENT_BASE_URL);
	foreach ( $baseUrlTokens as $token ) array_shift($tokens);  // remove ../mds
	
	$token =  array_shift($tokens);  // {library name} | admin
	
	if ( $token == 'admin' ) {
		
		$token =  array_shift($tokens);  // create | update | delete
	
		if ( in_array($token, array('create','update','delete')) ) {
			define('MDS_ACTION',$token);
			define('MDS_LIBRARY',array_shift($tokens));
			require_once('adminView.php');
		}
	}
	else {
		define('MDS_LIBRARY',$token);
		define('MDS_DOC',implode("/",$tokens));  // folder/document.md | document.md
		require_once('docView.php');
	}
}
else {  // mds home page
	define('MDS_LIBRARY',FALSE);
	require_once('docView.php');
}
