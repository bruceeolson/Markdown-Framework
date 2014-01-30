# MDS Documents Framework {.mds}

### Topics
<div id="ui-toc" markdown="1">
* [_config.xml](#config)
* _header.md, _footer.md, _sidebar.md
* _variables.md
* [images](#images)
* links
</div>

For the purposes of this section let's look at the book-set (shown below) used for the MDS documentation.  It can be found at **/mds/documentation** in the Download package. 

	_config.xml
	_variables.md
	_header.md
	_footer.md
	_sidebar.md
	addLibrary.md
	comingSoon.md
	documentsFramework.md
	installation.md
	overview.md
	releaseNotes.md
	terms.md
	style.css
	main.js



## _config.xml  (Optional)  {#config}

The _config.xml file tells MDS how to build the page.  For example, you can tell MDS to load specific CSS and JS assets.

Here is a sample _config.xml file.

~~~~.xml
<mdConfig>
	<defaultDoc>overview</defaultDoc>
	<showToolbar>yes</showToolbar>
	<css>
		<includeDefault>yes</includeDefault>
		<uri>style.css</uri>
	</css>
	<js>
		<uri>main.js</uri>
	</js>
</mdConfig>
~~~~

The **defaultDoc** property tells MDS which file to load when no file is specified in the url.  The default is **main**.

The **showToolbar** property turns the MDS toolbar at the top of the page on or off.  The default is **yes**.

The **css** section tells MDS what CSS files to load.  By default it loads a stylesheet called github.css.  If you don't want that to load then change **includeDefault** to **no**.  The path to CSS files is relative to the book-set folder.

The **js** section tells MDS what javascript assets to load.

## _header.md, _footer.md, _sidebar.md (Optional)

If MDS finds any of these files in your book-set then it adds them to the page.  Typically you would put a table of contents section in your _sidebar.md file.

## _variables.md (Optional)

This file has the markdown variables that are used when converting your markdown text to html.

## Document Links and Hashes

MDS transforms **relative** links and hashes in your documents to **MDS links** before rendering.  For example if you have the following link defined in your .md file :

	[Home](home.md)
	
the Markdown compiler translates that to the following html :
	
	<a href="home.md">Home</a>
	
and then MDS converts the href in the `<a>` element to :

	<a href="http://mysite.com/mds/libraryName/bookSetFolderName/home">Home</a>
	
## Images {#images}

MDS transforms **relative** `<img>` references in your documents to **absolute** uri's before rendering.

	![alt text](img.jpg "Title")
	
	<img src="img.jpg" alt="text">
	
becomes :

	<img src="http://yourLibraryFolderSite.com/bookSetFolderName/img.jpg">


