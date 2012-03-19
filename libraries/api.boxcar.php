<?php
class BoxcarAPI {
	private $APIKey;
	private $APISecret;
	private $Icon;
	
	public function __construct() {
		$this->APIKey    = 'wqEYImHDT5ZLsQgb5tZ0';
		$this->APISecret = 'iW2Ynkk31Z6MiVtuGVWBLaHvQ9EJWqRlvcDkhM1G';
		$this->Icon      = 'http://s3.amazonaws.com/boxcar-production1/providers/icons/994/hub-boxcar_normal_48.png';
	}
	
	public function Notify($EMail, $Name, $Message, $ID = '') {
		$Notification = array('token'                                => $this->APIKey,
							  'secret'                               => $this->APISecret,
							  'email'                                => md5($EMail),
							  'notification[from_screen_name]'       => $Name,
							  'notification[message]'                => $Message,
							  'notification[from_remote_service_id]' => ($ID) ? $ID : rand(),
							  'notification[icon_url]'               => $this->Icon);
		
		return $this->ResponseHandler($this->Send('notifications', $Notification));
	}
	
	private function Send($Action, $Data) {
		$URI = 'http://boxcar.io/devices/providers/'.$this->APIKey.'/'.$Action;
		
		$DataFields = array();
		foreach($Data AS $Key => $Value) {
			array_push($DataFields, $Key.'='.$Value);
		}
		$DataFields = implode('&', $DataFields);
		
		$CurlHandler = curl_init();
		curl_setopt($CurlHandler, CURLOPT_URL,            $URI);
		curl_setopt($CurlHandler, CURLOPT_USERAGENT,      'HubAppNotifier');
		curl_setopt($CurlHandler, CURLOPT_POST,           TRUE);
		curl_setopt($CurlHandler, CURLOPT_POSTFIELDS,     $DataFields);
		curl_setopt($CurlHandler, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($CurlHandler, CURLOPT_FOLLOWLOCATION, FALSE);
		curl_setopt($CurlHandler, CURLOPT_TIMEOUT,        5);

		$Result               = curl_exec($CurlHandler);
		$ResultInfo           = curl_getinfo($CurlHandler);
		$ResultInfo['result'] = $Result;
		
		curl_close($CurlHandler);
		
		return $ResultInfo;
	}
	
	private function ResponseHandler($Result) {
		switch($Result['http_code']) {
			case 200:
				return TRUE;
			break;
			
			case 400:
			case 401:
			case 403:
			case 404:
			default:
				return FALSE;
			break;
		}
	}
}
?>