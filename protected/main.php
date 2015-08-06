<?php

defined('MDS_DS') || define('MDS_DS',DIRECTORY_SEPARATOR);
define('MDS_VERSION','1.2');
define('MDS_LIBRARIES_XML',MDS_SERVER_BASE_PATH.MDS_DS.'config.xml');

require_once('mdsCurl.php');

if ( isset($_SERVER['REDIRECT_URL']) ) {  // url must contain information
	$mds_dirname = basename(MDS_SERVER_BASE_URL);
	$request = preg_replace('/.*\/'.$mds_dirname.'\/(.*)/',"$1",$_SERVER['REDIRECT_URL']);	
	$tokens = explode('/',$request);
		
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
		//require_once('test.php');
	}
}
else {  // show mds home page
	define('MDS_LIBRARY',FALSE);
	require_once('docView.php');
}
