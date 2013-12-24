# PHP Markdown Server (MDS)

MDS is a PHP framework that renders a special collection of **markdown** files (*.md) called a MDS **book-set**.  The framework uses [PHP Markdown Extra by Michel Fortin](http://http://michelf.ca/projects/php-markdown/) to convert *.md files to html and [highlight.js](http://highlightjs.org/) to style code blocks. This framework is particularly useful for software development shops because it appears to centralize documentation that resides on distributed web-accessible servers.  Under the MDS framework, the rendering is centralized and the documents are distributed.

In order to use the system just install the MDS framework and add MDS library pointers to the **config.php** file.  An MDS library pointer is simply a url that points to a web accessible folder somewhere.

An MDS library folder contains collection of files called a **book-set**.  A **book-set** is a collection of files that MDS recognizes.  Notice that the ONLY file that is required is the **_index.md** file.
	
	// book-set
	_mdConfig.xml
	_index.md			(required)
	variables.md
	header.md
	footer.md
	sidebar.md
	style.css
	main.js

An MDS library folder can contain book-set **sub-folders**.  Here is an example of an MD library which has a **root** book-set and two book-set folders ( Book1 and Book2).  The Book2 folder has a minimal book-set.

	_mdConfig.xml
	_index.md			(required)
	variables.md
	header.md
	footer.md
	style.css
	main.js
	/Book1
		_mdConfig.xml
		_index.md		(required)
		chapter1.md
		chapter2.md
		chapter3.md
		variables.md
		header.md
		footer.md
		sidebar.md
		style.css
	/Book2
		_index.md		(required)



## _mdConfig.xml  (Optional)

Each MD folder can have a **_mdConfig.xml** file that declares custom CSS and JS assets to include in the rendered page.

~~~~.xml
<mdConfig>
	<css>
		<uri>style.css</uri>
	</css>
	<js>
		<uri>main.js</uri>
	</js>
</mdConfig>
~~~~

## _index.md (REQUIRED)

Typically the **_index.md** file is a table of contents for the **book-set**.  However it doesn't need to be.  It can be the main document.

## variables.md (OPTIONAL)

## Document Links

MDS transforms relative links in your documents to **MDS links** before rendering.  For example if you have the following link defined in your .md file :

	[Home](home)
	
	<a href="home">Home</a>
	
becomes :

	<a href="/mds/libraryName/bookSetFolderName/home">Home</a>
	
## Images

MDS transforms relative `<img>` references in your documents to **absolute** uri's before rendering.

	![alt text](img.jpg "Title")
	
	<img src="img.jpg" alt="text">
	
becomes :

	<img src="http://yourLibraryPointerUrl/bookSetFolderName/img.jpg">


## .htaccess (REQUIRED)

You must have an **.htaccess** file in the root of your library folder which returns a 404 when MD is requesting a file that doesn't exist.   This happens because MD is looking for certain files that you may not have included because they are optional (e.g. _mdConfig.xml).  Put the following in your .htaccess file.

	RewriteEngine on
	
	# if the directory or file doesn't exist, redirect to index.php
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule . - [L,R=404]

## Security

If you only want your files to be accessible to MD just add the following code to your .htaccess file.

	# deny all except those indicated here
	<Limit GET POST PUT>
	 order deny,allow
	 deny from all
	 allow from .*MD\.com.*
	</Limit>

You can also encrypt your files and supply MD with the **key**.

You can define a user authentication call for your library.


