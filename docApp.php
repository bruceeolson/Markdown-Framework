<?php

/*

/docs [ /library [ /folder | /filename ] | /folder/filename ] 

Sample urls :

.../docs
.../docs/ui
.../docs/ui/releaseNotes
.../docs/ui/framework
.../docs/ui/framework/toc

*/

class MDS {  // is a singleton class

	const MAIN = "main";
	const ELEMENT_PREFIX = 'md';
	
	const ID_HEADER 	= 'md-user-header';
	const ID_CONTENT 	= 'md-user-content';
	const ID_SIDEBAR 	= 'md-user-sidebar';
	const ID_FOOTER 	= 'md-user-footer';
		
	public $basePath ='';
	public $baseUrl ='';
	public $libraryName = "Docs";
	public $docFolder;
	public $docName = 'Docs Home';
	public $docBaseUrl;
	public $docTokens;
	public $linkPath;
	public $docConfig;

	private $_config;	// library config array
	private $_pageUrl;
	private $_found;
	private $_library;
	private $_tmpTokens;
	private $_page;  // mkPageContent object
	

	private $_defaultDocConfig = array(
								'title' => 'Undefined',
								'author' => array('name'=>'Undefined', 'email'=>''),
								'css' => array(),
								'js' => array(),
								);
								
			
	private static $_app;  // holds the singleton
	
	public static function app() {
		if ( !self::$_app ) self::$_app = new MDS;
		return self::$_app;
	}
	
	private function __construct() {
		
		$this->_config = require('config.php');	
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
		
		$this->_tmpTokens = $tokens;
		
		if ( $library && isset($this->_config['libraries'][$library]) ) {
			$this->_library = $this->_config['libraries'][$library];
			$this->libraryName = $library;
			$this->init();
		}
		else $this->_library = FALSE;
		
		return $this;
	}
	
	private function cssElement($cssUri, $linkOwner='mdDocsHref') {
		$prefix = '<link rel="stylesheet" type="text/css" href="';
		$suffix = '"/>';
		
		// if css is a relative uri then prefix with docBaseUrl
		if ( $linkOwner == 'userHref' && !preg_match('/^http.*/',$cssUri) )
			$cssUri = $this->docBaseUrl.'/'.$cssUri;
		
		return $prefix.$cssUri.$suffix;
	}
	
	public function css() {
		
		$css = array();
		$css[] = $this->cssElement($this->baseUrl.'/css/github.css');
		
		
		// load user supplied css
		$cssUri = isset($this->docConfig['css']['uri']) ? $this->docConfig['css']['uri'] : FALSE;
				
		if ( $cssUri && is_array($cssUri) )
			foreach ( $cssUri as $cssFile ) $css[] = $this->cssElement($cssFile, 'userHref');
		elseif ( $cssUri ) $css[] = $this->cssElement($cssUri, 'userHref');
		
		$css[] = $this->cssElement($this->baseUrl.'/css/main.css');
		$css[] = $this->cssElement('http://yandex.st/highlightjs/7.5/styles/default.min.css');
		
		echo implode("\n",$css);
	}
	
	private function init() {
		
		$this->docFolder = '.';
				
		$docTokens = array();
		
		// remove empty tokens
		foreach ( $this->_tmpTokens as $token ) if ( strlen($token) ) $docTokens[] = $token;
		
		$this->docTokens = $docTokens;
		
		if ( count($docTokens) == 2 ) {
			$this->docFolder = $docTokens[0];
			$this->docName = $docTokens[1];
		}
		elseif ( count($docTokens) == 1 ) {
			$this->docName = $docTokens[0];
			
			$test = new docAsset($this->_library['uri'].'/'.$this->docName.'.md');
			
			if ( !$test->found ) {
				$this->docFolder = $this->docName;
				$this->docName = self::MAIN;
			}
			
		}
		else $this->docName = self::MAIN;			
					
		$this->docBaseUrl = $this->_library['uri'].'/'.$this->docFolder;				
		$this->linkPath = $this->baseUrl.'/'.$this->libraryName.'/'.$this->docFolder;
		
		// initialize the config array
		$config = new docAsset($this->docBaseUrl.'/_mdConfig.xml');
		if ( $config->found ) {
			$configXML = simplexml_load_string($config->payload);
			$json = json_encode($configXML);
			$userConfig = json_decode($json,TRUE);
			$this->docConfig = array_merge($this->_defaultDocConfig, $userConfig);		
		}
		else $this->docConfig = $this->_defaultDocConfig;
		
		$this->_page = new mdPageContent($this);
	}
	
	private function homePageHtml() {
		
		$html = array();
		$html[] = '<h1>Markdown Server Document Libraries</h1>';
		$html[] = '<div id="md-libraries">';
		foreach ( $this->_config['libraries'] as $name=>$meta )
			$html[] = '<ul><li><a href="'.$this->baseUrl.'/'.$name.'">'.$name.'</a></li><li>'.$meta['title'].'</li></ul>';
		$html[] = '</div>';
		return implode("\n",$html);
	}
	
