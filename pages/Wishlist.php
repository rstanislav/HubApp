<script type="text/javascript">
$('#tbl-wishlist .editable').editable('load.php?page=WishlistEdit');
$('#AddWishlistItem').click(function(event) {
	event.preventDefault();
	
	WishlistID = randomString();
	$('#tbl-wishlist tbody tr:first').before(
	    '<tr id="' + WishlistID + '">' +
	     '<form name="' + WishlistID + '" method="post" action="load.php?page=WishlistAdd" style="display:none">' +
	      '<td><input name="WishlistTitle" style="width:250px" type="text" /></td>' +
	      '<td><input name="WishlistYear" style="width:115px" type="text" /></td>' +
	      '<td><input name="WishlistDate" type="hidden" value="1" />Now</td>' +
	      '<td style="text-align:center">' +
	       '<a onclick="javascript:ajaxSubmit(\'' + WishlistID + '\');"><img src="images/icons/add.png" /></a>' +
	       '<a onclick="javascript:$(\'#' + WishlistID + '\').remove();"><img src="images/icons/delete.png" /></a>' +
	      '</td>' +
	     '</form>' +
	    '</tr>');
});
</script>

<div class="head-control">
 <a id="AddWishlistItem" class="button positive"><span class="inner"><span class="label" nowrap="">Add Wish</span></span></a>
 <a id="WishlistRefresh-0" class="button positive"><span class="inner"><span class="label" nowrap="">Refresh Wishlist</span></span></a>
</div>
 
<div class="head">Wishlist <small style="font-size: 12px;">(<a href="#!/Help/Wishlist">?</a>)</small></div>

<?php
$Wishes = $WishlistObj->GetUnfulfilledWishlistItems();

if(is_array($Wishes)) {
	echo '
	<table id="tbl-wishlist">
	 <thead>
	 <tr>
	  <th>Title</th>
	  <th style="width:50px">Year</th>
	  <th style="width:150px">Since</th>
	  <th style="width:54px">&nbsp;</th>
	 </tr>
	 </thead>'."\n";
	foreach($Wishes AS $Wish) {
		$WishlistDeleteLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'WishlistDelete')) ? '<a id="WishlistDelete-'.$Wish['WishlistID'].'" rel="'.$Wish['WishlistTitle'].' ('.$Wish['WishlistYear'].')"><img src="images/icons/delete.png" /></a>' : '';
		
		echo '
		<tr id="Wishlist-'.$Wish['WishlistID'].'">
		 <td class="editable" id="'.$Wish['WishlistID'].'-|-WishlistTitle">'.$Wish['WishlistTitle'].'</td>
		 <td class="editable" id="'.$Wish['WishlistID'].'-|-WishlistYear">'.$Wish['WishlistYear'].'</td>
		 <td>'.date('d.m.y H:i', $Wish['WishlistDate']).'</td>
		 <td style="text-align: center">
		  '.$RSSObj->CreateSearchLink($Wish['WishlistTitle'].' '.$Wish['WishlistYear'], 'movie').'
		  <a href="http://www.youtube.com/results?search_query='.urlencode($Wish['WishlistTitle'].' '.$Wish['WishlistYear'].' trailer').'" target="_blank" title="Search for trailer on YouTube"><img src="images/icons/youtube.png" /></a>
		  '.$WishlistDeleteLink.'
		 </td>
		</tr>'."\n";
	}
	echo '</table>'."\n";
}
else {
	echo '
	<table id="tbl-wishlist">
	 <thead>
	 <tr>
	  <th>Title</th>
	  <th style="width:50px">Year</th>
	  <th style="width:150px">Since</th>
	  <th style="width:36px">&nbsp;</th>
	 </tr>
	 </thead>
	 <tr>
      <td colspan="4">
       <div class="notification information">No data available</div>
      </td>
     </tr>
    </table>'."\n";
}
?>

<br />

<div class="head">Wishlist &raquo; Granted <small style="font-size: 12px;">(<a href="#!/Help/Wishlist">?</a>)</small></div>

<?php
$Wishes = $WishlistObj->GetFulfilledWishlistItems();

if(is_array($Wishes)) {
	echo '
	<table>
	 <thead>
	 <tr>
	  <th>Title</th>
	  <th>Year</th>
	  <th>Since</th>
	  <th>File</th>
	  <th>&nbsp;</th>
	 </tr>
	 </thead>'."\n";
	foreach($Wishes AS $Wish) {
		$WishlistPlayLink   = ($UserObj->CheckPermission($UserObj->UserGroupID, 'XBMCPlay')) ? '<a id="FilePlay-'.urlencode($Wish['WishlistFile']).'"><img src="images/icons/control_play.png" title="Play '.$Wish['WishlistFile'].'" /></a>' : '';
		$WishlistDeleteLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'WishlistDelete')) ? '<a id="WishlistDelete-'.$Wish['WishlistID'].'" rel="'.$Wish['WishlistTitle'].' ('.$Wish['WishlistYear'].')"><img src="images/icons/delete.png" /></a>' : '';
		$WishListStatusImg = '';
		
		if($Wish['TorrentKey'] && !$Wish['WishlistFile'] && !$Wish['WishlistFileGone']) {
			$Torrent = $RSSObj->GetTorrentByID($Wish['TorrentKey']);
			$FileText = $Torrent['TorrentTitle'].' has been added to uTorrent';
			$WishlistPlayLink = '';
			$WishlistDeleteLink = '';
			$WishListStatusImg = '<img src="images/icons/downloaded.png" />';
		}
		else {
			$FileText = $HubObj->ConcatFilePath($Wish['WishlistFile']);
		}
		
		
		if($Wish['WishlistFileGone'] && !$Wish['WishlistFile']) {
			$FileText = 'Movie has been downloaded, but the file is missing';
			$WishlistPlayLink   = '';
			$WishlistDeleteLink = $WishlistDeleteLink;
			$WishListStatusImg  = '<img src="images/icons/file_error.png" />';
		}
		
		echo '
		<tr>
		 <td>'.$Wish['WishlistTitle'].'</td>
		 <td style="width:50px">'.$Wish['WishlistYear'].'</td>
		 <td style="width:150px">Granted on '.date('d.m.y H:i', $Wish['WishlistDownloadDate']).'</td>
		 <td>'.$FileText.'</td>
		 <td style="text-align: center;width:54px">
		  '.$WishListStatusImg.'
		  '.$WishlistPlayLink.'
		  <a href="http://www.youtube.com/results?search_query='.urlencode($Wish['WishlistTitle'].' '.$Wish['WishlistYear'].' trailer').'" target="_blank" title="Search for trailer on YouTube"><img src="images/icons/youtube.png" /></a>
		  '.$WishlistDeleteLink.'
		 </td>
		</tr>'."\n";
	}
	echo '</table>'."\n";
}
else {
	echo '<div class="notification information">No data available</div>';
}
?>