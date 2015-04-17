<?php

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
	public $docConfig;
	public $pageTitle;
	

	private $_config;				// library config array
	private $_library;
	private $_isLibraryFolder;
	public $_mdsConfig;
	
	public $rawBooksetUrl;
	public $rawDocUrl;
	private $_docName;
	private $_booksetFolder;
	private $_mdsFolder;
	private $_dom;
	
	
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
		$this->baseUrl = MDS_CLIENT_BASE_URL;
				
		// load the config for this library
		// the value for MDS_LIBRARIES_XML is set in client index.php
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
			if ( MDS_LIBRARY ) $libraryNode = $config->xpath('//alias[text()="'.MDS_LIBRARY.'"]/..');
			else $libraryNode = FALSE;
		} 
		else {
			$this->_mdsConfig = $this->_defaultMDSconfig;
			$libraryNode = FALSE;
		}
		
		// change allowAddLibrary from yes/no string to boolean
		$this->_mdsConfig['allowAddLibrary'] = strtolower($this->_mdsConfig['allowAddLibrary']) == "yes" ? TRUE : FALSE;
				
		// look for this library in the config	
		if ( $libraryNode ) {
			$libraryNode = $libraryNode[0];
			$uri = $libraryNode->xpath('./url');
			$this->_library = array('uri'=>$uri[0][0]);
			$this->libraryName = MDS_LIBRARY;
			$this->initLibrary();
		}
		else {
			$this->_library = FALSE;
			$this->pageTitle = "MDS Home";
		}
		
		return $this;
	}  // construct()
	
	
	private function initLibrary() {
		
		$this->_isLibraryFolder = FALSE;
		$docFolder = NULL;
		$docName = NULL;		
		$docTokens = array();
		
		$tokens = explode('/',MDS_DOC);
		
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
		//$this->doc = new docAssets($this->libraryName, $this->_library['uri'], $docFolder, $docName);
		
		$this->_docName = $docName;
		$booksetFolder = $this->_library['uri'] . ($docFolder ? '/'.$docFolder : '');
		
		$this->_dom = new DOMDocument('1.0', 'UTF-8');
		$this->_dom->validateOnParse = true;
		$this->_dom->loadHTML('<html/>');
		
		$bookset = $this->_booksetFolder = new mdsFolder($booksetFolder);
		$this->_mdsFolder = new mdsFolder($booksetFolder.'/mds');
				
		if ( $this->_mdsFolder->found ) $this->variables = $this->_mdsFolder->fileContent('_variables.md');
		else $this->variables = '';
				
		// if there is only one NON MDS .md file then assign it to name
		if ( count($bookset->mdFiles) == 1 ) $this->_docName = $bookset->mdFiles[0];	
		
		$this->rawBooksetUrl = $booksetFolder;
		$this->rawDocUrl = $booksetFolder.'/'.$this->_docName;
												
		// initialize the config array
		if ( $this->_mdsFolder->hasFile('_config.xml')  ) {
			$configXML = simplexml_load_string($this->_mdsFolder->fileContent('_config.xml'));
			$json = json_encode($configXML);
			$userConfig = json_decode($json,TRUE);
			$this->docConfig = array_merge($this->_defaultDocConfig, $userConfig);		
		}
		else $this->docConfig = $this->_defaultDocConfig;
		
		// make sure the defaultDoc has an .md extension
		if ( !preg_match('/.+\.md$/',$this->docConfig['defaultDoc']) ) $this->docConfig['defaultDoc'] .= '.md';
		
		// if no file specified and the defaultDoc file exists then use it
		if ( !$this->_docName && in_array($this->docConfig['defaultDoc'],$this->_booksetFolder->mdFiles) )
			$this->_docName = $this->docConfig['defaultDoc'];		
			
		$this->pageTitle  = $this->libraryName;
		$this->pageTitle .= $docFolder ? ':'.$docFolder : '';
		$this->pageTitle .= ':'.$this->_docName;
				
	}  // initLibrary()
	
	
	private function addCSSelement($cssUri, $linkOwner='mds') {
		
		$prefix = '<link rel="stylesheet" type="text/css" href="';
		$suffix = '"/>';
				
		if ( preg_match('/^(\/\/|http).*/',$cssUri) ) { /*  do nothing */ }
		//elseif ( $linkOwner == 'mds' )  $cssUri = $this->baseUrl.'/css/'.$cssUri;
		elseif ( $linkOwner == 'mds' )  $cssUri = MDS_SERVER_BASE_URL.'/css/'.$cssUri;
		elseif ( $linkOwner == 'user' ) $cssUri = $this->_booksetFolder->baseUrl.'/'.$cssUri;
		
		// send css to the page
		echo $prefix.$cssUri.$suffix."\n";
	}
	
	private function addJSelement($jsUri, $linkOwner='mds') {
		
		$prefix = '<script type="text/javascript" src="';
		$suffix = '"></script>';
				
		if ( preg_match('/^(\/\/|http).*/',$jsUri) ) { /*  do nothing */ }
		//elseif ( $linkOwner == 'mds' )  $jsUri = $this->baseUrl.'/js/'.$jsUri;
		elseif ( $linkOwner == 'mds' )  $jsUri = MDS_SERVER_BASE_URL.'/js/'.$jsUri;
		elseif ( $linkOwner == 'user' ) $jsUri = $this->_booksetFolder->baseUrl.'/'.$jsUri;
		
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
		
		$bookset = $this->_booksetFolder;
		
		$config = $this->docConfig;
		$html = array();
		$html[] = '<ul class="partialBlock">';
		
		if ( $this->_mdsConfig['allowAddLibrary'] )
			$html[] = '<li><a href="'.$this->baseUrl.'/admin/create'.'">Add Library</a></li>';

		$html[] = '<li><a href="'.$this->baseUrl.'">Libraries</a></li>';
		
		if ( $this->_library )
			$html[] = '<li>Current Library => <a href="'.$this->baseUrl.'/'.$this->libraryName.'">'.$this->libraryName.'</a></li>';
		
		// add a link to display the book-set for the current folder
		if ( isset($_GET['showFolder']) ) { /*  do nothing because we are showing the book-set */ }
		elseif ( $this->_library && $bookset->found ) 
			$html[] = '<li><a href="'.$this->mdsLinkPath.'?showFolder=1">Current book-set</a></li>';
		else { /*  do nothing */ }
		
						
		//$html[] = '<li>'.$config['title'].'</li>';
		//$html[] = '<li>'.$config['author']['name'].'</li>';
		if ( $this->_docName && !isset($_GET['showFolder']) ) {
			$href = $this->_booksetFolder->baseUrl.'/'.$this->_docName;
			$html[] = '<li><a href="'.$href.'" title="'.$href.'">Raw Document</a></li>';
		}
		$html[] = '</ul>';
		echo implode("\n",$html);
	}
	
	private function loadAsset($div, $payload) {
										
		if ( !strlen($payload) ) $div->parentNode->removeChild($div);
		else {
					
			$html = \Michelf\MarkdownExtra::defaultTransform($payload.$this->variables);
			
			$newdoc = new DOMDocument;
			$newdoc->formatOutput = true;
			
			// @ suppresses warning messages
			@$newdoc->loadHTML('<div class="md-tmp-node">'.$html.'</div>');
			
			$newxpath = new DOMXpath($newdoc);	
					
			$newnode = $newxpath->query('//div[@class="md-tmp-node"]')->item(0);	
			$newnode = $this->_dom->importNode($newnode,true);
			
			$div->appendChild($newnode);
		}
	}
	
	// removes the extra tags that DOMDoc puts around an html fragment by default
	private function removeWrapperTags($html)  {
		return preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $html));	
	}
	
	
	public function pageContent() { 
												
		$dom = $this->_dom;
		
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
				
		if ( $this->_mdsFolder->hasFile('_sidebar.md') ) {
			$contentContainer->setAttribute('class','clearfix');
			$contentDiv->setAttribute('class','partial');
		}
				
		$this->loadAsset($headerDiv,  $this->_mdsFolder->fileContent('_header.md'));
		$this->loadAsset($sidebarDiv, $this->_mdsFolder->fileContent('_sidebar.md'));
		$this->loadAsset($contentDiv, $this->_booksetFolder->fileContent($this->_docName));
		$this->loadAsset($footerDiv,  $this->_mdsFolder->fileContent('_footer.md'));
			
		return $this->removeWrapperTags($dom->saveHTML()); 
	}
	

	public function content() {
		
		$b = $this->_booksetFolder;
		
		if ( !$this->_mdsConfig['configFound'] ) return '<p>ERROR : MDS_LIBRARIES_XML in index.php contains path to invalid or missing .xml config file.</p>';
		elseif ( !$this->_library ) return $this->homePageHtml();
		elseif ( isset($_GET['showFolder']) ) return $this->htmlMdFiles();
		
		$docFilename = $this->_docName;
		
		if ( !$this->_docName ) return $this->htmlMdFiles();
		elseif ( !$b->hasFile($docFilename) ) return '<p>'.$b->baseUrl.'/'.$docFilename.' not found.</p>';
		else return \Michelf\MarkdownExtra::defaultTransform($this->pageContent());
	}
	
	private function htmlMdFiles() {
		
		$bookset = $this->_booksetFolder;
		$folderName = $this->_isLibraryFolder ? 'library root' : $bookset->name;
		
		$html = array();
		$html[] = '<h1>.md files and folders in the '.$folderName.' folder</h1>';
		$html[] = '<ul class="md-folder">';
		
		// list of .md files
		foreach ( $bookset->mdFiles as $docName )
			$html[] = '<li><a href="'.$this->mdsLinkPath.'/'.$docName.'">'.$docName.'</a></li>';

		// list of folders
		foreach ( $bookset->folders as $folder )
			$html[] = '<li><a href="'.$this->mdsLinkPath.'/'.$folder.'">/'.$folder.'</a></li>';
			
		// if no .md files or folders then show message
		if ( (count($bookset->mdFiles) + count($bookset->folders)) == 0 )
			$html[] = '<li>Folder is empty.</li>';
			
		$html[] = '</ul>';
		return implode("\n",$html);
	}
	
	
	public function test() {
	}
	
	
	
}



