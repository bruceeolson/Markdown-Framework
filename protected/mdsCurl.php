<?php

// this class provides an abstraction for a cUrl call
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