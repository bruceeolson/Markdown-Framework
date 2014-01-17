<?php

require_once('mdsCurl.php'); 

class mdsAdmin {
	
	public $mdsBaseUrl;
	public $action;
	public $form;
	public $libraryUrl;
	public $success = FALSE;
	
	private static $_app;
	private $_inputs;
	private $_lib;
		
	private function __construct() {
		
		$this->_lib = new mdsLibraries;
		
		$this->_inputs = isset($_POST['Register']) ? $_POST['Register'] : FALSE;
		
		$tokens = array();
		$uritokens = explode('/',$_SERVER['REQUEST_URI']);
		
		// remove empty tokens
		foreach ( $uritokens as $token ) if ( strlen($token) ) $tokens[] = $token;
		
		$this->mdsBaseUrl = '/'.array_shift($tokens);  // mds
				
		$alias = count($tokens) == 3 ? array_pop($tokens) : FALSE;
		$action = $this->action = array_pop($tokens);	// create, update, delete
		
		if ( in_array($action, array('admin','update','delete')) && !$alias) {
			$this->action = FALSE;
			return;
		}
		
		if 		( $action == 'create' ) $this->actionCreate();
		elseif ( $action == 'update' ) $this->actionUpdate($alias);
		elseif ( $action == 'delete' ) $this->actionDelete($alias);
	}
	
	public static function app() {
		if ( !self::$_app ) self::$_app = new mdsAdmin;
		return self::$_app;
	}
	
	private function actionCreate() {
		
		$this->form = new mdsRegistrationForm($this->_inputs, 'create');
				
		if ( $this->form->submitted && $this->form->isValid && $this->libraryExists() ) {
			$this->success = TRUE;			
			$this->libraryUrl = $this->mdsBaseUrl.'/'.$this->form->alias;
		}
	}
	
	private function actionUpdate($alias) {
		
		if ( is_array($this->_inputs) ) {
			$this->form = new mdsRegistrationForm($this->_inputs, 'update');
			if ( $this->form->isValid ) {
				$this->_lib->update($this->form);
				$this->success = TRUE;
				$this->libraryUrl = $this->mdsBaseUrl;
			}
		}
		else {
			$node = $this->_lib->getNodeByAlias($alias);
			$this->form = new mdsRegistrationForm($node, 'update');
		}		
	}
	
	private function actionDelete($alias) {
		$this->_lib->delete($alias);
		$this->success = TRUE;			
		$this->libraryUrl = $this->mdsBaseUrl;
	}
	
	
	public function libraryExists() {
				
		$folder = new docAsset($this->form->url);
		
		if ( $folder->found ) {
														
			$xpath = new DOMXpath($this->_lib->dom);
			$libExists = $xpath->query('//library/url[text()="'.$this->form->url.'"]');
			$aliasExists = $xpath->query('//library/alias[text()="'.$this->form->alias.'"]');
									
			if ( $libExists->length ) $this->form->addError('This library url already exists.');
			elseif ( $aliasExists->length ) $this->form->addError('This library alias already exists.');
			else $this->_lib->create($this->form);	
		}
		else $this->form->addError('Can\'t find folder using this url.');
						
		return $this->form->isValid;
	}


}  // mdsRegisterApp class

class mdsLibraries {
	
	public $dom;
	
	private $_xpath;
	private $_root;
	private $_configFilePath;
	
	public function __construct() {
		
		$fileConfigXML = $this->_configFilePath = dirname(__FILE__).'/config.xml';
		
		// add library to config.xml if it doesn't already exist
		$config = $this->dom = new DOMDocument('1.0', 'UTF-8');
		$config->preserveWhiteSpace = FALSE;
		$config->load($fileConfigXML);
		$this->_xpath = new DOMXpath($config);
		$this->_root = $this->_xpath->query('//libraries')->item(0);
	}
	
