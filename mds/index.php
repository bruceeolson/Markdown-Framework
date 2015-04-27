<?php
// comment out this line if you don't want to see PHP errors
if (!ini_get('display_errors')) ini_set('display_errors', 1);

// DO NOT MODIFY
define('MDS_SERVER_BASE_PATH',dirname(__FILE__));
define('MDS_SERVER_BASE_URL',preg_replace('/(.+)\/index\.php$/',"$1",$_SERVER['PHP_SELF']));
														
// MODIFY these paths if necessary
define('MDS_APP_BASE_URL','/mds-app');
require_once('../mds-app/protected/main.php');
?>