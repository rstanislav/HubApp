<?php
class User extends Hub {
	public $LoggedIn = FALSE;
	public $User;
	public $UserID;
	public $UserGroup;
	public $UserGroupID;
	public $UserEMail;
	
	function CheckStatus() {
		if(filter_has_var(INPUT_COOKIE, 'HubUser')) {
			$User = self::GetUser($_COOKIE['HubUser']);
			
			if(is_array($User)) {
				$this->LoggedIn    = TRUE;
				$this->User        = $User['UserName'];
				$this->UserID      = $User['UserID'];
				$this->UserGroup   = $User['UserGroupName'];
				$this->UserGroupID = $User['UserGroupID'];
				$this->UserEMail   = $User['UserEMail'];
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
			
			setcookie('HubUser', $_POST['HubUser'], (time() + (3600 * 24 * 31)));
		}
	}
	
	function UserDelete($UserID) {
		$User = $this->GetUserByID($UserID);
		
		$UserDeletePrep = $this->PDO->prepare('DELETE FROM User WHERE UserID = :ID');
		$UserDeletePrep->execute(array(':ID' => $UserID));
		
		Hub::AddLog(EVENT.'Users', 'Success', 'Deleted user "'.$User['UserName'].'"');
	}
	
	function UserGroupDelete($UserGroupID) {
		$UserGroup = $this->GetUserGroupByID($UserGroupID);
		
		$UserGroupDeletePrep = $this->PDO->prepare('DELETE FROM UserGroups WHERE UserGroupID = :ID');
		$UserGroupDeletePrep->execute(array(':ID' => $UserGroupID));
		
		$UserGroupUsersDeletePrep = $this->PDO->prepare('DELETE FROM User WHERE UserGroupKey = :ID');
		$UserGroupUsersDeletePrep->execute(array(':ID' => $UserGroupID));
		
		Hub::AddLog(EVENT.'User Groups', 'Success', 'Deleted user group "'.$UserGroup['UserGroupName'].'" along with all users present in that group');
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
	
	function ProfileSave() {
		if(!filter_var($_POST['UserEMail'], FILTER_VALIDATE_EMAIL)) {
			echo 'The supplied e-mail adress is not valid';
		}
		else {
			if(!empty($_POST['UserCurrentPass'])) {
				$User = $this->PDO->query('SELECT * FROM User WHERE UserPassword = "'.md5($_POST['UserCurrentPass']).'"')->fetch();
				
				if(is_array($User)) {
					if(empty($_POST['UserNewPass1']) || empty($_POST['UserNewPass2'])) {
						echo 'You have to type in both new password fields in order to change password';
					}
					else if($_POST['UserNewPass1'] != $_POST['UserNewPass2']) {
						echo 'The new password does not match each other';
					}
					else {
						$UserEditPrep = $this->PDO->prepare('UPDATE User SET UserEMail = :EMail, UserPassword = :Password WHERE UserID = :UserID');
						$UserEditPrep->execute(array(':EMail'    => $_POST['UserEMail'],
													 ':Password' => md5($_POST['UserNewPass1']),
													 ':UserID'   => $this->UserID));
													 
						Hub::AddLog(EVENT.'Users', 'Success', 'User "'.$this->User.'" updated their profile');
					}
				}
				else {
					echo 'You typed the wrong password';
				}
			}
			else if(!empty($_POST['UserNewPass1']) || !empty($_POST['UserNewPass2'])) {
				echo 'You need to type in your current password in order to change it';
			}
			else {
				$UserEditPrep = $this->PDO->prepare('UPDATE User SET UserEMail = :EMail WHERE UserID = :UserID');
				$UserEditPrep->execute(array(':EMail'  => $_POST['UserEMail'],
											 ':UserID' => $this->UserID));
											 
				Hub::AddLog(EVENT.'Users', 'Success', 'User "'.$this->User.'" updated their profile');
			}
		}		
	}
	
	function UserAdd() {// $_POST
		$AddError = FALSE;
		foreach($_POST AS $PostKey => $PostValue) {
			if(!filter_has_var(INPUT_POST, $PostKey) || empty($PostValue)) {
				$AddError = TRUE;
			}
		}
		
		if(!$AddError) {
			$User = $this->PDO->query('SELECT * FROM User WHERE UserName = "'.$_POST['UserName'].'" OR UserEMail = "'.$_POST['UserEMail'].'"')->fetch();
			
			if(!is_array($User)) {
				if(!filter_var($_POST['UserEMail'], FILTER_VALIDATE_EMAIL)) {
					echo 'The supplied e-mail adress is not valid';
				}
				else {
					$UserAddPrep = $this->PDO->prepare('INSERT INTO User (UserID, UserDate, UserName, UserPassword, UserEMail, UserGroupKey) VALUES (NULL, :Date, :UserName, :Password, :EMail, :GroupID)');
					$UserAddPrep->execute(array(':Date'     => time(),
												':UserName' => $_POST['UserName'],
												':Password' => md5(strtolower($_POST['UserName'])),
												':EMail'    => $_POST['UserEMail'],
												':GroupID'  => $_POST['UserGroup']));
												
					Hub::AddLog(EVENT.'Users', 'Success', 'Added "'.$_POST['UserName'].'" with e-mail address "'.$_POST['UserEMail'].'"');
				}
			}
			else {
				echo 'A user already exists with that combination';
			}
		}
		else {
			echo 'You have to fill in all the fields';
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
	
	function GetNotifications() {
		$NotificationPrep = $this->PDO->prepare('SELECT * FROM Notifications ORDER BY NotificationText');
		$NotificationPrep->execute();
		
		if($NotificationPrep->rowCount()) {
			return $NotificationPrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}
	
	function GetUserNotification($NotificationID, $UserID) {
		$UserNotificationPrep = $this->PDO->prepare('SELECT * FROM UserNotifications WHERE NotificationKey = :NotificationID AND UserKey = :UserID');
		$UserNotificationPrep->execute(array(':NotificationID' => $NotificationID,
											 ':UserID'         => $UserID));
		
		if($UserNotificationPrep->rowCount()) {
			return $UserNotificationPrep->fetchAll();
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
	
	function GetUserByID($UserID) {
		$UserPrep = $this->PDO->prepare('SELECT * FROM User, UserGroups WHERE UserID = :ID AND UserGroupKey = UserGroupID');
		$UserPrep->execute(array(':ID' => $UserID));
		
		if($UserPrep->rowCount()) {
			return $UserPrep->fetch();
		}
		else {
			return FALSE;
		}
	}
	
	function GetUserGroupByID($UserGroupID) {
		$UserGroupPrep = $this->PDO->prepare('SELECT * FROM UserGroups WHERE UserGroupID = :ID');
		$UserGroupPrep->execute(array(':ID' => $UserGroupID));
		
		if($UserGroupPrep->rowCount()) {
			return $UserGroupPrep->fetch();
		}
		else {
			return FALSE;
		}
	}
}
?>