	public function getNodeByAlias($alias) {
		$node = $this->_xpath->query('//alias[text()="'.$alias.'"]');
		if ( $node->length ) return $node->item(0)->parentNode;
		else return FALSE;
	}
	
	public function getNodeById($id) {
		$node = $this->_xpath->query('//library[@id="'.$id.'"]');
		if ( $node->length ) return $node->item(0);
		else return FALSE;
	}
	
	public function create($form) {
		
		// add library to config.xml
		$root = $this->_root;	
		
		$library = $this->dom->createElement("library");
		$library->setAttribute('id',time());
		$root->appendChild($library);
						
		$alias = $this->dom->createElement("alias");
		$alias->nodeValue = $form->alias;
		$library->appendChild($alias);
		
		$title = $this->dom->createElement("title");
		$title->nodeValue = $form->title;
		$library->appendChild($title);
		
		$owner = $this->dom->createElement("owner");
		$owner->nodeValue = $form->owner;
		$library->appendChild($owner);
		
		$url = $this->dom->createElement("url");
		$url->nodeValue = $form->url;
		$library->appendChild($url);
		
		file_put_contents($this->_configFilePath,$this->dom->saveXML());
	}
	
	public function update($form) {
		$node = $this->getNodeById($form->id);
		if ( $node ) $this->_root->removeChild($node);
		$this->create($form);
	}
	
	public function delete($alias) {
		$node = $this->getNodeByAlias($alias);
		
		if ( $node ) {
			$this->_root->removeChild($node);
			file_put_contents($this->_configFilePath,$this->dom->saveXML());
		}
	}
	
	
}

class mdsRegistrationForm {
	
	public $formTitle;
	public $submitted = FALSE;
	public $action = '';
	public $id = '';
	public $alias = '';
	public $title = '';
	public $owner = '';
	public $url = '';
	public $hasErrors;
	public $isValid;
	public $messages =  array();
	
	public function __construct($form, $action=NULL) {
		
		$this->action = $action;
		$this->formTitle = $action.' MDS library';
		
		if ( is_array($form) ) {  // user inputs
			$this->submitted = TRUE;
			$this->id = $form['id'];
			$this->alias = $form['alias'];
			$this->title = $form['title'];
			$this->owner = $form['owner'];
			$this->url = $form['url'];
			$this->hasErrors = $this->validate();
			$this->isValid = !$this->hasErrors;
		}
		elseif ( is_object($form) ) {  // xml element
			$this->id = $form->getAttribute('id');
			
			foreach ( $form->childNodes as $child ) {
				if ( $child->nodeName == 'alias' ) $this->alias = $child->nodeValue;
				if ( $child->nodeName == 'title' ) $this->title = $child->nodeValue;
				if ( $child->nodeName == 'owner' ) $this->owner = $child->nodeValue;
				if ( $child->nodeName == 'url' ) $this->url = $child->nodeValue;
			}
			$this->hasErrors = $this->validate();
			$this->isValid = !$this->hasErrors;
		}
	}
	
	private function validate() {
		
		$validId = preg_match('/(^$|^\d{10}$)/',$this->id);  // a 10-digit timestamp
		$validAlias = preg_match('/^(\w|\.|-)+$/',$this->alias);  // single word
		$validTitle = preg_match('/(\b(\w|\.|-)+\b)+/',$this->title);  // one or more words
		$validOwner = preg_match('/(\b(\w|\.|-)+\b)+/',$this->owner);  // one or more words
		$validUrl = (bool)parse_url($this->url);
		
		if ( !$validId ) $this->addError('Invalid id');
		if ( !$validAlias ) $this->addError('Invalid alias');
		if ( !$validTitle ) $this->addError('Invalid title');
		if ( !$validOwner ) $this->addError('Invalid owner');
		if ( !$validUrl ) $this->addError('Invalid url');
		if ( strtolower($this->alias) == 'admin' ) $this->addError('Admin is a reserved word so it cannot be used as an alias.');
				
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
?>