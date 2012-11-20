<?php
define('UTORRENT_TORRENT_HASH',             0);
define('UTORRENT_TORRENT_STATUS',           1);
define('UTORRENT_TORRENT_NAME',             2);
define('UTORRENT_TORRENT_SIZE',             3);
define('UTORRENT_TORRENT_PROGRESS',         4);
define('UTORRENT_TORRENT_DOWNLOADED',       5);
define('UTORRENT_TORRENT_UPLOADED',         6);
define('UTORRENT_TORRENT_RATIO',            7);
define('UTORRENT_TORRENT_UPSPEED',          8);
define('UTORRENT_TORRENT_DOWNSPEED',        9);
define('UTORRENT_TORRENT_ETA',             10);
define('UTORRENT_TORRENT_LABEL',           11);
define('UTORRENT_TORRENT_PEERS_CONNECTED', 12);
define('UTORRENT_TORRENT_PEERS_SWARM',     13);
define('UTORRENT_TORRENT_SEEDS_CONNECTED', 14);
define('UTORRENT_TORRENT_SEEDS_SWARM',     15);
define('UTORRENT_TORRENT_AVAILABILITY',    16);
define('UTORRENT_TORRENT_QUEUE_POSITION',  17);
define('UTORRENT_TORRENT_REMAINING',       18);
define('UTORRENT_FILEPRIORITY_HIGH',        3);
define('UTORRENT_FILEPRIORITY_NORMAL',      2);
define('UTORRENT_FILEPRIORITY_LOW',         1);
define('UTORRENT_FILEPRIORITY_SKIP',        0);
define('UTORRENT_TYPE_INTEGER',             0);
define('UTORRENT_TYPE_BOOLEAN',             1);
define('UTORRENT_TYPE_STRING',              2);
define('UTORRENT_STATUS_STARTED',           1);
define('UTORRENT_STATUS_CHECKED',           2);
define('UTORRENT_STATUS_START_AFTER_CHECK', 4);

class UTorrentAPI {
    // class static variables
    private static $BaseURI = 'http://%s:%s/gui/%s';

    // member variables
	public $Host;
	public $User;
	public $Pass;
	public $Port;
	
    // constructor
    function __construct($Host, $User, $Pass, $Port) {
    	$this->Host = $Host;
    	$this->User = $User;
    	$this->Pass = $Pass;
    	$this->Port = $Port;
    	
        if(!$this->getToken($this->Token)) {
            return FALSE;
        }
    }
    
    // gets token, returns true on success, token is stored in $token by-ref argument
    public function getToken(&$Token) {
        $Output = $this->makeRequest('token.html', FALSE);
        
        if(preg_match('/<div id=\'token\'.+>(.*)<\/div>/', $Output, $Message)) {
            $Token = $Message[1];
            
            return TRUE;
        }
        else {
        	return FALSE;
        }
    }

