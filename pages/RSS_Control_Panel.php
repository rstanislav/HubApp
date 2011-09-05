<script type="text/javascript">
$('#tbl-rss .editable').editable('load.php?page=RSSFeedEdit');
$('#AddRSSFeed').click(function(event) {
	event.preventDefault();
	
	RSSID = randomString();
	$('#tbl-rss tbody tr:first').before(
	    '<tr id="' + RSSID + '">' +
	     '<form name="' + RSSID + '" method="post" action="load.php?page=RSSFeedAdd" style="display:none">' +
	      '<td style="text-align:center"><input name="RSSDate" type="hidden" value="" />Now</td>' +
	      '<td><input name="RSSTitle" style="width:180px" type="text" /></td>' +
	      '<td><input name="RSSFeed" style="width:350px" type="text" /></td>' +
	      '<td style="text-align:center">' +
	       '<a onclick="javascript:ajaxSubmit(\'' + RSSID + '\');"><img src="images/icons/add.png" /></a>' +
	       '<a onclick="javascript:$(\'#' + RSSID + '\').remove();"><img src="images/icons/delete.png" /></a>' +
	      '</td>' +
	     '</form>' +
	    '</tr>');
});
</script>

<div class="head-control">
 <a id="RSSUpdate" class="button positive"><span class="inner"><span class="label" nowrap="">Update Feeds</span></span></a>
 <a id="AddRSSFeed" class="button positive"><span class="inner"><span class="label" nowrap="">Add Feed</span></span></a>
</div>

<div class="head">RSS Control Panel <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<?php
$RSSFeeds = $RSSObj->GetRSSFeeds();

if(is_array($RSSFeeds)) {
	echo '
	<table id="tbl-rss">
	 <thead>
	 <tr>
	  <th style="text-align: center">Since</th>
	  <th>Title</th>
	  <th>Feed</th>
	  <th>&nbsp;</th>
	 </tr>
	 </thead>'."\n";
	foreach($RSSFeeds AS $RSSFeed) {
		$RSSFeedDeleteLink = ($UserObj->CheckPermission($UserObj->UserGroupID, 'RSSFeedDelete')) ? '<a id="RSSFeedDelete-'.$RSSFeed['RSSID'].'" rel="'.$RSSFeed['RSSTitle'].'"><img src="images/icons/delete.png" /></a>' : '';
		
		echo '
		<tr id="RSSFeed-'.$RSSFeed['RSSID'].'">
		 <td style="width:100px; text-align:center">'.date('d.m.y H:i', $RSSFeed['RSSDate']).'</td>
		 <td class="editable" id="'.$RSSFeed['RSSID'].'-|-RSSTitle">'.$RSSFeed['RSSTitle'].'</td>
		 <td class="editable" id="'.$RSSFeed['RSSID'].'-|-RSSFeed">'.$RSSFeed['RSSFeed'].'</td>
		 <td style="width:36px;text-align: center">
		  '.$RSSFeedDeleteLink.'
		 </td>
		</tr>'."\n";
	}
	echo '</table>'."\n";
}
else {
	echo '<div class="notification">No data available</div>';
}
?>