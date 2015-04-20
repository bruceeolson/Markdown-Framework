<?php
// comment out this line if you don't want to see PHP errors
if (!ini_get('display_errors')) ini_set('display_errors', 1);

define('MDS_CLIENT_BASE_PATH',dirname(__FILE__));
define('MDS_CLIENT_BASE_URL','/mds');
define('MDS_SERVER_BASE_URL','/mds-app');
require_once('../mds-app/protected/main.php');
?>