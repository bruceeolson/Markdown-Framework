# Markdown Documentation Framework

The MD cloud service allows you to publish markdown libraries.  A MD library is a folder that contains markdown files, an **mkConfig.xml** file, and **book** folders.  **book** folders do NOT contain subfolders.

Here is an example of an MD library folder.

	mdConfig.xml
	main.md
	variables.md
	header.md
	footer.md
	/MyFirstNovel
	/MySecondNovel

None of these files is required.


Each MD library can have a config.xml file that looks like this.  If no config.xml file is found then the defaults are used.

~~~~.xml
<mdConfig>
	<title>My Markdown Library</title>
	<path></path>
	<css>
		<uri>http://mysite.com/github.css</uri>
		<uri>http://mysite.com/markdown.css</uri>
	</css>
	<js>
		<uri>http://mysite/.com/markdown.js</uri>
	</js>
	<html>
		<div id="header"/>
		<div id="content"/>
		<div id="footer"/>
	</html>
	<selectors>
		<header>#header</header>
		<content>#content</content>
		<footer>#footer</footer>
		<sidebar>#sidebar</sidebar>
	</selectors>
	<main>main.md</content>
	<header>header.md</header>
	<footer>footer.md</footer>
	<sidebar>sidebar.md</sidebar>
	<variables>variables.md</variables>
</mdConfig>
~~~~

## variables.md



## Security

If you only want your files to be accessible to MD just put the following code in an .htaccess file in your MD library directory.

	# deny all except those indicated here
	<Limit GET POST PUT>
	 order deny,allow
	 deny from all
	 allow from .*MD\.com.*
	</Limit>

You can also encrypt your files and supply MD with the **key**.

You can define a user authentication call for your library.


