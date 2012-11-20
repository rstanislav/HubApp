<?php
/**
 * //@protected
**/
class Wishlist {
	private $PDO;
	
	function __construct() {
		$this->PDO = DB::Get();
	}
	
	/**
	 * @url GET /
	**/
	function WishlistAll() {
		try {
			$WishlistPrep = $this->PDO->prepare('SELECT
		                                     	 	*
		                                     	 FROM
		                                     	 	Wishlist
		                                      	 WHERE
		                                     	 	DownloadDate = ""
		                                     	 ORDER BY
		                                     	 	Title');
		                                     	
			$WishlistPrep->execute();
			$WishlistRes = $WishlistPrep->fetchAll();
			
			if(sizeof($WishlistRes)) {
				return $WishlistRes;
			}
			else {
				throw new RestException(404, 'Did not find any wishlist items matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url GET /granted
	**/
	function WishlistGranted() {
		try {
			$WishlistPrep = $this->PDO->prepare('SELECT
		                                     	 	*
		                                     	 FROM
		                                     	 	Wishlist
		                                      	 WHERE
		                                     	 	DownloadDate != ""
		                                     	 ORDER BY
		                                     	 	DownloadDate
		                                     	 DESC');
		                                     	
			$WishlistPrep->execute();
			$WishlistRes = $WishlistPrep->fetchAll();
			
			if(sizeof($WishlistRes)) {
				return $WishlistRes;
			}
			else {
				throw new RestException(404, 'Did not find any wishlist items matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url GET /refresh
	**/
	function WishlistRefresh() {
		$DrivesObj = new Drives;
		$Movies    = $DrivesObj->GetMovieFiles();
		
		try {
			$WishlistRefreshPrep = $this->PDO->prepare('UPDATE
			                                            	Wishlist
			                                            SET
			                                            	File = null,
			                                            	IsFileGone = 1
			                                            WHERE
			                                            	DownloadDate != 0
			                                            AND
			                                            	File != ""
			                                            OR
			                                            	(DownloadDate != 0
			                                            AND
			                                            	TorrentKey != 0)');
			                                            	
			$WishlistRefreshPrep->execute();
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$WishlistItems = 0;
		foreach($Movies AS $Movie) {
			$ParsedInfo = ParseRelease($Movie);
			
			if(is_array($ParsedInfo)) {
				try {
					$WishItemPrep = $this->PDO->prepare('SELECT
					                                     	*
					                                     FROM
					                                     	Wishlist
					                                     WHERE
					                                     	Title = :Title
					                                     AND
					                                     	Year = :Year');
					                                     	
					$WishItemPrep->execute(array(':Title' => $ParsedInfo['Title'],
												 ':Year'  => $ParsedInfo['Year']));
					
					if($WishItemPrep->rowCount()) {
						$WishlistItem = $WishItemPrep->fetch();
					}
				}
				catch(PDOException $e) {
					throw new RestException(400, 'MySQL: '.$e->getMessage());
				}
				
				if(isset($WishlistItem)) {
					if(!$WishlistItem['DownloadDate']) {
						$WishlistDownloadDate = time();
					}
					else {
						$WishlistDownloadDate = $WishlistItem['DownloadDate'];
					}
					
					try {
						$WishlistRefreshPrep = $this->PDO->prepare('UPDATE
						                                            	Wishlist
						                                            SET
						                                            	File = :File,
						                                            	IsFileGone = 0,
						                                            	DownloadDate = :Date
						                                            WHERE
						                                            	Title = :Title
						                                            AND
						                                            	Year = :Year');
						                                            	
						$WishlistRefreshPrep->execute(array('File'  => $Movie,
														    'Date'  => $WishlistDownloadDate,
														    'Title' => $ParsedInfo['Title'],
														    'Year'  => $ParsedInfo['Year']));
														
						$WishlistItems++;
					}
					catch(PDOException $e) {
						throw new RestException(400, 'MySQL: '.$e->getMessage());
					}
				}
			}
		}
		
		try {
			$UpdatePrep = $this->PDO->prepare('UPDATE
			                                   	Hub
			                                   SET
			                                   	Value = :Time
			                                   WHERE
			                                   	Setting = "LastWishlistRefresh"');
			                                   	
			$UpdatePrep->execute(array(':Time' => time()));
			
			if($WishlistItems) {
				$LogEntry = 'Refreshed '.$WishlistItems.' wishlist items';
				
				AddLog(EVENT.'Wishlist', 'Success', $LogEntry);
				throw new RestException(200, $LogEntry);
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
	
	/**
	 * @url POST /
	**/
	function AddWishlistItem($Title, $Year) {
		if(empty($Title) || empty($Year)) {
			throw new RestException(412, 'Invalid request. Required parameters are "Title", "Year"');
		}
		
		try {
			$WishlistAddPrep = $this->PDO->prepare('INSERT INTO
														Wishlist
															(Date,
															Title,
															Year)
														VALUES
															(:Date,
															:Title,
															:Year)');
														
			$WishlistAddPrep->execute(array(':Date'  => time(),
			                                ':Title' => $Title,
			                                ':Year'  => $Year));
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Added "'.$Title.' ('.$Year.')" to the wishlist';
		
		AddLog(EVENT.'Wishlist', 'Success', $LogEntry);
		throw new RestException(201, $LogEntry);
	}
	
	/**
	 * @url DELETE /:ID
	**/
	function DeleteWishlistItem($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		try {
			$WishlistDeletePrep = $this->PDO->prepare('DELETE FROM
			                            	           	Wishlist
			                                           WHERE
			                            	            ID = :ID');
			                            	
			$WishlistDeletePrep->execute(array(':ID' => $ID));
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Deleted wishlist item from the database with ID "'.$ID.'"';
		
		AddLog(EVENT.'Wishlist', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
	
	/**
	 * @url POST /update/:ID
	**/
	function UpdateWishlistItem($ID) {
		if(!is_numeric($ID)) {
			throw new RestException(412, 'ID must be a numeric value');
		}
		
		$AcceptedParameters = array('Title',
		                            'Year');
		
		if(!sizeof($_POST)) {
			throw new RestException(412, 'Invalid request. Accepted parameters are "'.implode(', ', $AcceptedParameters).'"');
		}
		
		$UpdateQuery = 'UPDATE Wishlist SET ';
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
			$ZonePrep = $this->PDO->prepare($UpdateQuery);
			$ZonePrep->execute($PrepArr);
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
		
		$LogEntry = 'Updated wishlist item with ID "'.$ID.'"';
		
		AddLog(EVENT.'Wishlist', 'Success', $LogEntry);
		throw new RestException(200, $LogEntry);
	}
}
?>