# Installation {.mds}

The MDS environment consists of the **application**, a **server**, and distributed **libraries**.

## Default Installation

1. Copy or unzip the **Markdown-Framework** folder under your webroot in a folder named **mds-app**.  This is the **application**.
1. Copy the **mds** directory to **/mds** under your webroot.  This is the **server**.

The webroot should now have these two folders:

		/mds-app
		/mds
			.htaccess
			index.php
			config.xml

That's it!  You are ready to go.  Open /mds in your browser and start [Adding Libraries][addLibrary].


## Custom Installation

If you put the MDS application folder or server folder somewhere other than the defaults recommended above then you will need to modify index.php in the MDS **server** folder.

### index.php

~~~~ .php	
// comment out this line if you don't want to see PHP errors
if (!ini_get('display_errors')) ini_set('display_errors', 1);

// DO NOT MODIFY
define('MDS_SERVER_BASE_PATH',dirname(__FILE__));
define('MDS_SERVER_BASE_URL',preg_replace('/(.+)\/index\.php$/',"$1",$_SERVER['PHP_SELF']));
														
// MODIFY these paths if necessary
define('MDS_APP_BASE_URL','/mds-app');
require_once('../mds-app/protected/main.php');
~~~~

You can modify **config.xml** in the MDS client folder, if necessary.

### config.xml

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


