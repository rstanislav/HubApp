<?php
class Zones extends Hub {
	function ZoneAdd() { // $_POST
		$AddError = FALSE;
		foreach($_POST AS $PostKey => $PostValue) {
			if(!filter_has_var(INPUT_POST, $PostKey) || empty($PostValue)) {
				$AddError = TRUE;
			}
		}
		
		if(!$AddError) {
			if(XBMC::CheckConnection($_POST['ZoneUser'], $_POST['ZonePass'], $_POST['ZoneHost'], $_POST['ZonePort'])) {
				$ZoneAddPrep = $this->PDO->prepare('INSERT INTO Zones (ZoneDate, ZoneName, ZoneXBMCHost, ZoneXBMCPort, ZoneXBMCUsername, ZoneXBMCPassword) VALUES (:ZoneDate, :ZoneName, :ZoneXBMCHost, :ZoneXBMCPort, :ZoneXBMCUsername, :ZoneXBMCPassword)');
				$ZoneAddPrep->execute(array(':ZoneDate'         => time(),
				                            ':ZoneName'         => $_POST['ZoneName'],
				                            ':ZoneXBMCHost'     => $_POST['ZoneHost'],
				                            ':ZoneXBMCPort'     => $_POST['ZonePort'],
				                            ':ZoneXBMCUsername' => $_POST['ZoneUser'],
				                            ':ZoneXBMCPassword' => $_POST['ZonePass']));
			}
		}
		else {
			echo 'You have to fill in all the fields';
		}
	}
	
	function ZoneEdit() { // $_POST
		if(filter_has_var(INPUT_POST, 'id') && filter_has_var(INPUT_POST, 'value')) {
			if(!empty($_POST['id']) || !empty($_POST['value'])) {
				list($EditID, $EditField) = explode('-|-', $_POST['id']);
			
				$ZoneFromDB = self::GetZoneByID($EditID);
			
				if($ZoneFromDB) {
					$ZoneEdit = array_replace($ZoneFromDB, array($EditField => $_POST['value']));
					
					if(XBMC::CheckConnection($ZoneEdit['ZoneXBMCHost'], $ZoneEdit['ZoneXBMCPort'], $ZoneEdit['ZoneXBMCUsername'], $ZoneEdit['ZoneXBMCPassword'])) {
						$ZoneEditPrep = $this->PDO->prepare('UPDATE Zones SET '.$EditField.' = :EditValue WHERE ZoneID = :EditID');
						$ZoneEditPrep->execute(array(':EditValue' => $_POST['value'], ':EditID' => $EditID));
						
						echo $_POST['value'];
					}
				}
			}
		}
	}
	
	function ZoneDelete() {
		if(filter_has_var(INPUT_GET, 'ZoneID')) {
			$ZoneDeletePrep = $this->PDO->prepare('DELETE FROM Zones WHERE ZoneID = :ZoneID');
			$ZoneDeletePrep->execute(array(':ZoneID' => $_GET['ZoneID']));
		}
	}
	
	function GetZoneByID($ID) {
		$ZonePrep = $this->PDO->prepare('SELECT * FROM Zones WHERE ZoneID = :ZoneID');
		$ZonePrep->execute(array(':ZoneID' => $ID));
		
		if($ZonePrep->rowCount()) {
			return $ZonePrep->fetch();
		}
		else {
			return FALSE;
		}
	}
	
	function GetZoneByName($Name) {
		$ZonePrep = $this->PDO->prepare('SELECT * FROM Zones WHERE ZoneName = :ZoneName');
		$ZonePrep->execute(array(':ZoneName' => $Name));
		
		if($ZonePrep->rowCount()) {
			return $ZonePrep->fetch();
		}
		else {
			return FALSE;
		}
	}
	
	function CheckZoneConnection($Name) {
		$Zone = self::GetZoneByName($Name);
		
		if(is_array($Zone)) {
			if(XBMC::CheckConnection($Zone['ZoneXBMCUsername'], $Zone['ZoneXBMCPassword'], $Zone['ZoneXBMCHost'], $Zone['ZoneXBMCPort'])) {
				return TRUE;
			}
		}
		else {
			return FALSE;
		}
	}
	
	function GetCurrentZone() {
		if(filter_has_var(INPUT_COOKIE, 'HubZone')) {
			if(self::IsZone($_COOKIE['HubZone'])) {
				$this->CurrentZone = $_COOKIE['HubZone'];
			}
			else {
				$this->CurrentZone = self::GetDefaultZone();
			}
		}
		else {
			self::SetZone(self::GetDefaultZone());
		}
		
		return $this->CurrentZone;
	}
	
	function SetZone($ZoneName) {
		if(self::IsZone($ZoneName) && self::CheckZoneConnection($ZoneName)) {
			setcookie('HubZone', $ZoneName, (time() + (3600 * 24 * 61)));
			$this->CurrentZone = $ZoneName;
			
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	function ZoneChange($ZoneName) {
		if(self::IsZone($ZoneName)) {
			self::SetZone($ZoneName);
			
			$this->PDO->query('UPDATE Zones SET ZoneDefault = 0');
			$ZonePrep = $this->PDO->prepare('UPDATE Zones SET ZoneDefault = 1 WHERE ZoneName = :ZoneName');
			$ZonePrep->execute(array(':ZoneName' => $ZoneName));
		}
		else {
			$this->CurrentZone = self::GetDefaultZone();
		}
	}
	
	function IsZone($ZoneName) {
		$ZonePrep = $this->PDO->prepare('SELECT * FROM Zones WHERE ZoneName = :ZoneName');
		$ZonePrep->execute(array(':ZoneName' => $ZoneName));
		
		return $ZonePrep->rowCount();
	}
	
	function GetDefaultZone() {
		$Zone = $this->PDO->query('SELECT ZoneName FROM Zones WHERE ZoneDefault = 1')->fetch();
		
		return $Zone['ZoneName'];
	}
	
	function GetZones() {
		return $this->PDO->query('SELECT * FROM Zones')->fetchAll();
	}
}
?>