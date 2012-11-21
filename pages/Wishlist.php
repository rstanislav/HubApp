<div class="head-control">
 <a id="WishlistUpdateShared" class="button positive"><span class="inner"><span class="label" nowrap="">Update Shared Wishlist</span></span></a>
 <a id="WishlistAddItem" class="button positive"><span class="inner"><span class="label" nowrap="">Add Wish</span></span></a>
 <!--<a id="WishlistBookmarklet" href="javascript:(function(){_hub_wishlist=document.createElement('SCRIPT');_hub_wishlist.type='text/javascript';_hub_wishlist.src='http://5.80.30.23/js/hub.bookmarklet.js?x='+(Math.random());document.getElementsByTagName('head')[0].appendChild(_hub_wishlist);})();" class="button positive"><span class="inner"><span class="label" nowrap="">+ Wish</span></span></a>//-->
 <a id="WishlistRefresh" class="button positive"><span class="inner"><span class="label" nowrap="">Refresh Wishlist</span></span></a>
</div>

<div class="head">Wishlist</div>

<table id="tbl-wishlist">
 <thead>
  <tr>
   <th style="width:90px">Since</th>
   <th>Title</th>
   <th style="width:30px">Year</th>
   <th style="width:54px">&nbsp;</th>
  </tr>
 </thead>
 
<?php 
$Wishes = json_decode($Hub->Request('wishlist/'));

if(is_object($Wishes) && property_exists($Wishes, 'error')) {
	echo '
	<tr>
	 <td colspan="4">'.$Wishes->error->message.'</td>
	</tr>'."\n";
}
else {
	foreach($Wishes AS $Wish) {
		echo '
		<tr id="'.$Wish->ID.'">
		 <td>'.date('d.m.y H:i', $Wish->Date).'</td>
		 <td>'.$Wish->Title.'</td>
		 <td>'.$Wish->Year.'</td>
		 <td style="text-align: center">
		  <img src="images/icons/search.png" />
		  <img src="images/icons/youtube.png" />
		  <a id="WishlistDelete-'.$Wish->ID.'" rel="ajax"><img src="images/icons/delete.png" /></a>
		 </td>
		</tr>'."\n";
	}
}
?>
</table>

<br />

<div class="head">Wishlist &raquo; Granted</div>

<?php
$Wishes = json_decode($Hub->Request('wishlist/granted/'));

if(is_object($Wishes) && is_object($Wishes->error)) {
	echo '<div class="notification information">'.$Wishes->error->message.'</div>'."\n";
}
else {
	echo '
	<table>
	 <thead>
	 <tr>
	  <th style="width: 90px">Since</th>
	  <th>Title</th>
	  <th style="width: 30px">Year</th>
	  <th>File</th>
	  <th style="width: 54px">&nbsp;</th>
	 </tr>
	 </thead>'."\n";
	foreach($Wishes AS $Wish) {
		if(is_file($Wish->File)) {
			$ActionLink = '<a id="FilePlay-'.$Wish->File.'" rel="ajax"><img src="images/icons/control_play.png" /></a>';
		}
		else {
			$ActionLink = '<img src="images/icons/error.png" />';
		}
		
		echo '
		<tr>
		 <td>'.date('d.m.y H:i', $Wish->DownloadDate).'</td>
		 <td>'.$Wish->Title.'</td>
		 <td>'.$Wish->Year.'</td>
		 <td>'.ConcatFilePath($Wish->File).'</td>
		 <td style="text-align: center;">
		  '.$ActionLink.'
		  <img src="images/icons/youtube.png" />
		  <a id="WishlistDelete-'.$Wish->ID.'" rel="ajax"><img src="images/icons/delete.png" /></a>
		 </td>
		</tr>'."\n";
	}
	echo '</table>'."\n";
}
?>