# MDS Documents Framework {.mds}

### Topics
<div id="ui-toc" markdown="1">
* [_config.xml](#config)
* _header.md, _footer.md, _sidebar.md
* _variables.md
* [images](#images)
* links
</div>

The MDS documents framework defines a set of files that can be used to customize the rendering of your .md source files.  All of these files are OPTIONAL.  It also describes how links and images in your page are resolved.

For the purposes of this section let's look at the book-set (shown below) that defines the rendering of the MDS documentation.  It can be found at **/mds/documentation** in the Download package. 

	/mds
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

The /mds folder is optional and contains a number of files that define custom rendering.

## _config.xml  {#config}

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

The **defaultDoc** property tells MDS which .md file in the book-set to load when the url references a book-set folder.  The default is **main**.

The **showToolbar** property turns the MDS toolbar at the top of the page on or off.  The default is **yes**.

The **css** section tells MDS what CSS files to load.  By default MDS always loads a stylesheet called github.css.  If you don't want that to load then change **includeDefault** to **no**.  Each path to a CSS files is relative to the book-set folder or is an absolute http path.

The **js** section tells MDS what javascript assets to load.  Again, paths are relative to the book-set folder OR can be absolute http paths (e.g. http://js-site.com/js/my.js ).

## _header.md, _footer.md, _sidebar.md (Optional)

If MDS finds any of these files in your book-set then it adds them to the page.  Typically you would put a table of contents for your book-set  in the _sidebar.md file.

## _variables.md (Optional)

This file has the markdown variables that are used when converting your markdown text to html.

## Document Links and Hashes

MDS transforms **relative** links and hashes in your documents to **MDS links** before rendering.  For example if you have the following link defined in your .md file :

	[Overview](overview.md)
	
the Markdown compiler translates that to the following html :
	
	<a href="overview.md">Overview</a>
	
and then MDS converts the href to :

	<a href="http://mysite.com/mds/libraryAlias/bookSetFolderName/overview.md">Home</a>
	
## Images {#images}

MDS transforms **relative** `<img>` references in your documents to **absolute** uri's before rendering.

	![pretty picture](pretty.jpg "Pretty")
	
	<img src="pretty.jpg" alt="pretty picture" title="Pretty">
	
becomes :

	<img src="http://yourLibraryFolderSite.com/bookSetFolderName/pretty.jpg" alt="pretty picture" title="Pretty">


