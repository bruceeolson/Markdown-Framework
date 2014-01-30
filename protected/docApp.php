<?php

use \Exception;

require_once('mdsCurl.php'); 

# Install PSR-0-compatible class autoloader
spl_autoload_register(function($class){
	require dirname(__FILE__).'/'.preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim($class, '\\')).'.php';
});

# Get Markdown class
use \Michelf\MarkdownExtra;

/*

/docs [ /library [ /folder | /filename ] | /folder/filename ] 

Sample urls :

.../docs
.../docs/ui
.../docs/ui/releaseNotes
.../docs/ui/releaseNotes/main.md
.../docs/ui/framework
.../docs/ui/framework/main.md

*/

class MDS {  // is a singleton class

	const ELEMENT_PREFIX = 'md';
	
	const ID_HEADER 	= 'md-user-header';
	const ID_CONTENT 	= 'md-user-content';
	const ID_SIDEBAR 	= 'md-user-sidebar';
	const ID_FOOTER 	= 'md-user-footer';
	
	private static $_configFile;  // passed in on init()
		
	public $basePath ='';
	public $baseUrl ='';
	public $libraryName = "Docs";
	public $mdsLinkPath;   			// the mds link to this document
	public $docBaseAbsolutePath;  	// the absolute link to this document
	public $docConfig;
	public $doc;					// contains a docAssets object
	public $pageTitle;
	

	private $_config;				// library config array
	private $_library;
	private $_isLibraryFolder;
	public $_mdsConfig;
	
	private $_defaultMDSconfig = array(
					'title' => 'Markdown Libraries',
					'allowAddLibrary' => TRUE,
					'configFound' => FALSE,
	);

	private $_defaultDocConfig = array(
										'defaultDoc' => 'main.md',
										'showToolbar' => 'yes',
										'css' => array('css/github.css'),
										'js' => array(),
	);
				
	private static $_app;  // holds the singleton
	
	public static function app() {
		if ( !self::$_app ) self::$_app = new MDS;
		return self::$_app;
	}
	
	private function __construct() {
		
		$this->basePath = dirname(__FILE__);
		
		if ( isset($_SERVER['REDIRECT_URL']) )
			$tokens = explode('/',$_SERVER['REDIRECT_URL']);
		else {
			$tokens = explode('/',$_SERVER['PHP_SELF']);
			array_pop($tokens);  // remove the index.php
		}
				
		array_shift($tokens);  // remove the empty token from the beginning of the array
		$this->baseUrl = '/'.array_shift($tokens);
					
		$library = array_shift($tokens);
		
		// load the config for this library
		// the value for MDS_LIBRARIES_XML is set in index.php
    	$config = simplexml_load_file(MDS_LIBRARIES_XML);
		if ( $config ) {
			$mdsConfig = $config->xpath('//config');
			if ( $mdsConfig ) {		
				$json = json_encode($mdsConfig[0]);
				$mdsConfig = json_decode($json,TRUE);
				$this->_mdsConfig = array_merge($this->_defaultMDSconfig, $mdsConfig);
				$this->_mdsConfig['configFound'] = TRUE;
			}
			else $this->_mdsConfig = $this->_defaultMDSconfig;
			$libraryNode = $config->xpath('//alias[text()="'.$library.'"]/..');
		} 
		else {
			$this->_mdsConfig = $this->_defaultMDSconfig;
			$libraryNode = NULL;
		}
		
		// change allowAddLibrary from yes/no string to boolean
		$this->_mdsConfig['allowAddLibrary'] = strtolower($this->_mdsConfig['allowAddLibrary']) == "yes" ? TRUE : FALSE;
				
		// look for this library in the config	
		if ( $libraryNode ) {
			$libraryNode = $libraryNode[0];
			$uri = $libraryNode->xpath('./url');
			$this->_library = array('uri'=>$uri[0][0]);
			$this->libraryName = $library;
			$this->initLibrary($tokens);
		}
		else {
			$this->_library = FALSE;
			$this->pageTitle = "MDS Home";
		}
		
		return $this;
	}  // construct()
	
	
	private function initLibrary($tokens) {
		
		$this->_isLibraryFolder = FALSE;
		$docFolder = NULL;
		$docName = NULL;		
		$docTokens = array();
		
		// remove empty tokens
		foreach ( $tokens as $token ) if ( strlen($token) ) $docTokens[] = $token;
		
		$this->docTokens = $docTokens;
		
		if ( count($docTokens) > 2 ) {
			// invalid url
		}
		elseif ( count($docTokens) == 2 ) {
			$docFolder = $docTokens[0];
			$docName = $docTokens[1];
		}
		elseif ( count($docTokens) == 1 ) {  // token is either a .md file OR a folder
			$docName = $docTokens[0];
			if ( !preg_match('/.+\.md$/',$docName) ) {  // token is a folder
				$docFolder = $docName;
				$docName = NULL;
			}
		}
		elseif ( count($docTokens) == 0 ) $this->_isLibraryFolder = TRUE;
		
		$this->mdsLinkPath = $this->baseUrl.'/'.$this->libraryName;
		if ( $docFolder !== NULL ) $this->mdsLinkPath .= '/'.$docFolder;
		
						
		// initialize the doc property with a docAssets object
		$this->doc = new docAssets($this->libraryName, $this->_library['uri'], $docFolder, $docName);
		
		$this->docBaseAbsolutePath = $this->doc->baseUrl;
												
		// initialize the config array
		if ( $this->doc->exists('_config.xml')  ) {
			$configXML = simplexml_load_string($this->doc->content('_config.xml'));
			$json = json_encode($configXML);
			$userConfig = json_decode($json,TRUE);
			$this->docConfig = array_merge($this->_defaultDocConfig, $userConfig);		
		}
		else $this->docConfig = $this->_defaultDocConfig;
		
		// make sure the defaultDoc has an .md extension
		if ( !preg_match('/.+\.md$/',$this->docConfig['defaultDoc']) ) $this->docConfig['defaultDoc'] .= '.md';
		
		// if no file specified and the defaultDoc file exists then use it
		if ( !$this->doc->name && in_array($this->docConfig['defaultDoc'],$this->doc->mdFiles) )
			$this->doc->name = $this->docConfig['defaultDoc'];		
			
		$this->pageTitle = $this->libraryName.':'.$this->doc->folder.':'.$this->doc->name;
				
	}  // initLibrary()
	
	
	private function addCSSelement($cssUri, $linkOwner='mds') {
		
		$prefix = '<link rel="stylesheet" type="text/css" href="';
		$suffix = '"/>';
				
		if ( preg_match('/^(\/\/|http).*/',$cssUri) ) { /*  do nothing */ }
		elseif ( $linkOwner == 'mds' )  $cssUri = $this->baseUrl.'/css/'.$cssUri;
		elseif ( $linkOwner == 'user' ) $cssUri = $this->doc->baseUrl.'/'.$cssUri;
		
		// send css to the page
		echo $prefix.$cssUri.$suffix."\n";
	}
	
