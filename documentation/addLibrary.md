# Adding Libraries {.mds}

Let's assume that you have installed MDS at mysite.com/mds.  Go to mysite.com/mds and click on the **Add Library** link and fill out the form.  We can start by adding a library for the MDS Documentation.

	Alias = "markdown"
	Description = "Markdown Server Docs"
	Owner = "Me"
	Url = "http://mysite.com/mds/documentation"
	
	
The **Alias** is a single word ( i.e. lowercase alpha-numeric, dashes allowed) that becomes the permalink to the library. The MDS link to this library is  :

	mysite.com/mds/markdown
	
which points to http://mysite.com/mds/documentation.  
	
MDS will render a page for the url **mysite.com/mds/markdown** as follows:

1. If the book-set folder has one .md file then that file will be rendered.
2. If the book-set folder has a file that matches the **defaultDoc** name then that file will be rendered.
3. If the book-set folder has multiple .md files and no defaultDoc then a book-set listing is displayed.

The MDS link to render this documentation page is :

	mysite.com/mds/markdown/addLibrary.md

If you want to see the contents of a folder click on the **Current book-set** link in the MDS toolbar.
	