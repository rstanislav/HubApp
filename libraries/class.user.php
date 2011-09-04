<?php
class User extends Hub {
	public $LoggedIn = FALSE;
	public $User;
	public $UserGroup;
	public $UserGroupID;
	
	function CheckStatus() {
		if(filter_has_var(INPUT_COOKIE, 'HubUser')) {
			$User = self::GetUser($_COOKIE['HubUser']);
			
			if(is_array($User)) {
				$this->LoggedIn    = TRUE;
				$this->User        = $User['UserName'];
				$this->UserGroup   = $User['UserGroupName'];
				$this->UserGroupID = $User['UserGroupID'];
			}
			else {
				self::Logout();
			}
		}
		else {
			self::Logout();
		}
	}
	
	function Logout() {
		setcookie('HubUser', '', (time() - (3600 * 24 * 61)));
		$this->LoggedIn = FALSE;
		unset($this->User);
	}
	
	function Login($User, $Pass) {
		$UserPrep = $this->PDO->prepare('SELECT * FROM User, UserGroups WHERE UserName = :UserName AND UserPassword = :UserPassword AND UserGroupKey = UserGroupID');
		$UserPrep->execute(array(':UserName'     => $User,
		                         ':UserPassword' => md5($Pass)));
		
		if($UserPrep->rowCount()) {
			$UserInfo = $UserPrep->fetch();
			
			$this->LoggedIn = TRUE;
			$this->User     = $UserInfo['UserName'];
			
			setcookie('HubUser',        $_POST['HubUser'],        (time() + (3600 * 24 * 31)));
		}
	}
	