    // performs request
    private function makeRequest($Request, $Decode = true, $OptionsArr = array()) {
        $CurlResource = curl_init();

        curl_setopt_array($CurlResource, $OptionsArr);
        if(!empty($this->Token)) {
            // Check if we have a ?
            if(substr($Request, 0, 1) == '?') {
                $Request = preg_replace('/^\?/', '?token='.$this->Token . '&', $Request);
            }
        }
        
        curl_setopt($CurlResource, CURLOPT_URL, sprintf(self::$BaseURI, $this->Host, $this->Port, $Request));
        curl_setopt($CurlResource, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($CurlResource, CURLOPT_TIMEOUT, 1);
        curl_setopt($CurlResource, CURLOPT_USERPWD, $this->User.':'.$this->Pass);

        $Request = curl_exec($CurlResource);
        curl_close($CurlResource);

        return ($Decode ? json_decode($Request, TRUE) : $Request);
    }

    // implodes given parameter with glue, whether it is an array or not
    private function paramImplode($Glue, $Parameters) {
        return $Glue.implode($Glue, is_array($Parameters) ? $Parameters : array($Parameters));
    }

    // returns the uTorrent build number
    public function getBuild(){
        $JSON = $this->makeRequest('?');
        
        return $JSON['build'];
    }

    // returns an array of files for the specified torrent hash
    // TODO:
    //  - (when implemented in API) allow multiple hashes to be specified
    public function getFiles($Hash) {
        $JSON = $this->makeRequest('?action=getfiles&hash='.$Hash);
        
        return $JSON['files'];
    }

    // returns an array of all labels
    public function getLabels(){
        $JSON = $this->makeRequest('?list=1');
        
        return $JSON['label'];
    }

    // returns an array of the properties for the specified torrent hash
    // TODO:
    //  - (when implemented in API) allow multiple hashes to be specified
    public function getProperties($Hash) {
        $JSON = $this->makeRequest('?action=getprops&hash='.$Hash);
        
        return $JSON['props'];
    }

    // returns an array of all settings
    public function getSettings() {
        $JSON = $this->makeRequest('?action=getsettings');
        
        return $JSON['settings'];
    }

    // returns an array of all torrent jobs and related information
    public function getTorrents() {
        $JSON = $this->makeRequest('?list=1');
        
        return $JSON['torrents'];
    }

    // returns true if WebUI server is online and enabled, false otherwise
    public function is_online() {
        return is_array($this->makeRequest('?'));
    }

    // sets the properties for the specified torrent hash
    // TODO:
    //  - allow multiple hashes, properties, and values to be set simultaneously
    public function setProperties($Hash, $Property, $Value) {
        $this->makeRequest('?action=setprops&hash='.$Hash.'&s='.$Property.'&v='.$Value, FALSE);
    }

    // sets the priorities for the specified files in the specified torrent hash
    public function setPriority($Hash, $Files, $Priority) {
        $this->makeRequest('?action=setprio&hash='.$Hash.'&p='.$Priority.$this->paramImplode('&f=', $Files), FALSE);
    }

    // sets the settings
    // TODO:
    //  - allow multiple settings and values to be set simultaneously
    public function setSetting($Setting, $Value) {
        $this->makeRequest('?action=setsetting&s='.$Setting.'&v='.$Value, FALSE);
    }

    // add a file to the list
    public function torrentAdd($Filename, &$ErrorStr = FALSE) {
        $Split = explode(':', $Filename, 2);
        
        if(count($Split) > 1 && (stristr('|magnet|http|https|file|', '|'.$Split[0].'|') !== FALSE)) {
            $this->makeRequest('?action=add-url&s='.urlencode($Filename), FALSE);
            
            return TRUE;
        }
        else if(file_exists($Filename)) {
            $JSON = $this->makeRequest('?action=add-file', TRUE, array(CURLOPT_POSTFIELDS => array('torrent_file' => '@'.realpath($Filename))));

            if(array_key_exists('error', $JSON)) {
                if($ErrorStr !== FALSE) {
                	$ErrorStr = $JSON['error'];
                }
                
                return FALSE;
            }
            
            return TRUE;
        }
        else {
            if($ErrorStr !== FALSE) {
            	$ErrorStr = 'File does not exist!';
            }
            
            return FALSE;
        }
    }

    // force start the specified torrent hashes
    public function torrentForceStart($Hash) {
        $this->makeRequest('?action=forcestart'.$this->paramImplode('&hash=', $Hash), FALSE);
    }

    // pause the specified torrent hashes
    public function torrentPause($Hash) {
        $this->makeRequest('?action=pause'.$this->paramImplode('&hash=', $Hash), FALSE);
    }

    // recheck the specified torrent hashes
    public function torrentRecheck($Hash) {
        $this->makeRequest('?action=recheck'.$this->paramImplode('&hash=', $Hash), FALSE);
    }

    // start the specified torrent hashes
    public function torrentStart($Hash) {
        $this->makeRequest('?action=start'.$this->paramImplode('&hash=', $Hash), FALSE);
    }

    // stop the specified torrent hashes
    public function torrentStop($Hash) {
        $this->makeRequest('?action=stop'.$this->paramImplode('&hash=', $Hash), FALSE);
    }

    // remove the specified torrent hashes (and data, if $data is set to true)
    public function torrentRemove($Hash, $Data = FALSE) {
        $this->makeRequest('?action='.($Data ? 'removedata' : 'remove').$this->paramImplode('&hash=', $Hash), FALSE);
    }
}
?>