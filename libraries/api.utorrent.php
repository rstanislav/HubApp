<?php
define("UTORRENT_TORRENT_HASH", 0);
define("UTORRENT_TORRENT_STATUS", 1);
define("UTORRENT_TORRENT_NAME", 2);
define("UTORRENT_TORRENT_SIZE", 3);
define("UTORRENT_TORRENT_PROGRESS", 4);
define("UTORRENT_TORRENT_DOWNLOADED", 5);
define("UTORRENT_TORRENT_UPLOADED", 6);
define("UTORRENT_TORRENT_RATIO", 7);
define("UTORRENT_TORRENT_UPSPEED", 8);
define("UTORRENT_TORRENT_DOWNSPEED", 9);
define("UTORRENT_TORRENT_ETA", 10);
define("UTORRENT_TORRENT_LABEL", 11);
define("UTORRENT_TORRENT_PEERS_CONNECTED", 12);
define("UTORRENT_TORRENT_PEERS_SWARM", 13);
define("UTORRENT_TORRENT_SEEDS_CONNECTED", 14);
define("UTORRENT_TORRENT_SEEDS_SWARM", 15);
define("UTORRENT_TORRENT_AVAILABILITY", 16);
define("UTORRENT_TORRENT_QUEUE_POSITION", 17);
define("UTORRENT_TORRENT_REMAINING", 18);
define("UTORRENT_FILEPRIORITY_HIGH", 3);
define("UTORRENT_FILEPRIORITY_NORMAL", 2);
define("UTORRENT_FILEPRIORITY_LOW", 1);
define("UTORRENT_FILEPRIORITY_SKIP", 0);
define("UTORRENT_TYPE_INTEGER", 0);
define("UTORRENT_TYPE_BOOLEAN", 1);
define("UTORRENT_TYPE_STRING", 2);
define("UTORRENT_STATUS_STARTED", 1);
define("UTORRENT_STATUS_CHECKED", 2);
define("UTORRENT_STATUS_START_AFTER_CHECK", 4);

class UTorrentAPI {
    // class static variables
    private static $base = "http://%s:%s/gui/%s";

    // member variables
	public $host;
	public $user;
	public $pass;
	public $port;
	
    // constructor
    function __construct($host, $user, $pass, $port) {
    	$this->host = $host;
    	$this->user = $user;
    	$this->pass = $pass;
    	$this->port = $port;
    	
        if (!$this->getToken($this->token)) {
            //handle error here, don't know how to best do this yet
            return FALSE;
        }
    }
    
    // gets token, returns true on success, token is stored in $token by-ref argument
    public function getToken(&$token) {
        $output = $this->makeRequest('token.html', false);
        
        if(preg_match('/<div id=\'token\'.+>(.*)<\/div>/', $output, $m)) {
            $token = $m[1];
            return true;
        }
        return false;
    }

    // performs request
    private function makeRequest($request, $decode = true, $options = array()) {
        $ch = curl_init();

        curl_setopt_array($ch, $options);
        if (!empty($this->token)) {
            // Check if we have a ?
            if (substr($request, 0, 1) == '?')
                $request = preg_replace('/^\?/', '?token='.$this->token . '&', $request);
        }
        //print "<code>$request</code>"; //DEBUG
        curl_setopt($ch, CURLOPT_URL, sprintf(self::$base, $this->host, $this->port, $request));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $this->user.":".$this->pass);

        $req = curl_exec($ch);
        curl_close($ch);

        return ($decode ? json_decode($req, true) : $req);
    }

    // implodes given parameter with glue, whether it is an array or not
    private function paramImplode($glue, $param) {
        return $glue.implode($glue, is_array($param) ? $param : array($param));
    }

    // returns the uTorrent build number
    public function getBuild(){
        $json = $this->makeRequest("?");
        return $json['build'];
    }

    // returns an array of files for the specified torrent hash
    // TODO:
    //  - (when implemented in API) allow multiple hashes to be specified
    public function getFiles($hash) {
        $json = $this->makeRequest("?action=getfiles&hash=".$hash);
        return $json['files'];
    }

    // returns an array of all labels
    public function getLabels(){
        $json = $this->makeRequest("?list=1");
        return $json['label'];
    }

    // returns an array of the properties for the specified torrent hash
    // TODO:
    //  - (when implemented in API) allow multiple hashes to be specified
    public function getProperties($hash) {
        $json = $this->makeRequest("?action=getprops&hash=".$hash);
        return $json['props'];
    }

    // returns an array of all settings
    public function getSettings() {
        $json = $this->makeRequest("?action=getsettings");
        return $json['settings'];
    }

    // returns an array of all torrent jobs and related information
    public function getTorrents() {
        $json = $this->makeRequest("?list=1");
        return $json['torrents'];
    }

    // returns true if WebUI server is online and enabled, false otherwise
    public function is_online() {
        return is_array($this->makeRequest("?"));
    }

    // sets the properties for the specified torrent hash
    // TODO:
    //  - allow multiple hashes, properties, and values to be set simultaneously
    public function setProperties($hash, $property, $value) {
        $this->makeRequest("?action=setprops&hash=".$hash."&s=".$property."&v=".$value, false);
    }

    // sets the priorities for the specified files in the specified torrent hash
    public function setPriority($hash, $files, $priority) {
        $this->makeRequest("?action=setprio&hash=".$hash."&p=".$priority.$this->paramImplode("&f=", $files), false);
    }

    // sets the settings
    // TODO:
    //  - allow multiple settings and values to be set simultaneously
    public function setSetting($setting, $value) {
        $this->makeRequest("?action=setsetting&s=".$setting."&v=".$value, false);
    }

    // add a file to the list
    public function torrentAdd($filename, &$estring = false) {
        $split = explode(":", $filename, 2);
        if (count($split) > 1 && (stristr("|http|https|file|", "|".$split[0]."|") !== false)) {
            $this->makeRequest("?action=add-url&s=".urlencode($filename), false);
        }
        elseif (file_exists($filename)) {
            $json = $this->makeRequest("?action=add-file", true, array(CURLOPT_POSTFIELDS => array("torrent_file" => "@".realpath($filename))));

            if (isset($json['error'])) {
                if ($estring !== false) $estring = $json['error'];
                return false;
            }
            return true;
        }
        else {
            if ($estring !== false) $estring = "File doesn't exist!";
            return false;
        }
    }

    // force start the specified torrent hashes
    public function torrentForceStart($hash) {
        $this->makeRequest("?action=forcestart".$this->paramImplode("&hash=", $hash), false);
    }

    // pause the specified torrent hashes
    public function torrentPause($hash) {
        $this->makeRequest("?action=pause".$this->paramImplode("&hash=", $hash), false);
    }

    // recheck the specified torrent hashes
    public function torrentRecheck($hash) {
        $this->makeRequest("?action=recheck".$this->paramImplode("&hash=", $hash), false);
    }

    // start the specified torrent hashes
    public function torrentStart($hash) {
        $this->makeRequest("?action=start".$this->paramImplode("&hash=", $hash), false);
    }

    // stop the specified torrent hashes
    public function torrentStop($hash) {
        $this->makeRequest("?action=stop".$this->paramImplode("&hash=", $hash), false);
    }

    // remove the specified torrent hashes (and data, if $data is set to true)
    public function torrentRemove($hash, $data = false) {
        $this->makeRequest("?action=".($data ? "removedata" : "remove").$this->paramImplode("&hash=", $hash), false);
    }
}
?>