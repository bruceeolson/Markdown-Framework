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
<html data-json='{"linkPath":"<?php echo mkDoc::app()->linkPath;?>"}'>
<head>
    <title><?php echo mkDoc::app()->docName; ?></title>
    <?php mkDoc::app()->css();?>
</head>
<body>
<!--
<pre>
	<?php 
	//print_r($_SERVER);
	//print_r(mkDoc::app()->docConfig);
	//print_r(mkDoc::app()); 
	//mkDoc::app()->test();
	?>
</pre>
-->

<div id="md-pageContainer">
    <div id="md-header"><?php mkDoc::app()->mdHeader();?></div>
    <div id="md-content" class="clearfix">
        <?php 
        echo mkDoc::app()->html();
        ?>
    </div>
    <div id="md-footer"><p>MD footer</p></div>
</div> 

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>
<script src="http://yandex.st/highlightjs/7.5/highlight.min.js" type="text/javascript"></script>
<script src="<?php echo mkDoc::app()->baseUrl;?>/uriTransform.js" type="text/javascript"></script>
<?php /* mkDoc::app()->js(); */ ?> 

</body>
</html>

