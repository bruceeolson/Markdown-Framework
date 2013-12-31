# PHP Markdown Server (MDS)

MDS is a PHP framework for rendering **markdown** files (i.e. *.md).  It is particularly useful for software development shops because it appears to centralize documentation that resides in different places.  Under the MDS framework, the rendering is centralized but the documents can be anywhere.

In order to use the system just install the MDS framework and point it to web-accessible folder(s) that have .md files.

The MDS framework uses [PHP Markdown Extra by Michel Fortin](http://michelf.ca/projects/php-markdown/) to convert *.md files to html and [highlight.js](http://highlightjs.org/) to style code blocks. 


An MDS library folder contains a collection of files called a **book-set** which are files that MDS recognizes.

An MDS folder can have a book-set with a single file, such as :

	document.md
	
However, MDS also looks for **special** .md files and a **_config.xml** file in the book-set that can further define the page rendering.  More on that later.  For example, a book-set can look like this :
	
	document.md
	_config.xml
	_variables.md
	_header.md
	_footer.md
	_sidebar.md
	style.css
	main.js

An MDS library folder can have book-set **sub-folders**.  Here is an example of an MD library which has a **root** book-set consisting of one file, index.md, and two book-set folders ( Book1 and Book2).

	index.md
	/Book1
		chapter1.md
		chapter2.md
		chapter3.md
		diagram.pdf
		_config.xml
		_variables.md
		_header.md
		_footer.md
		_sidebar.md
		style.css
	/Book2
		document.md


## _config.xml  (Optional)

Use **_config.xml** to include CSS and JS assets in the rendered page.  If no CSS files are specified then MDS loads a version of github.css for styling purposes.

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


## _variables.md (Optional)

## Document Links and Hashes

MDS transforms **relative** links and hashes in your documents to **MDS links** before rendering.  For example if you have the following link defined in your .md file :

	[Home](home.md)
	
the Markdown compiler translates that to the following html :
	
	<a href="home.md">Home</a>
	
and MDS converts the href in the `<a>` element to :

	<a href="/mds/libraryName/bookSetFolderName/home">Home</a>
	
## Images

MDS transforms **relative** `<img>` references in your documents to **absolute** uri's before rendering.

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


