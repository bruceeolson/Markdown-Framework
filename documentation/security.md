# Security {.mds}

If you only want your files to be accessible to MD just create an .htaccess file in your Library folder with the following:

	# deny all except those indicated here
	<Limit GET POST PUT>
	 order deny,allow
	 deny from all
	 allow from .*mysite\.com.*
	</Limit>

You can also encrypt your files and supply MD with the **key**.

You can define a user authentication call for your library.


