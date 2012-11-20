<?php
class Hub {
	const BASE_URI = API_URL;
	const API_KEY  = API_KEY;
	
	public function Request($Request, $Type = 'GET', $Data = array()) {
		$AcceptedTypes = array('GET', 'PUT', 'POST', 'DELETE');
		
		if(!in_array($Type, $AcceptedTypes)) {
			return FALSE;
		}
		
		$cURL = curl_init();
		
		if(strpos($Request, '/', 0)) {
			$Request = '/'.$Request;
		}
		
		if(strlen(self::API_KEY)) {
			$Request = $Request.'?key='.self::API_KEY;
		}
		
		$Request = self::BASE_URI.$Request;
		curl_setopt_array($cURL, array(CURLOPT_RETURNTRANSFER  => TRUE,
		                               CURLOPT_URL             => $Request,
		                               CURLOPT_CONNECTTIMEOUT  => 5,
		                               CURLOPT_TIMEOUT         => 120,
		                               CURLOPT_FAILONERROR     => FALSE));
		if($Type == 'POST') {
			curl_setopt($cURL, CURLOPT_POST, 1);
			//curl_setopt($cURL, CURLOPT_TIMEOUT, 480); // For file extraction etc
			curl_setopt($cURL, CURLOPT_POSTFIELDS, $Data);
		}
		else if($Type == 'PUT') {
			curl_setopt($cURL, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($cURL, CURLOPT_POSTFIELDS, http_build_query($Data));
		}
		else if($Type == 'DELETE') {
			curl_setopt($cURL, CURLOPT_CUSTOMREQUEST, 'DELETE');
		}
		
		$cURLResponse = curl_exec($cURL);
		curl_close($cURL);
		
		return $cURLResponse;
	}
}

$Hub = new Hub;
?>