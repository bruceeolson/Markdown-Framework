<?php

define('MDS_VERSION','1.2');

if ( preg_match('/\/admin\/(create|update|delete).*/',$_SERVER['REQUEST_URI']) ) 
	require_once('adminView.php');
else 
	require_once('docView.php');