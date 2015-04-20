# Installation {.mds}

The MDS environment consists of an **application** folder and a **client** folder on your web server and distributed library folders.

## Default Installation

1. Copy or unzip the **Markdown-Framework** folder under your webroot.  This is the **application** folder.
1. Copy the **mds-client** directory to **/mds** under your webroot.  This is the **client** folder.

You webroot folder should now have these two folders:

		/Markdown-Framework
		/mds
			.htaccess
			/index.php
			/config.xml

That's it!  You are ready to go.  Open /mds in your browser and start [Adding Libraries][addLibrary].


## Custom Installation

If you put the MDS application folder or client folder somewhere other than the defaults recommended above then you will need to modify index.php in the MDS client folder.

	~~~~ .php	
	// comment out this line if you don't want to see PHP errors
	if (!ini_get('display_errors')) ini_set('display_errors', 1);
	
	define('MDS_CLIENT_BASE_PATH',dirname(__FILE__));
	define('MDS_CLIENT_BASE_URL','/mds');
	define('MDS_SERVER_BASE_URL','/Markdown-Framework');
	require_once('../Markdown-Framework/protected/main.php');
	~~~~

## config.xml

You can modify **config.xml** in the MDS client folder, if necessary.

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
	
## .htaccess

In some situations you may need to include an .htaccess file in library folders.  When MDS prepares to load a page it first requests a directory listing using the book-set folder url so that it can determine what files are there.  However, if your Apache server is configured to disable the directory listing then you need to override that for your library folder with the following.

	RewriteEngine on
	Options +Indexes