class mdsFolder {
	
	public $files = array();
	public $folders = array();
	public $mdFiles  = array();
	public $name;
	
	public $found;
	public $baseUrl;
	public $mdsLink;
	
	public function __construct($url) {
		$this->baseUrl = $url;
		$this->name = basename($url);
		$folder = new docAsset($url);
		$this->found = $folder->found;
		if ( $this->found ) $this->parseFolder($folder);
	}
	
	private function parseFolder($folder) {
		
		// parse the payload to find what files and folders exist
		$dir = new DOMDocument('1.0', 'UTF-8');
		$dir->validateOnParse = true;
		$dir->loadHTML($folder->payload);
		
		$xpath = new DOMXpath($dir);
		$items = $xpath->query('//a');
		
		foreach ( $items as $item) {
			
			// don't process the link to the parent directory
			if ( preg_match('/Parent Directory/',$item->nodeValue) ) continue;
			
			$filename = trim($item->getAttribute('href'));
			
			if ( preg_match('/.+\.md$/',$filename) ) $this->mdFiles[] = $filename;
			
			if ( preg_match('/.+\/$/',$filename) ) $this->folders[] = substr($filename,0,-1);
			else $this->files[] = $filename;
		}
	}
	
	public function fileContent($filename) {
		
		if ( $this->hasFile($filename) ) {
			$asset = new docAsset($this->baseUrl.'/'.$filename);
			if ( $asset->found ) return $asset->payload;
			else return '';
		}
		else return '';
	}
	
	
	public function hasFile($name) { return in_array($name,$this->files); }
	public function hasFolder($name) { return in_array($name,$this->folders); }
	
}