	public function mdHeader() {
		
		$config = $this->docConfig;
		$html = array();
		$html[] = '<ul>';
		//$html[] = '<li>'.$config['title'].'</li>';
		//$html[] = '<li>'.$config['author']['name'].'</li>';
		$html[] = '<li>'.$this->docBaseUrl.'/'.$this->docName.'.md</li>';
		$html[] = '</ul>';
		echo implode("\n",$html);
	}
	

	public function html() {
		if ( !$this->_library ) return $this->homePageHtml();
		elseif ( !$this->_page->found ) return '<p>'.$this->_page->uri.' not found.</p>';
		else return \Michelf\MarkdownExtra::defaultTransform($this->_page->content());
	}
	
	public function test() {
		//$config = new docAsset($this->docBaseUrl.'/_mdConfig.xml');
		
		$config = new docAsset($this->docBaseUrl.'/xyz.md');
		
		print_r($config);
	}
	
	
	
}

class mdPageContent {
	
	public $dom, $uri, $found;
	
	private $_xpath, $_content, $_path, $_html, $_variables, $_mds;
	
	public function __construct($mds) {
		$this->_mds = $mds;
		$this->_path = $mds->docBaseUrl;
		$this->_content	= new docAsset($this->_path.'/'.$mds->docName.'.md');
		$this->found = $this->_content->found;
		$this->_html = $mds->docConfig['html'];
		$this->uri = $this->_content->uri;
	}
	
	private function load($div, $pageComponent) {
										
		if ( !$pageComponent->found ) $div->parentNode->removeChild($div);
		else {
			
			$mdString = $pageComponent->payload;
		
			$html = \Michelf\MarkdownExtra::defaultTransform($mdString.$this->_variables);
			
			$newdoc = new DOMDocument;
			$newdoc->formatOutput = true;
			$newdoc->loadHTML('<div class="md-tmp-node">'.$html.'</div>');
			
			$newxpath = new DOMXpath($newdoc);	
					
			$newnode = $newxpath->query('//div[@class="md-tmp-node"]')->item(0);	
			$newnode = $this->dom->importNode($newnode,true);
			
			$div->appendChild($newnode);
		}
	}
	
	// removes the extra tags that DOMDoc puts around an html fragment by default
	private function removeWrapperTags($html)  {
		return preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $html));	
	}
	
	
	public function content() { 
			
		$variables = new docAsset($this->_path.'/variables.md');
		$this->_variables = $variables->payload;
		
		$header		= new docAsset($this->_path.'/header.md');
		$footer		= new docAsset($this->_path.'/footer.md');
		$sidebar	= new docAsset($this->_path.'/sidebar.md');
						
		$dom = $this->dom = new DOMDocument('1.0', 'UTF-8');
		$dom->validateOnParse = true;
		
		$dom->loadHTML('<html></html>');
						
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
				
		if ( $sidebar->found ) {
			$contentContainer->setAttribute('class','clearfix');
			$contentDiv->setAttribute('class','partial');
		}
				
		$this->load($headerDiv, $header);
		$this->load($sidebarDiv, $sidebar);
		$this->load($contentDiv, $this->_content);
		$this->load($footerDiv, $footer);
			
		return $this->removeWrapperTags($dom->saveHTML()); 
	}
}


class docAsset {
	
	public $uri, $payload, $error, $message, $found;
	
	private static $_options = array(
			CURLOPT_RETURNTRANSFER => true,         // return web page
			CURLOPT_HEADER         => false,        // don't return headers
			CURLOPT_FOLLOWLOCATION => true,         // follow redirects
			CURLOPT_ENCODING       => "",           // handle all encodings
			CURLOPT_AUTOREFERER    => true,         // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 30,          // timeout on connect
			CURLOPT_TIMEOUT        => 30,          // timeout on response
			CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects
			CURLOPT_POST           => 0,        // i am sending post data
			CURLOPT_POSTFIELDS     => '',		// this are my post vars
			CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
			CURLOPT_SSL_VERIFYPEER => false,        //
			CURLOPT_VERBOSE        => 1                //
		);
		
	
	public function __construct($uri) {
		
		$this->uri = $uri;
		
		$ch = curl_init($uri);
				
		curl_setopt_array($ch,self::$_options);
		
		$this->payload	= curl_exec($ch);
		$this->error	= curl_errno($ch);
		$this->message  = curl_error($ch) ;
		$httpHeader		= curl_getinfo($ch);
		$this->httpCode	= $httpHeader['http_code'];
		$this->found	= $this->httpCode == 200 ? TRUE : FALSE;
		if ( !$this->found || $this->error ) $this->payload = '';
		
		curl_close($ch);
	}
			
}
