<?php

include 'mdsCurl.php';

class mdsRegister {
	
	private static $_app;
	
	public $form;
	public $libraryUrl;
	public $success = FALSE;
		
	private function __construct() {
		
		$inputs = isset($_POST['Register']) ? $_POST['Register'] : FALSE;
		$this->form = new mdsRegistrationForm($inputs);
				
		if ( $this->form->submitted && $this->form->isValid && $this->libraryExists() ) {
		//if ( $this->form->submitted && $this->form->isValid ) {
			
			$this->success = TRUE;
			
			if ( isset($_SERVER['REDIRECT_URL']) )
				$tokens = explode('/',$_SERVER['REDIRECT_URL']);
			else {
				$tokens = explode('/',$_SERVER['PHP_SELF']);
				array_pop($tokens);  // remove the index.php
			}
					
			array_shift($tokens);  // remove the empty token at the beginning of the url
			$this->mdsBaseUrl = '/'.array_shift($tokens);
			
			$this->libraryUrl = $this->mdsBaseUrl.'/'.$this->form->alias;
		}
		
	}
	
	public static function app() {
		if ( !self::$_app ) self::$_app = new mdsRegister;
		return self::$_app;
	}
	
	public function libraryExists() {
				
		$folder = new docAsset($this->form->url);
		
		if ( $folder->found ) {
			
			$fileConfigXML = dirname(__FILE__).'/config.xml';
			
			// add library to config.xml if it doesn't already exist
			$config = new DOMDocument('1.0', 'UTF-8');
			$config->preserveWhiteSpace = FALSE;
			$config->load($fileConfigXML);
								
			$xpath = new DOMXpath($config);
			$libExists = $xpath->query('//library/url[contains(., "'.$this->form->url.'")]');
									
			if ( $libExists->length ) $this->form->addError('This library url already exists.');
			else {
				// add library to config.xml
				$root = $xpath->query('//libraries')->item(0);		
				
				$library = $config->createElement("library");
				$library->setAttribute('alias',$this->form->alias);
				$root->appendChild($library);
								
				$title = $config->createElement("title");
				$title->nodeValue = $this->form->title;
				$library->appendChild($title);
				
				$url = $config->createElement("url");
				$url->nodeValue = $this->form->url;
				$library->appendChild($url);
				
				file_put_contents($fileConfigXML,$config->saveXML());
			}
			
		}
		else $this->form->addError('Can\'t find folder using this url.');
						
		return $this->form->isValid;
	}


}  // mdsRegisterApp class



class mdsRegistrationForm {
	
	public $submitted = FALSE;
	public $alias = '';
	public $title = '';
	public $url = '';
	public $hasErrors;
	public $isValid;
	public $messages =  array();
	
	public function __construct($form) {
		
		if ( $form ) {
			$this->submitted = TRUE;
			$this->alias = $form['alias'];
			$this->title = $form['title'];
			$this->url = $form['url'];
			$this->hasErrors = $this->validate();
			$this->isValid = !$this->hasErrors;
		}
	}
	
	private function validate() {
		
		$urlRegEx = '/^http.+/';
		
		$validAlias = preg_match('/^(\w|\.|-)+$/',$this->alias);
		$validTitle = preg_match('/(\b(\w|\.|-)+\b)+/',$this->title);
		$validUrl = preg_match($urlRegEx,$this->url);
		
		if ( !$validAlias ) $this->addError('Bad alias');
		if ( !$validTitle ) $this->addError('Bad title');
		if ( !$validUrl ) $this->addError('Bad url');
				
		//return (count($this->messages)==0 ? TRUE : FALSE);
		return $this->hasErrors;
	}
	
	public function messages() {
		$html = array();
		foreach ( $this->messages as $message ) $html[] = '<p>'.$message.'</p>';
		return implode("\n",$html);
	}
	
	public function addError($msg) {
		$this->hasErrors = TRUE;
		$this->isValid = FALSE;
		$this->messages[] = $msg;
	}
	
	
}