	private function addJSelement($jsUri, $linkOwner='mds') {
		
		$prefix = '<script type="text/javascript" src="';
		$suffix = '"></script>';
				
		if ( preg_match('/^(\/\/|http).*/',$jsUri) ) { /*  do nothing */ }
		elseif ( $linkOwner == 'mds' )  $jsUri = $this->baseUrl.'/js/'.$jsUri;
		elseif ( $linkOwner == 'user' ) $jsUri = $this->doc->baseUrl.'/'.$jsUri;
		
		// send js element to the page
		echo $prefix.$jsUri.$suffix."\n";
	}
	
	private function getConfigAssets($type) {  // $type = css | js
		
		$list = array();
		$useDefault = TRUE;		
		
		// extract user css assets from docConfig
		$assets = isset($this->docConfig[$type]['uri']) ? $this->docConfig[$type]['uri'] : FALSE;		
		if ( $assets && is_array($assets) ) {
			if ( in_array('no-default', $assets) ) $useDefault = FALSE;
			foreach ( $assets as $filepath ) 
				if ( $filepath != 'no-default' ) $list[] = $filepath;
		}
		elseif ( $assets ) $list[] = $assets;
		
		return array('useDefault'=>$useDefault, 'assets'=>$list);
	}
	
	public function css() {
		$settings = $this->getConfigAssets('css');
		$useDefault = $settings['useDefault'];
		$cssAssets = $settings['assets'];
		
		// now insert the CSS in the page
		$this->addCSSelement('http://yandex.st/highlightjs/7.5/styles/default.min.css');
		foreach ( $cssAssets as $cssUri ) $this->addCSSelement($cssUri, 'user');
		if ( $useDefault ) {
			$this->addCSSelement('github.css');
		}
		$this->addCSSelement('main.css');
	}
	
	public function js() {
		$settings = $this->getConfigAssets('js');
		$jsAssets = $settings['assets'];
		foreach ( $jsAssets as $jsUri ) $this->addJSelement($jsUri, 'user');
	}
	
