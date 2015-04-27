# Overview {.mds}

**MDS** is a PHP framework for rendering distributed **markdown** files (i.e. *.md).  It is particularly useful for software development shops because it appears to centralize documentation that resides on different servers.  Under the MDS framework, the rendering is centralized but the documents can be anywhere.  You simply tell your MDS server where **web accessible** folders containing .md files are located and it renders them.

The MDS Framework provides a number of customizable page rendering options.

MDS uses [PHP Markdown Extra by Michel Fortin](http://michelf.ca/projects/php-markdown/) to convert *.md files to html and [highlight.js](http://highlightjs.org/) to style programming language code blocks. 

An MDS library folder contains a collection of files called a **book-set**. A book-set can consist of a single file, such as :

	document.md
	
or a book-set can have multiple .md files, css files, js files, and a /mds directory containing a _config.xml file and special rendering files (i.e. files that start with the underscore character such as _sidebar.md).  More on how all these files come in to play later.  Here is an example book-set :
	
	chapter1.md
	chapter2.md
	/mds
		_config.xml
		_variables.md
		_header.md
		_footer.md
		_sidebar.md
	style.css
	main.js

An MDS library can also have book-set **sub-folders**.  Here is an example of an MDS library that has a **root** book-set consisting of one file, **index.md**, and two book-set sub-folders, **Book1** and **Book2**.

	index.md
	/Book1
		chapter1.md
		chapter2.md
		chapter3.md
		diagram.pdf
		/mds
			_config.xml
			_variables.md
			_header.md
			_footer.md
			_sidebar.md
		style.css
	/Book2
		document.md

When you reference an .md file the page is rendered using the supporting files in your book-set.  If you have a _header.md file in your book-set it includes that at the top.  If you have a _sidebar.md file it includes that on the left.

For instructions on how all these files work together see [MDS Documents Framework][mdsAuthoring].
