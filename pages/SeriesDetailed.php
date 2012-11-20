<?php
$Series = json_decode($Hub->Request('/series/'.$_GET['ID']));

if(is_object($Series) && is_object($Series->error)) {
	echo '<div class="notification information">'.$Series->error->message.'</div>'."\n";
}
else {
	foreach($Series AS $Serie) {
		$TitleAlt = (!is_null($Serie->TitleAlt)) ? '/'.$Serie->TitleAlt : '';
		
		$SerieRefreshLink = '<a id="SerieRefresh-'.$Serie->ID.'" class="button positive"><span class="inner"><span class="label" nowrap="">Refresh</span></span></a>';
		$SerieSpellingLink = '<a id="SerieSpelling-'.$Serie->ID.'" rel="'.$Serie->Title.'" class="button positive"><span class="inner"><span class="label" nowrap="">+Spelling</span></span></a>';
		$SerieDeleteLink = '<a id="SerieDelete-'.$Serie->ID.'" rel="'.$Serie->Title.'" class="button negative"><span class="inner"><span class="label" nowrap="">Delete</span></span></a>';
		
		$PosterSmall = property_exists($Serie, 'PosterSmall') ? '<img class="poster" src="'.$Serie->PosterSmall.'" />' : '';
		
		echo '
		<div class="head-control">
		 '.$SerieRefreshLink.'
		 '.$SerieSpellingLink.'
		 '.$SerieDeleteLink.'
		</div>
	
		<div class="head">'.$Serie->Title.$TitleAlt.' ('.date('Y', $Serie->FirstAired).')</div>
	
		<table width="300" class="nostyle">
		 <tr>
		  <td rowspan="9" width="100">
		   '.$PosterSmall.'
		  </td>
		  <td><strong>First Aired:</strong></td>
		  <td>'.date('F jS, Y', $Serie->FirstAired).'</td>
		 </tr>
		 <tr>
		  <td><strong>Genre:</strong></td>
		  <td>'.$Serie->Genre.'</td>
		 </tr>
		 <tr>
	 	  <td><strong>Schedule:</strong></td>
		  <td>'.$Serie->AirDay.' '.$Serie->AirTime.'</td>
		 </tr>
		 <tr>
		  <td><strong>Runtime:</strong></td>
		  <td>'.$Serie->Runtime.'</td>
		 </tr>
		 <tr>
		  <td><strong>Rating:</strong></td>
		  <td>'.$Serie->Rating.' ('.$Serie->RatingCount.' votes)</td>
		 </tr>
		 <tr>
		  <td width="100"><strong>Content Rating:</strong></td>
		  <td>'.$Serie->ContentRating.'</td>
		 </tr>
		 <tr>
		  <td><strong>Network:</strong></td>
		  <td>'.$Serie->Network.'</td>
		 </tr>
		 <tr>
		  <td><strong>Status:</strong></td>
		  <td>'.$Serie->Status.'</td>
		 </tr>
		 <tr>
	 	 <td style="vertical-align: top"><strong>External info:</strong></td>
	 	 <td style="vertical-align: top">
	 	  <a href="http://imdb.com/title/'.$Serie->IMDBID.'/" target="_blank">IMDB</a> |
		   <a href="http://thetvdb.com/?tab=series&id='.$Serie->TheTVDBID.'" target="_blank">TheTVDB</a>
		  </td>
		 </tr>
		 <tr>
		  <td colspan="3">
		   '.nl2br($Serie->Plot).'
		  </td>
		 </tr>
		</table>
	
		<br />'."\n";
	}
	
	$Episodes = json_decode($Hub->Request('/series/'.$_GET['ID'].'/episodes/'));
	if(is_object($Episodes) && is_object($Episodes->error)) {
		echo '
		<tr>
		 <td colspan="5">'.$Episodes->error->message.'</td>
		</tr>'."\n";
	}
	else {
		echo '
		<div class="head">Episodes</div>
		
		<table>
		 <thead>
		  <tr>
		   <th style="width: 50px">Air Date</th>
		   <th style="width: 300px">Title</th>
		   <th style="width: 50px">&nbsp;</th>
		   <th>File/Search</th>
		   <th style="width: 35px">&nbsp;</th>
		  </tr>
		 </thead>'."\n";
		
		foreach($Episodes AS $Episode) {
			$SeasonNo = isset($SeasonNo) ? $SeasonNo : $Episode->Season;
			if($Episode->Season == $SeasonNo) {
				$Season = ($SeasonNo >= 1) ? 'Season '.$SeasonNo-- : 'Specials';
				
				echo '
				<tr class="heading">
				 <th colspan="5" style="color: white; text-align: center">'.$Season.'</th>
				</tr>'."\n";
			}
			
			$DeleteLink = '';
			switch($Episode->Status) {
				case 'Available':
					$ActionLink = '<a id="FilePlay-'.$Episode->File.'" rel="ajax"><img src="images/icons/control_play.png" /></a>';
					$DeleteLink = '<a id="FileDelete-'.$Episode->File.'" rel="ajax"><img src="images/icons/delete.png" /></a>';
				break;
				
				case 'Downloaded':
					$ActionLink = '<img src="images/icons/downloaded.png" />';
				break;
				
				case 'Torrent':
					$ActionLink = '<img src="images/icons/download.png" />';
				break;
				
				case 'Torrents':
					$ActionLink = '<img src="images/icons/download_multiple.png" />';
				break;
				
				default:
					$ActionLink = '<img src="images/icons/search.png" />';
				break;
			}
			
			echo '
			<tr>
			 <td>'.date('d.m.y', $Episode->AirDate).'</td>
			 <td>'.$Episode->Title.'</td>
			 <td>S'.sprintf('%02s', $Episode->Season).'E'.sprintf('%02s', $Episode->Episode).'</td>
			 <td>'.ConcatFilePath($Episode->File).'</td>
			 <td>
			  '.$ActionLink.'
			  '.$DeleteLink.'
			 </td>
			</tr>'."\n";
		}
	}
}

echo '
</table>'."\n";
?>