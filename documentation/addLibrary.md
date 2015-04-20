# Adding Libraries {.mds}

Let's assume that the url to your MDS client folder is at mysite.com/mds.  Go to mysite.com/mds and click on the **Add Library** link and fill out the form.  We can start by adding a library that points to the MDS Framework documentation folder.

	Alias = "markdown"
	Description = "Markdown Server Docs"
	Owner = "Me"
	Url = "http://mysite.com/Markdown-Framework/documentation"
	
	
The **Alias** is a single word ( i.e. lowercase alpha-numeric, dashes allowed) that becomes the MDS permalink for this library ( e.g. mysite.com/mds/markdown ).
	
The url **mysite.com/mds/markdown** will render as follows:

1. If the book-set folder has one .md file then that file will be rendered.
1. If the book-set folder has a file that matches the **defaultDoc** name then that file will be rendered.
1. If the book-set folder has multiple .md files and no defaultDoc then a book-set listing is displayed.

The MDS link to render this documentation page is :

	mysite.com/mds/markdown/addLibrary.md

If you want to see the contents of a folder click on the **Current book-set** link in the MDS toolbar.
	