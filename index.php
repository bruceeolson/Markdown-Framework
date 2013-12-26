<?php

if (!ini_get('display_errors')) ini_set('display_errors', 1);

header("X-UA-Compatible: IE=Edge");
header('Content-Type: text/html; charset=utf-8');

require_once('docApp.php');

# Install PSR-0-compatible class autoloader
spl_autoload_register(function($class){
	require dirname(__FILE__).'/'.preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim($class, '\\')).'.php';
});

# Get Markdown class
use \Michelf\MarkdownExtra;
?>
<!DOCTYPE html>
<html data-json='{"linkPath":"<?php echo MDS::app()->linkPath;?>"}'>
<head>
    <title><?php echo MDS::app()->docName; ?></title>
    <?php MDS::app()->css();?>
</head>
<body>
<!-- -->
<pre>
	<?php 
	//print_r($_SERVER);
	//print_r(MDS::app()->docConfig);
	//print_r(MDS::app()); 
	//MDS::app()->test();
	?>
</pre>
<!-- -->

<div id="md-pageContainer">
    <div id="md-header"><?php MDS::app()->mdHeader();?></div>
    <div id="md-content" class="clearfix">
        <?php 
        echo MDS::app()->html();
        ?>
        <!--  MDS::app()->html() returns this structure
        <div id="md-user-header"></div>
        <div id="md-user-contentContainer">
            <div id="md-user-sidebar"></div>
            <div id="md-user-content"></div>
        </div>
        <div id="md-user-footer"></div>
        -->
        
    </div>
    <div id="md-footer"><p>MD footer</p></div>
</div> 

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>
<script src="http://yandex.st/highlightjs/7.5/highlight.min.js" type="text/javascript"></script>
<script src="<?php echo MDS::app()->baseUrl;?>/uriTransform.js" type="text/javascript"></script>
<?php /* MDS::app()->js(); */ ?> 

</body>
</html>

