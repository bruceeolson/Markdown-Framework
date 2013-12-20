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

class mkDoc {  // is a singleton class

	const MAIN = "main";
		
	public $basePath ='';
	public $baseUrl ='';
	public $libraryName = "Docs";
	public $docFolder;
	public $docName = 'Docs Home';
	public $docPath;
	public $docTokens;
	public $linkPath;

	private $_config;	
	private $_pageUrl;
	private $_found;
	private $_library;
		
	private static $_app;  // holds the singleton
	
	public static function app() {
		if ( !self::$_app ) self::$_app = new mkDoc;
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
		
		if ( $library && isset($this->_config['libraries'][$library]) ) {
			
			$this->_library = $this->_config['libraries'][$library];
			
			$this->docFolder = '.';
					
			$docTokens = array();
			// remove empty tokens
			foreach ( $tokens as $token ) if ( strlen($token) ) $docTokens[] = $token;
			
			$this->docTokens = $docTokens;
			
			if ( count($docTokens) == 2 ) {
				$this->docFolder = $docTokens[0];
				$this->docName = $docTokens[1];
			}
			elseif ( count($docTokens) == 1 ) {
				$this->docName = $docTokens[0];
				if ( !is_file($this->_library['path'].'/'.$this->docName.'.md') ) {
					$this->docFolder = $this->docName;
					$this->docName = self::MAIN;
				}
			}
			else $this->docName = self::MAIN;			
						
			$this->docPath = $this->_library['path'].'/'.$this->docFolder.'/'.$this->docName.'.md';
			$this->_found = is_file($this->docPath) ? TRUE : FALSE;	
			$this->linkPath = $this->baseUrl.'/'.$library.'/'.$this->docFolder;
			$this->libraryName = $library;
		}
		else $this->_library = FALSE;
		
		return $this;
	}
	
	private function homePageHtml() {
		
		$html = '<h1>Document Libraries</h1><div class="libraries">';
		foreach ( $this->_config['libraries'] as $name=>$meta )
			$html .= '<a href="'.$this->baseUrl.'/'.$name.'">'.$meta['title'].'</a>';
		$html .= '</div>';
		return $html;
	}

	public function html() {
		
		if ( !$this->_library ) return $this->homePageHtml();
		elseif ( !$this->_found ) return '<p>Document not found.</p>';
		else {
			
			$path = $this->_library['path'];
					
			$mdVariables = '';
						
			$mdVariablesFile = $path.'/'.$this->docFolder.'/variables.md';
			
			if ( $this->docFolder && is_file($mdVariablesFile) ) $mdVariables = file_get_contents($mdVariablesFile);			
			
			$text = file_get_contents($this->docPath).$mdVariables;
			$my_html = \Michelf\MarkdownExtra::defaultTransform($text);
			return \Michelf\MarkdownExtra::defaultTransform($text);
		}
	}
	
	
}


class docAsset {
	
	public $found;
	public $error;
	public $content;
	public $result;
	
	public function __construct($uri) {
		
		$this->result = self::cUrl($uri);
		
	}
		
	// handles calls to JSONDataServlet
	private static function cUrl($url) {
			
		$options = array(
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
		
		
		$ch      = curl_init($url);
		curl_setopt_array($ch,$options);
		$content = curl_exec($ch);
		$err     = curl_errno($ch);
		$errmsg  = curl_error($ch) ;
		$header  = curl_getinfo($ch);
		curl_close($ch);
		
		return array(
					'payload' 	=> $content,
					'error'		=> $err,
					'message'	=> $errmsg,
					'header'	=> $header,
					);
	}
	
}