	private function homePageHtml() {

		$configXML = simplexml_load_file(MDS_LIBRARIES_XML);
		
		$libraries = $configXML->xpath('//library');
				
		$html = array();
		$html[] = '<h1>'.$this->_mdsConfig['title'].'</h1>';
		$html[] = '<table id="md-libraries">';
		$html[] = '<tr><th>Alias</th><th>Title</th><th>Owner</th><th>Url</th><th></th><th></th></tr>'; 
		
		if ( $libraries )
			foreach ( $libraries as $library ) {
				$html[] = '<tr>';
				$html[] = '<td><a href="'.$this->baseUrl.'/'.$library->alias.'">'.$library->alias.'</a></td>';
				$html[] = '<td>'.$library->title.'</td>';
				$html[] = '<td>'.$library->owner.'</td>';
				$html[] = '<td>'.$library->url.'</td>';
				$html[] = '<td><a href="'.$this->baseUrl.'/admin/update/'.$library->alias.'">Edit</a></td>';
				$html[] = '<td><a href="'.$this->baseUrl.'/admin/delete/'.$library->alias.'">Delete</a></td>';
				$html[] = '</tr>';
			}
		$html[] = '</table>';
		return implode("\n",$html);
	}
	
	public function mdHeader() {
		
		if ( preg_match('/[nN][oO]/',$this->docConfig['showToolbar']) ) return;
		
		$config = $this->docConfig;
		$html = array();
		$html[] = '<ul class="partialBlock">';
		
		if ( $this->_mdsConfig['allowAddLibrary'] )
			$html[] = '<li><a href="'.$this->baseUrl.'/admin/create'.'">Add Library</a></li>';

		if ( $this->_library )
			$html[] = '<li><a href="'.$this->baseUrl.'">Libraries</a></li>';
		
		if ( $this->doc )
			$html[] = '<li>Current Library => <a href="'.$this->baseUrl.'/'.$this->libraryName.'">'.$this->libraryName.'</a></li>';
		
		// add a link to display the book-set for the current folder
		if ( isset($_GET['showFolder']) ) { /*  do nothing because we are showing the book-set */ }
		elseif ( $this->_library && $this->doc && $this->doc->folder && $this->doc->name ) 
			$html[] = '<li><a href="'.$this->baseUrl.'/'.$this->libraryName.'/'.$this->doc->folder.'?showFolder=1">Current book-set</a></li>';
		elseif ( $this->_library && $this->doc && !$this->doc->folder && $this->doc->name ) 
			$html[] = '<li><a href="'.$this->baseUrl.'/'.$this->libraryName.'?showFolder=1">Current book-set</a></li>';
		else { /*  do nothing */ }
		
						
		//$html[] = '<li>'.$config['title'].'</li>';
		//$html[] = '<li>'.$config['author']['name'].'</li>';
		if ( $this->doc && $this->doc->name && !isset($_GET['showFolder']) ) {
			$href = $this->doc->baseUrl.'/'.$this->doc->name;
			$html[] = '<li><a href="'.$href.'" title="'.$href.'">Raw Document</a></li>';
		}
		$html[] = '</ul>';
		echo implode("\n",$html);
	}
	
	private function loadAsset($div, $payload) {
										
		if ( !strlen($payload) ) $div->parentNode->removeChild($div);
		else {
					
			$html = \Michelf\MarkdownExtra::defaultTransform($payload.$this->doc->variables);
			
			$newdoc = new DOMDocument;
			$newdoc->formatOutput = true;
			
			// @ suppresses warning messages
			@$newdoc->loadHTML('<div class="md-tmp-node">'.$html.'</div>');
			
			$newxpath = new DOMXpath($newdoc);	
					
			$newnode = $newxpath->query('//div[@class="md-tmp-node"]')->item(0);	
			$newnode = $this->doc->dom->importNode($newnode,true);
			
			$div->appendChild($newnode);
		}
	}
	