	function GetUserIP() {
		return (filter_has_var(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR')) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
	}
	
	function ResetPassword($UserName, $UserEMail) {
		$UserPrep = $this->PDO->prepare('SELECT * FROM User WHERE UserName = :User OR UserEMail = :EMail');
		$UserPrep->execute(array(':User'  => $UserName,
		                         ':EMail' => $UserEMail));
		
		$IP = User::GetUserIP();
		if($UserPrep->rowCount()) {
			foreach($UserPrep AS $User) {
				$NewPassword = Hub::GetRandomID();
				$UserPrep = $this->PDO->prepare('UPDATE User SET UserPassword = :NewPass WHERE UserName = :User OR UserEMail = :EMail');
				$UserPrep->execute(array(':NewPass' => md5($NewPassword),
			                             ':User'    => $UserName,
			                             ':EMail'   => $UserEMail));
			    
			    if($UserName && $UserEMail) {
			   		$LogEntry = ' for user "'.$UserName.'" with the e-mail "'.$UserEMail.'"';
			   	}
			   	else if($UserName) {
			   		$LogEntry = ' for user "'.$UserName.'"';
			   	}
			   	else if($UserEMail) {
			   		$LogEntry = ' for a user with the e-mail "'.$UserEMail.'"';
			   	}
			    	
			    $Message = 'A request to reset the password for '.$LogEntry.' has been taken care of. Your new password is: '.$NewPassword;
			    mail($User['UserEMail'], 'Hub Password Reset', $Message);
			
				Hub::AddLog(EVENT.'Password', 'Success', $IP.' successfully reset the password '.$LogEntry.'. An e-mail has been sent to "'.$User['UserEMail'].'"');
				
				return TRUE;
			}
		}
		else {
			Hub::AddLog(EVENT.'Password', 'Failure', $IP.' tried to reset a user password');
			
			return FALSE;
		}
	}
	
	function GetUsers() {
		$UserPrep = $this->PDO->prepare('SELECT * FROM User, UserGroups WHERE User.UserGroupKey = UserGroups.UserGroupID');
		$UserPrep->execute();
		
		if($UserPrep->rowCount()) {
			return $UserPrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function GetUserGroups() {
		$UserGroupPrep = $this->PDO->prepare('SELECT * FROM UserGroups');
		$UserGroupPrep->execute();
		
		if($UserGroupPrep->rowCount()) {
			return $UserGroupPrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function GetGroupPermission($PermissionID, $UserGroupID) {
		$CheckPermissionPrep = $this->PDO->prepare('SELECT * FROM UserGroupPermissions WHERE PermissionKey = :PermissionID AND UserGroupKey = :UserGroupID');
		$CheckPermissionPrep->execute(array(':PermissionID' => $PermissionID,
		                                    ':UserGroupID'  => $UserGroupID));
		
		if($CheckPermissionPrep->rowCount()) {
			return $CheckPermissionPrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function PermissionEdit() { // $_POST
		if(filter_has_var(INPUT_POST, 'id') && filter_has_var(INPUT_POST, 'value')) {
			if(!empty($_POST['id']) || !empty($_POST['value'])) {
				list($EditID, $EditField) = explode('-|-', $_POST['id']);
			
				$PermissionFromDB = self::GetPermissionByID($EditID);
			
				if($PermissionFromDB) {
					$PermissionEdit = array_replace($PermissionFromDB, array($EditField => $_POST['value']));
					
					$PermissionEditPrep = $this->PDO->prepare('UPDATE Permissions SET '.$EditField.' = :EditValue WHERE PermissionID = :EditID');
					$PermissionEditPrep->execute(array(':EditValue' => $_POST['value'], ':EditID' => $EditID));
						
					echo $_POST['value'];
				}
			}
		}
	}

	function PermissionAdd() { // $_POST
		$AddError = FALSE;
		foreach($_POST AS $PostKey => $PostValue) {
			if(!filter_has_var(INPUT_POST, $PostKey) || empty($PostValue)) {
				$AddError = TRUE;
			}
		}
		
		if(!$AddError) {
			$Permission = $this->PDO->query('SELECT COUNT(*) AS Total FROM Permissions')->fetch();
			
			$PermissionAddPrep = $this->PDO->prepare('INSERT INTO Permissions (PermissionID, PermissionDate, PermissionAction, PermissionText, PermissionValue) VALUES (NULL, :Date, :Action, :Text, :Value)');
			$PermissionAddPrep->execute(array(':Date'   => time(),
			                            	  ':Action' => $_POST['PermissionAction'],
			                            	  ':Text'   => $_POST['PermissionText'],
			                            	  ':Value'  => pow(($Permission['Total'] + 1), 2)));
		}
		else {
			echo 'You have to fill in all the fields';
		}
	}
	
	function GetPermissionByID($ID) {
		$PermissionPrep = $this->PDO->prepare('SELECT * FROM Permissions WHERE PermissionID = :PermissionID');
		$PermissionPrep->execute(array(':PermissionID' => $ID));
		
		if($PermissionPrep->rowCount()) {
			return $PermissionPrep->fetch();
		}
		else {
			return FALSE;
		}
	}
	
	function GetUserGroup($ID) {
		$UserGroupPrep = $this->PDO->prepare('SELECT * FROM UserGroups WHERE UserGroupID = :ID');
		$UserGroupPrep->execute(array(':ID' => $ID));
		
		if($UserGroupPrep->rowCount()) {
			return $UserGroupPrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function GetPermissions() {
		$PermissionPrep = $this->PDO->prepare('SELECT * FROM Permissions ORDER BY PermissionText');
		$PermissionPrep->execute();
		
		if($PermissionPrep->rowCount()) {
			return $PermissionPrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function CheckPermission($UserGroupID, $PermissionAction) {
		$Permission = $this->PDO->query('SELECT PermissionID FROM Permissions WHERE PermissionAction = "'.$PermissionAction.'"')->fetch();
		
		if($Permission['PermissionID']) {
			$PermissionPrep = $this->PDO->prepare('SELECT * FROM UserGroupPermissions WHERE UserGroupKey = :GroupID AND PermissionKey = :PermissionID');
			$PermissionPrep->execute(array(':GroupID'      => $UserGroupID,
		                                   ':PermissionID' => $Permission['PermissionID']));
		
			if($PermissionPrep->rowCount()) {
				return TRUE;
			}
			else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}
	
	function GetUser($UserName) {
		$UserPrep = $this->PDO->prepare('SELECT * FROM User, UserGroups WHERE UserName = :UserName AND UserGroupKey = UserGroupID');
		$UserPrep->execute(array(':UserName' => $UserName));
		
		if($UserPrep->rowCount()) {
			return $UserPrep->fetch();
		}
		else {
			return FALSE;
		}
	}
}
?>