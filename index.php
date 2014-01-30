<?php

// comment out this line if you don't want to see PHP errors
if (!ini_get('display_errors')) ini_set('display_errors', 1);

// modify the path to your mdsLibraries.xml file if necessary
define('MDS_LIBRARIES_XML',dirname(__FILE__).'/../mdsLibraries.xml');

require_once('protected/main.php');

?>