<?php
/**
 * //@protected
**/
class Hub {
	const HubVersion   = '3.0';
	const MinDBVersion = '3.0.0';
	
	private $PDO;
	
	function __construct() {
		$this->PDO = DB::Get();
	}
	
	/**
	 * @url GET /version
	**/
	function GetHubVersion() {
		return self::HubVersion;
	}
	
	/**
	 * @url GET /upgrade
	**/
	function DatabaseUpgrade() {
		$DBVersion = GetSetting('CurrentDBVersion');
		
		if(str_replace('.', '', $DBVersion) < str_replace('.', '', self::MinDBVersion)) {
			foreach(glob(APP_PATH.'/upgrade/db-*.php') AS $File) {
				$NewDBVersion = str_replace('.php', '', str_replace(APP_PATH.'/upgrade/db-', '', $File));
				
				if(str_replace('.', '', $NewDBVersion) > str_replace('.', '', $DBVersion)) {
					$sql = '';
					include_once $File;
					
					$IsUpgraded = FALSE;
					if(is_array($sql)) {
						foreach($sql AS $SQLUpgrade) {
							try {
								$UpgradePrep = $this->PDO->prepare($SQLUpgrade);
								$UpgradePrep->execute();
							}
							catch(PDOException $e) {
								throw new RestException(412, 'MySQL: '.$e->getMessage());
							}
							
							$IsUpgraded = TRUE;
						}
					}
					else if(is_string($sql)) {
						try {
							$UpgradePrep = $this->PDO->prepare($sql);
							$UpgradePrep->execute();
						}
						catch(PDOException $e) {
							throw new RestException(412, 'MySQL: '.$e->getMessage());
						}
						
						$IsUpgraded = TRUE;
					}
					
					if($IsUpgraded) {
						try {
							$DBUpgradePrep = $this->PDO->prepare('UPDATE Hub SET Value = :NewVersion WHERE Setting = "CurrentDBVersion"');
							$DBUpgradePrep->execute(array(':NewVersion' => $NewDBVersion));
						}
						catch(PDOException $e) {
							throw new RestException(412, 'MySQL: '.$e->getMessage());
						}
						
						AddLog(EVENT.'Database', 'Success', 'Upgraded database to "'.$NewDBVersion.'"');
					}
				}
				else {
					@unlink($File);
				}
			}
		}
	}
	
	/**
	 * @url GET /lockstatus
	**/
	function GetLockStatus() {
		try {
			$LockPrep = $this->PDO->prepare('SELECT
			                                 	*
			                                 FROM
			                                 	Hub
			                                 WHERE
			                                 	Setting = "IsLocked"');
			                                 	
			$LockPrep->execute();
			$LockRow = $LockPrep->fetch();
			
			if(sizeof($LockRow)) {
				if($LockRow['Value'] > strtotime('-4 hours')) {
					throw new RestException(409, 'Lock is in effect');
				}
				else if($LockRow['Value'] != 0 && $LockRow['Value'] < strtotime('-4 hours')) {
					try {
						$this->Unlock();
					}
					catch(RestException $e) {
						switch($e->getCode()) {
							case 200:
								$LogEntry = 'Lock was removed due to 4 hour timeout';
								AddLog(EVENT.'Hub', 'Success', $LogEntry);
								
								throw new RestException(200, $LogEntry);
							break;
							
							default:
								AddLog(EVENT.'Hub', 'Failure', $e->getMessage());
								
								throw new RestException(400, $e->getMessage());
						}
					}
				}
				else {
					throw new RestException(200, 'No lock is in effect');
				}
			}
			else {
				throw new RestException(404, 'Could not find a lock setting in the database');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url GET /unlock
	**/
	function Unlock() {
		try {
			$LockPrep = $this->PDO->prepare('UPDATE
			                             	 	Hub
			                             	 SET
			                             	 	Value = 0
			                             	 WHERE
			                             		Setting = "IsLocked"');
			                             	
			$LockPrep->execute();
			
			throw new RestException(200, 'Lock successfully removed');
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url GET /lock
	**/
	function Lock() {
		try {
			$LockPrep = $this->PDO->prepare('UPDATE
			                                 	Hub
			                                 SET
			                                 	Value = :Time
			                                 WHERE
			                                 	Setting = "IsLocked"');
			                                 	
			$LockPrep->execute(array(':Time' => time()));
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		throw new RestException(200, 'Lock is now in effect');
	}
	
	/**
	 * @url GET /backup/clean
	**/
	function CleanBackupDirectory() {
		throw new RestException(200);
	}
}
?>