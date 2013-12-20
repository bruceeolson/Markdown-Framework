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
<html>
<head>
    <title><?php echo mkDoc::app()->docName; ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo mkDoc::app()->baseUrl;?>/github.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo mkDoc::app()->baseUrl;?>/markdown.css" />
    <link rel="stylesheet" href="http://yandex.st/highlightjs/7.5/styles/default.min.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>
    <script src="http://yandex.st/highlightjs/7.5/highlight.min.js"></script>
    <script>
    $(function() { 
	
		var docBaseUrl = '<?php echo mkDoc::app()->linkPath;?>';
		$('code').each(function(i, e) {hljs.highlightBlock(e)});
		
		// modify all of the <a> links
		$('a').each(function() {
			var   href = $(this).attr('href')
				, relativeLink = href.slice(0,1) == '/' ? false : true
				;
			if ( relativeLink ) $(this).attr('href',docBaseUrl+'/'+href)
		});
		
	});     
    </script>   
</head>
<body>
<!-- 
<pre><?php print_r($_SERVER); ?></pre> 
<pre><?php print_r(mkDoc::app()); ?></pre>
-->

<?php echo mkDoc::app()->html();?>
</body>
</html>

