<?php
$RSSFeed = $RSSObj->GetRSSFeed($_GET['Feed']);
?>
<div class="head">
 <?php echo '<a href="#!/RSS/'.$RSSFeed['RSSTitle'].'">'.$RSSFeed['RSSTitle'].' RSS</a> <small style="font-size: 12px;">(<a href="#!/Help/TLRSS">?</a>)</small>'."\n"; ?>
</div>

<?php
$Categories = $RSSObj->GetCategories($RSSFeed['RSSID']);

if(is_array($Categories)) {
	$i = 1;
	$CatSize = sizeof($Categories);
	$CutAt = 6;
	echo '
	<form id="TorrentViewForm" name="TorrentView" method="post" action="load.php?page=RSSCategories">
	<table>
	 <tr>'."\n";
	 
	foreach($Categories AS $Category) {
		if($_GET['Category'] != 'undefined') {
			$Checked = ($_GET['Category'] == $Category['TorrentCategory']) ? ' checked="checked"' : '';
		}
		else if(filter_has_var(INPUT_COOKIE, 'TorrentCategories')) {
			$Checked = (in_array($Category['TorrentCategory'], explode(',', $_COOKIE['TorrentCategories']))) ? ' checked="checked"' : '';
		}
		else {
			$Checked = '';
		}
		
		echo '
		<td>
		 <input type="checkbox" name="TorrentCategories[]" value="'.$Category['TorrentCategory'].'"'.$Checked.' /> 
		 <a href="#!/RSS/'.$RSSFeed['RSSTitle'].'/'.urlencode($Category['TorrentCategory']).'">'.$Category['TorrentCategory'].'</a>
		</td>'."\n";
		
		$ShowButton = '<a id="TorrentViewButton" class="button positive"><span class="inner"><span class="label" style="min-width:50px;" nowrap="">Save</span></span></a>';
		--$CatSize;
		if(!$CatSize && ($i % $CutAt == 0)) {
			echo '
			<tr>
			 <td style="text-align: right" colspan="'.$CutAt.'">
			  '.$ShowButton.'
			 </td>
			</tr>'."\n";
		}
		else if(!$CatSize) {
			echo '
			<td style="text-align: right" colspan="'.($CutAt - ($i % $CutAt)).'">
			 '.$ShowButton.'
			</td>'."\n";
		}
		
		if($i++ % $CutAt == 0) {
			echo '
			</tr>
			<tr>'."\n";
		}
	}
	echo '
	</table>
	</form>'."\n";
}
?>
<br /><br />
<?php
$Category = (filter_has_var(INPUT_GET, 'Category')) ? $_GET['Category'] : '';

if($Category == 'undefined') {
	$Category = (filter_has_var(INPUT_COOKIE, 'TorrentCategories')) ? explode(',', $_COOKIE['TorrentCategories']) : '';
}
$Torrents = $RSSObj->GetTorrents($Category, $RSSFeed['RSSID']);

if(is_array($Torrents)) {
	echo '
	<div id="TorrentView">
	<table width="100%">
	 <thead>
	 <tr>
	  <th>Title</th>
	  <th>Category</th>
	  <th style="width: 85px">Added</th>
	  <th style="width: 85px">Published</th>
	  <th style="width: 54px">&nbsp;</th>
	 </tr>
	 </thead>'."\n";
	foreach($Torrents AS $Torrent) {
		$ParsedRelease = $RSSObj->ParseRelease($Torrent['TorrentTitle']);
		
		switch($ParsedRelease['Type']) {
			case 'TV':
				$IconState = ($SeriesObj->SerieExists($ParsedRelease['Title'])) ? 'gray' : 'add';
				$FavButton = '<a href="#!/Search/'.urlencode($ParsedRelease['Title']).'"><img src="images/icons/heart_'.$IconState.'.png" /></a>';
			break;
			
			case 'Movie':
				$FavButton = '<a href="http://www.imdb.com/search/title?release_date='.$ParsedRelease['Year'].','.$ParsedRelease['Year'].'&title='.urlencode($ParsedRelease['Title']).'" target="_blank"><img src="images/icons/imdb.png" /></a> ';
				$FavButton .= '<a href="http://www.youtube.com/results?search_query='.urlencode($ParsedRelease['Title'].' '.$ParsedRelease['Year'].' trailer').'&aq=f" target="_blank" title="Search for trailer on YouTube"><img src="images/icons/youtube.png" /></a>';
			break;
			
			default;
				$FavButton = '';
			break;
		}
		
		$DownloadImg = ($RSSObj->TorrentIsDownloaded($Torrent['TorrentID'])) ? 'downloaded' : 'download';
		
		$NewTorrent = ($Torrent['TorrentDate'] > $LastActivity) ? '<img src="images/icons/new.png" />' : '';
		
		echo '
		<tr>
		 <td>'.$NewTorrent.' '.$Torrent['TorrentTitle'].'</td>
		 <td>
		  <a href="#!/TLRSS/'.urlencode($Torrent['TorrentCategory']).'">'.$Torrent['TorrentCategory'].'</a>
		 </td>
		 <td>'.date('d.m.y H:i', $Torrent['TorrentDate']).'</td>
		 <td>'.date('d.m.y H:i', $Torrent['TorrentPubDate']).'</td>
		 <td style="text-align: right">
		  '.$FavButton.'
		  <a id="TorrentDownload-'.$Torrent['TorrentID'].'"><img src="images/icons/'.$DownloadImg.'.png" /></a>
		 </td>
		</tr>'."\n";
	}
	echo '
	</table>
	</div>'."\n";
}
?>