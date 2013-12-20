<?php
return array(

	'libraries' => array(
	
		'markdown' => array(
					'title' 	=> 'Markdown Server Framework',
					'path' 		=> '/Applications/MAMP/htdocs/docs/documentation',
					'uri' 		=> 'http://localhost:8888/docs/markdown', // http://missingkids.com/markdown/main.md'
					'css' 		=> 'http://markdown.css',
					'js'		=> '',
					'main' 		=> 'main.md',
					'header' 	=> 'header.md',
					'footer' 	=> 'footer.md',
					'variables'	=> 'variables.md',
					'shaKey'	=> '',
					'author'	=> array('Bruce Olson','bolson@ncmec.org')
					),
					
		'ui' => array(
					'title' 	=> 'UI Framework Developer Documents',
					'path' 		=> '/Applications/MAMP/htdocs/ui/documentation',
					'main' 		=> 'main.md',
					)
	)
);