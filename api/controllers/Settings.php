<?php
/**
 * //@protected
**/
class Settings {
	private $PDO;
	
	function __construct() {
		$this->PDO = DB::Get();
	}
	
	/**
	 * @url GET /
	**/
	function GetSettings() {try {
		$SettingsPrep = $this->PDO->prepare('SELECT
	                                     	 	*
	                                     	 FROM
	                                     	 	Hub
	                                     	 WHERE
	                                     	 	Setting != "IsInstalled"');
	                                     	
		$SettingsPrep->execute();
	
		if($SettingsPrep->rowCount()) {
			return $SettingsPrep->fetchAll();
		}
		else {
			throw new RestException(404, 'Did not find any settings in the database');
		}
	}
	catch(PDOException $e) {
		throw new RestException(400, 'MySQL: '.$e->getMessage());
	}
	}
	
	/**
	 * @url POST /update
	**/
	function UpdateSettings() {
		$AcceptedParameters = array('LastUpdateTime',
		                            'IsLocked',
		                            'LastSerieRefresh',
		                            'LastSerieRebuild',
		                            'LastFolderRebuild',
		                            'CurrentDBVersion',
		                            'ShareMovies',
		                            'UTorrentIP',
		                            'XBMCDataFolder',
		                            'SearchURIMovies',
		                            'SearchURITVSeries',
		                            'KillSwitch',
		                            'TheTVDBAPIKey',
		                            'MaximumDownloadQuality',
		                            'MinimumDownloadQuality',
		                            'MinimumDiskSpaceRequired',
		                            'BackupXBMCDatabase',
		                            'BackupXBMCFiles',
		                            'BackupHubDatabase',
		                            'BackupHubFiles',
		                            'BackupFolder',
		                            'LocalIP',
		                            'LocalHostname',
		                            'UTorrentPort',
		                            'UTorrentUsername',
		                            'UTorrentPassword',
		                            'UTorrentWatchFolder',
		                            'UTorrentDefaultUpSpeed',
		                            'UTorrentDefaultDownSpeed',
		                            'UTorrentDefinedUpSpeed',
		                            'UTorrentDefinedDownSpeed',
		                            'BackupAge',
		                            'ShareWishlist',
		                            'LastMoviesUpdate',
		                            'LastWishlistUpdate',
		                            'LastWishlistRefresh',
		                            'LastBackup');
		
		if(!sizeof($_POST)) {
			throw new RestException(412, 'Invalid request. Accepted parameters are "'.implode(', ', $AcceptedParameters).'"');
		}
		
		
		$i = 0;
		foreach($_POST AS $Key => $Value) {
			if(!in_array($Key, $AcceptedParameters)) {
				throw new RestException(412, 'Invalid request. Accepted parameters are "'.implode(', ', $AcceptedParameters).'"');
			}
			
			$UpdateQuery = 'UPDATE Hub SET '.$Key.' = :'.$Key.' WHERE Setting = "'.$Key.'"';
			$PrepArr = array(':'.$Key => $Value);
			
			try {
				$SettingsPrep = $this->PDO->prepare($UpdateQuery);
				$SettingsPrep->execute($PrepArr);
			}
			catch(PDOException $e) {
				throw new RestException(400, 'MySQL: '.$e->getMessage());
			}
		}
		
		$LogEntry = 'Updated settings';
		
		AddLog(EVENT.'Settings', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
}
?>