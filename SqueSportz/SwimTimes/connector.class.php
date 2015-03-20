<?php

namespace SqueSportz\SwimTimes;

class Connector {
    /**
     * Contains the connection method
     *
     * @var string
     */
	private $_method;
	
    /**
     * Contains the target host
     *
     * @var string
     */
	private $_base = "www.swimtimes.nl";
	
    /**
     * Contains the target path
     *
     * @var string
     */
	private $_path;
	
    /**
     * Contains the Username and Password (for authentication)
     *
     * @var array
     */
	private $_auth = null;
	
    /**
     * Contains the User agent string
     *
     * @var string
     */
	private $_ua = "SwimTimes API (+http://www.swimtimes.nl/apis)";
	
	function __construct() {
		if (function_exists("curl_init")) {
			$this->_method = 'curl';
		} else {
			$this->_method = 'fsock';
		}
	}
	
	public function setAuth($user, $key) {
		$this->_auth = array($user, $key);
	}
	
	public function setPath($path) {
		$this->_path = "/json/".$path;
	}
	
	public function getData() {
		if (empty($this->_path)) {
			throw new \Exception('Path is empty');
		}
		
		if ($this->_method == 'curl') {
			$data = $this->__req_curl();
		} elseif ($this->_method == 'fsock') {
			$data = $this->__req_fsock();
		}
		
		// All data is JSON encoded
		$data = json_decode($data);
		
		// Check for errors
		if (empty($data)) {
			throw new \Exception('Can\'t find Path');
		} elseif (isset($data->code) && isset($data->message)) {
			throw new \Exception($data->message);
		}
		
		return $data;
	}
	
	private function __req_curl() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://".$this->_base.$this->_path);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->_ua);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($ch, CURLOPT_CAINFO, __DIR__.'/cacerts.pem');
		
		// This API script is limited to read-only
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_POST, false);
		
		// Add authentication
		if ($this->_auth != null) {
			curl_setopt($ch, CURLOPT_USERPWD, implode(':', $this->_auth));
		}
		
		// Execute the request
		$response = curl_exec($ch);
		if ($err = curl_error($ch)) {
			return $err;
		}
		
		return $response;
	}
	
	private function __req_fsock() {
		$filePointer = fsockopen("ssl://".$this->_base, "443", $errorNumber, $errorString, 15);
		$responseHeader = '';
		$responseContent = '';

		if (!$filePointer) {
			return 'Failed opening http socket connection: ' . $errorString . ' (' . $errorNumber . ')';
		}
		$requestHeader  = "GET " . $this->_path . "  HTTP/1.1\r\n";
		$requestHeader .= "Host: " . $this->_base . "\r\n";
		$requestHeader .= "User-Agent: " . $this->_ua . "\r\n";
		
		// Add authentication
		if ($this->_auth != null) {
			$requestHeader .= "Authorization: Basic " . base64_encode(implode(':', $this->_auth)) . "\r\n";
		}
		$requestHeader .= "Connection: close\r\n\r\n";
		
		// Execute the request
		fwrite($filePointer, $requestHeader);
		do {
			$responseHeader .= fread($filePointer, 1);
		} while (!preg_match('/\\r\\n\\r\\n$/', $responseHeader));
		
		if (!preg_match('/transfer-encoding: chunked/i', $responseHeader)) {
			while (!feof($filePointer)) {
				$responseContent .= fgets($filePointer, 128);
			}
		} else {
			while ($chunkLength = hexdec(fgets($filePointer))) {
				$responseContentChunk = '';
				$readLength = 0;
			   
				while ($readLength < $chunkLength) {
					$responseContentChunk .= fread($filePointer, $chunkLength - $readLength);
					$readLength = strlen($responseContentChunk);
				}

				$responseContent .= $responseContentChunk;
				fgets($filePointer);  
			}
		}
		
		return chop($responseContent);
	}
}