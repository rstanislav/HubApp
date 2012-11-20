<div class="head-control">
 <a id="RSSUpdate" class="button positive"><span class="inner"><span class="label" nowrap="">Update Feeds</span></span></a>
 <a id="RSSAddFeed" class="button positive"><span class="inner"><span class="label" nowrap="">Add Feed</span></span></a>
</div>

<div class="head">RSS Control Panel</div>

<?php
$Feeds = json_decode($Hub->Request('/rss'));

if(is_object($Feeds) && is_object($Feeds->error)) {
	echo '<div class="notification information">'.$Feeds->error->message.'</div>'."\n";
}
else {
	echo '
	<table id="tbl-feeds">
	 <thead>
	 <tr>
	  <th style="width: 55px">Since</th>
	  <th>Title</th>
	  <th>Feed</th>
	  <th style="width: 34px">&nbsp;</th>
	 </tr>
	 </thead>'."\n";

	foreach($Feeds AS $Feed) {
		echo '
		<tr id="RSSFeed-'.$Feed->ID.'">
		 <td>'.date('d.m.y', $Feed->Date).'</td>
		 <td>'.$Feed->Title.'</td>
		 <td>'.$Feed->Feed.'</td>
		 <td style="text-align: right">
		  <a id="FeedDelete-'.$Feed->ID.'" rel="ajax"><img src="images/icons/delete.png" /></a>
		 </td>
		</tr>'."\n";
	}
	
	echo '</table>'."\n";
}
?>