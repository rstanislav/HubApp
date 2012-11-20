<?php
/**
 * //@protected
**/
class Users {
	private $PDO;
	
	function __construct() {
		$this->PDO = DB::Get();
	}
	
	/**
	 * @url GET /
 	**/
	function UsersAll() {
		try {
			$UsersPrep = $this->PDO->prepare('SELECT
			                                  	*
			                                  FROM
			                                  	User,
			                                  	UserGroups
			                                  WHERE
			                                  	User.UserGroupKey = UserGroups.ID');
		                                     	
			$UsersPrep->execute();
		
			if($UsersPrep->rowCount()) {
				return $UsersPrep->fetchAll();
			}
			else {
				throw new RestException(404, 'Did not find any users matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url POST /
	**/
	function AddUser() {
		$RequiredParameters = array('Name',
		                            'Password',
		                            'EMail',
		                            'UserGroupKey');
		
		if(sizeof($_POST) != 4) {
			throw new RestException(412, 'Invalid request. Required parameters are "'.implode(', ', $RequiredParameters).'"');
		}
		
		foreach($_POST AS $Key => $Value) {
			if(!in_array($Key, $RequiredParameters)) {
				throw new RestException(412, 'Invalid request. Required parameters are "'.implode(', ', $RequiredParameters).'"');
			}
		}
		
		try {
			$UserCheckPrep = $this->PDO->prepare('SELECT
			                           	          	*
			                                      FROM
			                           	          	User
			                                      WHERE
			                           	          	Name = :Name
			                                      OR
			                           	          	EMail = :EMail');
			
			$UserCheckPrep->execute(array(':Name'  => $_POST['Name'],
			                              ':EMail' => $_POST['EMail']));
			
			if($UserCheckPrep->rowCount()) {
				throw new RestException(412, 'A user already exists with that information');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		if(!filter_var($_POST['UserEMail'], FILTER_VALIDATE_EMAIL)) {
			throw new RestException(412, '"'.$_POST['EMail'].'" is not a valid e-mail address');
		}
		
		try {
			$UserAddPrep = $this->PDO->prepare('INSERT INTO
													User
														(Date,
														Name,
														Password,
														EMail,
														UserGroupKey)
													VALUES
														(:Date,
														:Name,
														:EMail,
														:UserGroupKey)');
														
			$UserAddPrep->execute(array(':Date'     => time(),
			                            ':Name'     => $_POST['Name'],
			                            ':Password' => md5($_POST['Password']),
			                            ':EMail'    => $_POST['EMail'],
			                            ':UserGroupKey' => $_POST['UserGroupKey']));
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Added user "'.$_POST['Name'].'" with e-mail "'.$_POST['EMail'].'"';
		
		AddLog(EVENT.'Users', 'Success', $LogEntry);
		throw new RestException(201, $LogEntry);
	}
	
	/**
	 * @url POST /login
	**/
	function Login() {
		try {
			$UserPrep = $this->PDO->prepare('SELECT
			                                 	*
			                                 FROM
			                                 	User,
			                                 	UserGroups
			                                 WHERE
			                                 	Name = :UserName
			                                 AND
			                                 	Password = :UserPassword
			                                 AND
			                                 	User.UserGroupKey = UserGroups.ID');
			                                 	
			$UserPrep->execute(array(':Name'     => $User,
									 ':Password' => md5($Pass)));
			
			if($UserPrep->rowCount()) {
				throw new RestException(200, 'User is permitted to log in');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		throw new RestException(404, 'Wrong user, pass or e-mail');
	}
	
	/**
	 * @url GET /groups
	**/
	function UserGroupsAll() {
		try {
			$UserGroupPrep = $this->PDO->prepare('SELECT
			                                      	*
			                                      FROM
			                                      	UserGroups');
			                                      	
			$UserGroupPrep->execute();
			
			if($UserGroupPrep->rowCount()) {
				return $UserGroupPrep->fetchAll();
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		throw new RestException(404, 'Did not find any user groups');
	}
	
	/**
	 * @url POST /groups
	**/
	function AddUserGroup() {
		$RequiredParameters = array('Name');
		
		if(sizeof($_POST) != 4) {
			throw new RestException(412, 'Invalid request. Required parameters are "'.implode(', ', $RequiredParameters).'"');
		}
		
		foreach($_POST AS $Key => $Value) {
			if(!in_array($Key, $RequiredParameters)) {
				throw new RestException(412, 'Invalid request. Required parameters are "'.implode(', ', $RequiredParameters).'"');
			}
		}
		
		try {
			$UserCheckPrep = $this->PDO->prepare('SELECT
			                           	          	*
			                                      FROM
			                           	          	UserGroups
			                                      WHERE
			                           	          	Name = :Name');
			
			$UserCheckPrep->execute(array(':Name' => $_POST['Name']));
			
			if($UserCheckPrep->rowCount()) {
				throw new RestException(412, 'A user group already exists with that information');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		try {
			$UserAddPrep = $this->PDO->prepare('INSERT INTO
													UserGroups
														(Date,
														Name)
													VALUES
														(:Date,
														:Name)');
														
			$UserAddPrep->execute(array(':Date' => time(),
			                            ':Name' => $_POST['Name']));
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Added user group "'.$_POST['Name'].'"';
		
		AddLog(EVENT.'Users', 'Success', $LogEntry);
		throw new RestException(201, $LogEntry);
	}
	
	/**
	 * @url GET /groups/:ID
	**/
	function GetUserGroup($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		try {
			$UserGroupPrep = $this->PDO->prepare('SELECT
			                                  	  	*
			                                      FROM
			                                  	  	UserGroups
			                                      WHERE
			                                   	  	ID = :ID');
			                                  	
			$UserGroupPrep->execute(array(':ID' => $ID));
			
			if($UserGroupPrep->rowCount()) {
				return $UserGroupPrep->fetchAll();
			}
			else {
				throw new RestException(404, 'Did not find any user group in the database matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url DELETE /groups/:ID
	**/
	function DeleteUserGroup($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		$LogEntry = '';
		try {	
			$UserGroupDeletePrep = $this->PDO->prepare('DELETE FROM
												  	    	UserGroups
												        WHERE
												   	    	ID = :ID');
												   	
			$UserGroupDeletePrep->execute(array(':ID' => $ID));
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		try {	
			$UserDeletePrep = $this->PDO->prepare('DELETE FROM
												   	User
												   WHERE
												   	UserGroupKey = :ID');
												   	
			$UserDeletePrep->execute(array(':ID' => $ID));
			$DeletedUsers = $UserDeletePrep->rowCount();
			
			if($DeletedUsers) {
				$LogEntry .= 'Deleted '.$DeletedUsers.' belonging to the user group with the ID "'.$ID.'" from the database'."\n";
			}
			
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry .= 'Deleted user group with the ID "'.$ID.'" from the database';
		
		AddLog(EVENT.'Users', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url POST /groups/update/:ID
	**/
	function UpdateUserGroup($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		$AcceptedParameters = array('Name',
		                            'PermissionKey');
		
		if(!sizeof($_POST)) {
			throw new RestException(412, 'Invalid request. Accepted parameters are "'.implode(', ', $AcceptedParameters).'"');
		}
		
		$LogEntry = '';
		foreach($_POST AS $Key => $Value) {
			if(!in_array($Key, $AcceptedParameters)) {
				throw new RestException(412, 'Invalid request. Accepted parameters are "'.implode(', ', $AcceptedParameters).'"');
			}
			
			if($Key == 'PermissionKey' && is_array($Value)) {
				try {
					$UserGroupPermissionsPrep = $this->PDO->prepare('DELETE FROM
					                                                 	UserGroupPermissions
					                                                 WHERE
					                                                 	UserGroupKey = :ID');
					                                                 
					$UserGroupPermissionsPrep->execute(array(':ID' => $ID));
				}
				catch(PDOException $e) {
					throw new RestException(400, 'MySQL: '.$e->getMessage());
				}
				
				foreach($Value AS $PermissionKey) {
					try {
						$UserGroupPermissionsPrep = $this->PDO->prepare('INSERT INTO
						                                                 	UserGroupPermissions
						                                                 		(UserGroupKey,
						                                                 		PermissionKey)
						                                                 	VALUES
						                                                 		(:ID,
						                                                 		:PermissionKey)');
						                                              
						$UserGroupPermissionsPrep->execute(array(':ID'            => $ID,
						                                         ':PermissionKey' => $PermissionKey));
						                                         
						$AddedPermissions = $UserGroupPermissionsPrep->rowCount();
						if($AddedPermissions) {
							$LogEntry .= 'User group with ID "'.$ID.'" now has '.$AddedPermissions.' permissions'."\n";
						}
					}
					catch(PDOException $e) {
						throw new RestException(400, 'MySQL: '.$e->getMessage());
					}
				}
			}
		}
		
		try {
			$UserGroupPrep = $this->PDO->prepare('UPDATE
			                                      	UserGroups
			                                      SET
			                                      	Name = :Name
			                                      WHERE
			                                      	ID = :ID');
			                                      	
			$UserGroupPrep->execute(array(':ID'            => $ID,
			                              ':Name' => $_POST['Name']));
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry .= 'Updated user group with the ID "'.$ID.'" in the database';
		
		AddLog(EVENT.'Users', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url GET /permissions
	**/
	function PermissionsAll() {
		try {
			$PermissionsPrep = $this->PDO->prepare('SELECT
			                                  	  		*
			                                        FROM
			                                  	  		Permissions
			                                  	  	ORDER BY
			                                  	  		Text');
			                                  	
			$PermissionsPrep->execute();
			
			if($PermissionsPrep->rowCount()) {
				return $PermissionsPrep->fetchAll();
			}
			else {
				throw new RestException(404, 'Did not find any permissions  in the database matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url POST /permissions
	**/
	function AddPermission() {
		$RequiredParameters = array('Action',
		                            'Text');
		
		if(sizeof($_POST) != 2) {
			throw new RestException(412, 'Invalid request. Required parameters are "'.implode(', ', $RequiredParameters).'"');
		}
		
		foreach($_POST AS $Key => $Value) {
			if(!in_array($Key, $RequiredParameters)) {
				throw new RestException(412, 'Invalid request. Required parameters are "'.implode(', ', $RequiredParameters).'"');
			}
		}
		
		try {
			$UserCheckPrep = $this->PDO->prepare('SELECT
			                           	          	*
			                                      FROM
			                           	          	Permissions
			                                      WHERE
			                           	          	Action = :Action
			                           	          OR
			                           	          	Text = :Text');
			
			$UserCheckPrep->execute(array(':Action' => $_POST['Action'],
			                              ':Text'   => $_POST['Text']));
			
			if($UserCheckPrep->rowCount()) {
				throw new RestException(412, 'A permission already exists with that information');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		try {
			$Permission = $this->PDO->query('SELECT COUNT(*) AS Total FROM Permissions')->fetch();
			
			$UserAddPrep = $this->PDO->prepare('INSERT INTO
													Permissions
														(Date,
														Action,
														Text,
														Value)
													VALUES
														(:Date,
														:Action,
														:Text,
														:Value)');
														
			$UserAddPrep->execute(array(':Date' => time(),
			                            ':Action' => $_POST['Action'],
			                            ':Text'   => $_POST['Text'],
			                            ':Value'  => pow(($Permission['Total'] + 1), 2)));
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Added permission "'.$_POST['Text'].'"';
		
		AddLog(EVENT.'Users', 'Success', $LogEntry);
		throw new RestException(201, $LogEntry);
	}
	
	/**
	 * @url DELETE /permissions/:ID
	**/
	function DeletePermission($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		try {
			$PermissionsDeletePrep = $this->PDO->prepare('DELETE FROM
			                                              	Permissions
			                                              WHERE
			                                              	ID = :ID');
			                                  	
			$PermissionsDeletePrep->execute(array(':ID' => $ID));
			
			$DeletedPermissions = $PermissionsDeletePrep->rowCount();
			if($DeletedPermissions) {
				$LogEntry = 'Deleted permission with ID "'.$ID.'" from the database';
				
				AddLog(EVENT.'Users', 'Success', $LogEntry);
				throw new RestException(200, $LogEntry);
			}
			else {
				throw new RestException(404, 'Did not find any permissions in the database matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url POST /permissions/update/:ID
	**/
	function UpdatePermission($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		$AcceptedParameters = array('Action',
		                            'Text');
		
		if(!sizeof($_POST)) {
			throw new RestException(412, 'Invalid request. Accepted parameters are "'.implode(', ', $AcceptedParameters).'"');
		}
		
		$UpdateQuery = 'UPDATE Permissions SET ';
		$PrepArr = array();
		$i = 0;
		foreach($_POST AS $Key => $Value) {
			if(!in_array($Key, $AcceptedParameters)) {
				throw new RestException(412, 'Invalid request. Accepted parameters are "'.implode(', ', $AcceptedParameters).'"');
			}
			
			$UpdateQuery .= ' '.$Key.' = :'.$Key;
			$PrepArr[':'.$Key] = $Value;
			
			if(++$i != sizeof($_POST)) {
				$UpdateQuery .= ', ';
			}
			else {
				$UpdateQuery .= ' WHERE ID = :ID';
				$PrepArr[':ID'] = $ID;
			}
		}
		
		try {
			$RSSPrep = $this->PDO->prepare($UpdateQuery);
			$RSSPrep->execute($PrepArr);
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Updated permission with the ID "'.$ID.'" in the database';
		
		AddLog(EVENT.'Users', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url GET /:ID
	**/
	function GetUser($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		try {
			$UserPrep = $this->PDO->prepare('SELECT
			                                  *
			                                 FROM
			                                  User
			                                 WHERE
			                                  ID = :ID');
			                                  	
			$UserPrep->execute(array(':ID' => $ID));
			
			if($UserPrep->rowCount()) {
				return $UserPrep->fetchAll();
			}
			else {
				throw new RestException(404, 'Did not find any user in the database matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url DELETE /:ID
	**/
	function DeleteUser($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		$LogEntry = '';
		try {	
			$UserDeletePrep = $this->PDO->prepare('DELETE FROM
												   	User
												   WHERE
												   	ID = :ID');
												   	
			$UserDeletePrep->execute(array(':ID' => $ID));
			$DeletedUser = $UserDeletePrep->rowCount();
			
			if($DeletedUser) {
				$LogEntry .= 'Deleted user with the ID "'.$ID.'" from the database'."\n";
			}
			
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		AddLog(EVENT.'Users', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url POST /update/:ID
	**/
	function UpdateUser($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		$AcceptedParameters = array('Name',
		                            'Password',
		                            'EMail',
		                            'UserGroupKey');
		
		if(!sizeof($_POST)) {
			throw new RestException(412, 'Invalid request. Accepted parameters are "'.implode(', ', $AcceptedParameters).'"');
		}
		
		$UpdateQuery = 'UPDATE User SET ';
		$PrepArr = array();
		$i = 0;
		foreach($_POST AS $Key => $Value) {
			if(!in_array($Key, $AcceptedParameters)) {
				throw new RestException(412, 'Invalid request. Accepted parameters are "'.implode(', ', $AcceptedParameters).'"');
			}
			
			$UpdateQuery .= ' '.$Key.' = :'.$Key;
			
			if($Key == 'UserEMail') {
				if(!filter_var($_POST['UserEMail'], FILTER_VALIDATE_EMAIL)) {
					throw new RestException(412, 'Invalid request. "'.$_POST['EMail'].'" is not a valid e-mail address');
				}
				
				$PrepArr[':'.$Key] = $Value;
			}
			else if($Key == 'UserPassword') {
				$PrepArr[':'.$Key] = md5($Value);
			}
			else {
				$PrepArr[':'.$Key] = $Value;
			}
			
			if(++$i != sizeof($_POST)) {
				$UpdateQuery .= ', ';
			}
			else {
				$UpdateQuery .= ' WHERE ID = :ID';
				$PrepArr[':ID'] = $ID;
			}
		}
		
		try {
			$RSSPrep = $this->PDO->prepare($UpdateQuery);
			$RSSPrep->execute($PrepArr);
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Updated user with the ID "'.$ID.'" in the database';
		
		AddLog(EVENT.'Users', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
}
?>