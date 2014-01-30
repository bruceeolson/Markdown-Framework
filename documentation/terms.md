# Terms {.mds}

The following terms are used throughout the documentation. 

For the purposes of these definitions let's assume that you have installed MDS at **mysite.com/mds** and you have added a library to MDS with the Alias => **manual** and the url => **http://document-site.com/installationManual**.

Alias
: is the permalink name given to a **Library**.  It maps a name to the http path for a Library folder.  In the example above, **manual** is the **Alias** that points to **http://document-site.com/installationManual**.

Library
: is the collection of files and folders that the **Alias** points to.

book-set
: is a collection of files that MDS recognizes.

book-set folder
: is a folder that contains a book-set.

Raw link
: is the absolute url to a .md file e.g.; **http://document-site.com/installationManual/intro.md**.  If you type this url into the browser it will display the raw contents of the intro.md file because you have instructed the browser to retrieve a specific file.

MDS link
: is the MDS url to a book-set folder OR the MDS url to an .md file in a book-set folder.

	This is an MDS link to a book-set foler : **http://mysite.com/mds/manual**.  This url will render in different ways depending on what MDS finds in the book-set.  It might render an .md file OR it might show a list of files and folders in the Library.

	This is an MDS link to a specific .md file in book-set : **http://mysite.com/mds/manual/intro.md**.  If you type this url into the browser it will invoke the MDS app to render intro.md as an html page.