	// removes the extra tags that DOMDoc puts around an html fragment by default
	private function removeWrapperTags($html)  {
		return preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $html));	
	}
	
	
	public function pageContent() { 
												
		$dom = $this->doc->dom;
		
		$xpath = $this->_xpath = new DOMXpath($dom);
		
		$root = $xpath->query('//html')->item(0);		
		
		$headerDiv = $dom->createElement("div");
		$headerDiv->setAttribute('id','md-user-header');
		$root->appendChild($headerDiv);
				
		$contentContainer = $dom->createElement("div");
		$contentContainer->setAttribute('id','md-user-contentContainer');
		$root->appendChild($contentContainer);
		
		$sidebarDiv = $dom->createElement("div");
		$sidebarDiv->setAttribute('id','md-user-sidebar');
		$contentContainer->appendChild($sidebarDiv);
		
		$contentDiv = $dom->createElement("div");
		$contentDiv->setAttribute('id','md-user-content');
		$contentContainer->appendChild($contentDiv);
		
		$footerDiv = $dom->createElement("div");
		$footerDiv->setAttribute('id','md-user-footer');
		$root->appendChild($footerDiv);
				
		if ( $this->doc->exists('_sidebar.md') ) {
			$contentContainer->setAttribute('class','clearfix');
			$contentDiv->setAttribute('class','partial');
		}
				
		$this->loadAsset($headerDiv,  $this->doc->content('_header.md'));
		$this->loadAsset($sidebarDiv, $this->doc->content('_sidebar.md'));
		$this->loadAsset($contentDiv, $this->doc->content($this->doc->name));
		$this->loadAsset($footerDiv,  $this->doc->content('_footer.md'));
			
		return $this->removeWrapperTags($dom->saveHTML()); 
	}
	

	public function content() {
		
		if ( !$this->_mdsConfig['configFound'] ) return '<p>ERROR : MDS_LIBRARIES_XML in index.php contains path to invalid or missing .xml config file.</p>';
		elseif ( !$this->_library ) return $this->homePageHtml();
		elseif ( isset($_GET['showFolder']) ) return $this->doc->htmlMdFiles();
		
		$docFilename = $this->doc->name;
		
		if ( !$this->doc->name ) return $this->doc->htmlMdFiles();
		elseif ( !$this->doc->exists($docFilename) ) return '<p>'.$this->doc->baseUrl.'/'.$docFilename.' not found.</p>';
		else return \Michelf\MarkdownExtra::defaultTransform($this->pageContent());
	}
	
	public function test() {
		//$config = new docAsset($this->docBaseUrl.'/_mdConfig.xml');
		
		$config = new docAsset($this->docBaseUrl.'/xyz.md');
		
		print_r($config);
	}
	
	
	
}


class docAssets {
		
	public $baseUrl, $library, $folder, $name, $files=array(), $dom, $variables;
	
	public $mdFiles = array();
	public $mdFolders = array();
		
	private $mdsAssets = array(
								'_header.md',
								'_footer.md',
								'_sidebar.md',
								'_variables.md',
								'_config.xml'
	);
	
	public function __construct($libraryName, $libraryUrl, $docFolder, $docName) {
				
		$this->baseUrl = $libraryUrl;
		$this->library = $libraryName;
								
		if ( $docFolder ) {	
			$this->folder = $docFolder;	
			$this->baseUrl .= '/'.$docFolder;
		}
		
		$this->name = $docName;
		
		$this->dom = new DOMDocument('1.0', 'UTF-8');
		$this->dom->validateOnParse = true;
		$this->dom->loadHTML('<html/>');
		
		// get the target folder contents
		$main = new docAsset($this->baseUrl);
		
		// parse the payload to find what files and folders exist
		$dir = new DOMDocument('1.0', 'UTF-8');
		$dir->validateOnParse = true;
		$dir->loadHTML($main->payload);
		
		$xpath = new DOMXpath($dir);
		$items = $xpath->query('//a');
		
		foreach ( $items as $item) {
			
			// don't process the link to the parent directory
			if ( preg_match('/Parent Directory/',$item->nodeValue) ) continue;
			
			$filename = trim($item->getAttribute('href'));
			$this->files[] = $filename;
			
			if ( preg_match('/.*\.md$/',$filename) && !in_array($filename, $this->mdsAssets) ) $this->mdFiles[] = $filename;
			if ( preg_match('/.+\/$/',$filename) ) $this->mdFolders[] = substr($filename,0,-1);
		}
		
		// if there is only one NON MDS .md file then assign it to name
		if ( count($this->mdFiles) == 1 ) $this->name = $this->mdFiles[0];
		
		$this->variables = $this->content('_variables.md');
	}
	
	public function exists($filename) { return in_array($filename,$this->files); }
	
	public function content($filename) {
		if ( $this->exists($filename) ) {
			$asset = new docAsset($this->baseUrl.'/'.$filename);
			if ( $asset->found ) return $asset->payload;
			else return '';
		}
		else return '';
	}
	
	public function htmlMdFiles() {
		$html = array();
		$folderName = $this->folder ? $this->folder.' folder' : $this->library.' library root';
		
		$html[] = '<h1>.md files and folders in the '.$folderName.'</h1>';
		$html[] = '<ul class="md-folder">';
		
		// list of .md files
		foreach ( $this->mdFiles as $docName )
			$html[] = '<li><a href="'.MDS::app()->baseUrl.'/'.MDS::app()->libraryName.'/'.$this->folder.'/'.$docName.'">'.$docName.'</a></li>';

		// list of folders
		foreach ( $this->mdFolders as $folder )
			$html[] = '<li><a href="'.MDS::app()->baseUrl.'/'.MDS::app()->libraryName.'/'.$folder.'">/'.$folder.'</a></li>';
			
		// if no .md files or folders then show message
		if ( (count($this->mdFiles) + count($this->mdFolders)) == 0 )
			$html[] = '<li>Folder is empty.</li>';
			
		$html[] = '</ul>';
		return implode("\n",$html);
	}
}


