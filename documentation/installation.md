# Installation {.mds}

1. Copy or unzip the mds folder under your webroot somewhere.
2. Create an .xml file (e.g. mdsLibraries.xml) and put the code below in the file.  Ideally this file should be located at the same level as the /mds directory not inside it.

	~~~~ .xml
	<?xml version="1.0" encoding="UTF-8"?>
	<libraries>
		<config>
			<allowAddLibrary>yes</allowAddLibrary>
			<title>Markdown Libraries</title>
		</config>
	</libraries>
	~~~~
	
	This file maintains the metadata for web-accesible library folders and it gives you a couple of configuration options.  The web application must have write priviledges to this file.
	
3. If necessary, modify the value assigned to **MDS_LIBRARIES_XML** in **/mds/index.php** so that it points correctly to your .xml file.

	~~~~ .php	
	// modify the path to mdsLibraries.xml if necessary
	define('MDS_LIBRARIES_XML',dirname(__FILE__).'/../mdsLibraries.xml');
	
	require_once('protected/main.php');
	~~~~

4. Open /mds in your browser and start [Adding Libraries][addLibrary].

## .htaccess

In some situations you may need to include an .htaccess file in your Library folder.  When MDS prepares to load a page it first requests a directory listing for the book-set folder so it can determine what files are there.  However, if your Apache server is configured to disable the directory listing then you need to override that for your Library folder with the following.

	RewriteEngine on
	Options +Indexes


