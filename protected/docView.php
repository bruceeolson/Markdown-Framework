<?php

header("X-UA-Compatible: IE=Edge");
header('Content-Type: text/html; charset=utf-8');

require_once('docApp.php');
?>
<!DOCTYPE html>
<html data-json='{"mdsLinkPath":"<?php echo MDS::app()->mdsLinkPath;?>", "absolutePath":"<?php echo MDS::app()->rawBooksetUrl;?>"}'>
<head>
    <title><?php echo MDS::app()->pageTitle; ?></title>
    <?php MDS::app()->css();?>
</head>
<body>
<!--
<pre>
	<?php 
	//print_r(MDS::app()->properties());
	//print_r($_SERVER);
	//print_r(MDS::app()->docConfig);
	//print_r(MDS::app()); 
	//MDS::app()->test();
	?>
</pre>
-->

<div id="md-pageContainer">
    <div id="md-header"><?php MDS::app()->mdHeader();?></div>
    <div id="md-content" class="partialBlock clearfix">
        <?php echo MDS::app()->content(); ?>
        <!--  MDS::app()->html() returns this structure
        <div id="md-user-header"></div>
        <div id="md-user-contentContainer">
            <div id="md-user-sidebar"></div>
            <div id="md-user-content"></div>
        </div>
        <div id="md-user-footer"></div>
        -->
    </div>
    <div id="md-footer"><div class="partialBlock"><p>Markdown Server <?php echo MDS_VERSION;?></p></div></div>
</div> 

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>
<script src="http://yandex.st/highlightjs/7.5/highlight.min.js" type="text/javascript"></script>
<script src="<?php echo MDS_SERVER_BASE_URL;?>/js/uriTransform.js" type="text/javascript"></script>
<?php MDS::app()->js(); ?> 

</body>
</html>

