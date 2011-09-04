<?php
class Wishlist extends Hub {
	function GetFulfilledWishlistItems() {
		$WishPrep = $this->PDO->prepare('SELECT * FROM Wishlist WHERE WishlistFile = "" OR TorrentKey = "" ORDER BY WishlistTitle');
		$WishPrep->execute();
		
		if($WishPrep->rowCount()) {
			return $WishPrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}

	function GetUnfulfilledWishlistItems() {
		$WishPrep = $this->PDO->prepare('SELECT * FROM Wishlist WHERE WishlistFile != "" OR TorrentKey != "" ORDER BY WishlistTitle');
		$WishPrep->execute();
		
		if($WishPrep->rowCount()) {
			return $WishPrep->fetchAll();
		}
		else {
			return FALSE;
		}
	}

	function WishlistAdd() { // $_POST
		$AddError = FALSE;
		foreach($_POST AS $PostKey => $PostValue) {
			if(!filter_has_var(INPUT_POST, $PostKey) || empty($PostValue)) {
				$AddError = TRUE;
			}
		}
		
		if(!$AddError) {
			$WishlistAddPrep = $this->PDO->prepare('INSERT INTO Wishlist (WishlistID, WishlistDate, WishlistTitle, WishlistYear) VALUES (NULL, :Date, :Title, :Year)');
			$WishlistAddPrep->execute(array(':Date'   => time(),
			                            	':Title' => $_POST['WishlistTitle'],
			                            	':Year'   => $_POST['WishlistYear']));
		}
		else {
			echo 'You have to fill in all the fields';
		}
	}
	
	function WishlistEdit() { // $_POST
		if(filter_has_var(INPUT_POST, 'id') && filter_has_var(INPUT_POST, 'value')) {
			if(!empty($_POST['id']) || !empty($_POST['value'])) {
				list($EditID, $EditField) = explode('-|-', $_POST['id']);
			
				$WishItemFromDB = self::GetWishlistItemByID($EditID);
			
				if($WishItemFromDB) {
					$WishItemEdit = array_replace($WishItemFromDB, array($EditField => $_POST['value']));
					
					$WishItemEditPrep = $this->PDO->prepare('UPDATE Wishlist SET '.$EditField.' = :EditValue WHERE WishlistID = :EditID');
					$WishItemEditPrep->execute(array(':EditValue' => $_POST['value'],
					                                 ':EditID'    => $EditID));
						
					echo $_POST['value'];
				}
			}
		}
	}
	
	function WishlistDelete() {
		if(filter_has_var(INPUT_GET, 'WishlistID')) {
			$Wishlist = $this->PDO->query('SELECT WishlistTitle, WishlistYear, WishlistFile FROM Wishlist WHERE WishlistID = "'.$_GET['WishlistID'].'"')->fetch();
			
			$AddLogEntry = '';
			if(is_file($Wishlist['WishlistFile'])) {
				if(unlink($Wishlist['WishlistFile'])) {
					$AddLogEntry = ' and deleted "'.$Wishlist['WishlistFile'].'"';
				}
			}
			$WishlistDeletePrep = $this->PDO->prepare('DELETE FROM Wishlist WHERE WishlistID = :ID');
			$WishlistDeletePrep->execute(array(':ID' => $_GET['WishlistID']));
			
			Hub::AddLog(EVENT.'Wishlist', 'Success', 'Deleted "'.$Wishlist['WishlistTitle'].' ('.$Wishlist['WishlistYear'].')" from the wishlist'.$AddLogEntry);
		}
	}
	
	function GetWishlistItemByID($WishlistID) {
		$WishItemPrep = $this->PDO->prepare('SELECT * FROM Wishlist WHERE WishlistID = :ID');
		$WishItemPrep->execute(array(':ID' => $WishlistID));
		
		if($WishItemPrep->rowCount()) {
			return $WishItemPrep->fetch();
		}
		else {
			return FALSE;
		}
	}
	
	function GetBadge() {
		$WishPrep = $this->PDO->prepare('SELECT * FROM Wishlist WHERE WishlistDownloadDate = 0 OR WishlistFile = ""');
		$WishPrep->execute();
		
		$WishlistSize = $WishPrep->rowCount();
		
		$LastActivity = Hub::GetActivity('page=Wishlist');
		$WishPrep = $this->PDO->prepare('SELECT * FROM Wishlist WHERE WishlistDownloadDate > :LastActivity');
		$WishPrep->execute(array(':LastActivity' => $LastActivity));
		
		$WishlistNewSize = $WishPrep->rowCount();
		
		if($WishlistNewSize > 0 && $WishlistSize > 0) {
			echo '<span class="badge dual rightbadge blue">'.$WishlistSize.'</span><span class="badge dual leftbadge red">'.$WishlistNewSize.'</span>';
		}
		else if($WishlistSize > 0) {
			echo '<span class="badge single blue">'.$WishlistSize.'</span>';
		}
	}
}
?>