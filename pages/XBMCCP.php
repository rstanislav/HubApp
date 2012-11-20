<div class="head-control">
 <a id="XBMCLibraryUpdate" class="button positive"><span class="inner"><span class="label" nowrap="">Update Library</span></span></a>
 <a id="XBMCLibraryClean" class="button positive"><span class="inner"><span class="label" nowrap="">Clean Library</span></span></a>
</div>

<div class="head">XBMC Control Panel</div>

<?php
$Player = json_decode($Hub->Request('/xbmc/players/active/'));

if(is_object($Player) && property_exists($Player, 'error')) {
	echo '<div class="notification information">'.$Player->error->message.'</div>'."\n";
}
else {
	$PlayPauseText = ($Player->status == 'Playing') ? 'Pause' : 'Play';
	echo '
	<div class="head-control">
	 <a id="XBMCPlayerTogglePlayback" class="button positive"><span class="inner"><span class="label" nowrap="">'.$PlayPauseText.'</span></span></a>
	 <a id="XBMCPlayerStop" class="button negative"><span class="inner"><span class="label" nowrap="">Stop</span></span></a>
	</div>'."\n";
	
	if($Player->item->type == 'episode') {
		echo '
		<div class="head">'.$Player->status.': '.$Player->item->showtitle.' &ndash; Season '.$Player->item->season.' Episode '.$Player->item->episode.'</div>
		
		<table width="300" class="nostyle">
		 <tr>
		  <td rowspan="9" style="text-align: center; width: 100px">
		   <img class="poster" src="'.$Player->item->postersmall.'" />
		  </td>
		 </tr>
		 <tr>
		  <td style="width: 100px"><strong>Episode:</strong></td>
		  <td>'.$Player->item->label.'</td>
		 </tr>
		 <tr>
	 	 <td><strong>Time:</strong></td>
	 	 <td>'.$Player->time->formatted.' of '.$Player->totaltime->formatted.'</td>
		 </tr>
		 <tr>
		  <td><strong>Aired:</strong></td>
		  <td>'.$Player->item->firstaired.'</td>
		 </tr>
		 <tr>
		  <td><strong>Audio:</strong></td>
		  <td>
	 	   '.$Player->currentaudiostream->channels.' channels /
		   '.$Player->currentaudiostream->codec.' ('.$Player->currentaudiostream->name.')
		  </td>
		 </tr>
		 <tr>
		  <td><strong>Rating:</strong></td>
		  <td>'.number_format($Player->item->rating, 2).'</td>
		 </tr>
		 <tr>
		  <td><strong>MPAA:</strong></td>
		  <td>'.$Player->item->mpaa.'</td>
		 </tr>
		 <tr>
		  <td><strong>File:</strong></td>
		  <td>'.ConcatFilePath($Player->item->file).'</td>
		 </tr>
		</table>
		<br />
			
		<div class="head">Plot</div>
		'.nl2br($Player->item->plot)."\n";
	}
	else if($Player->item->type == 'movie') {
		echo '
		<div class="head">'.$Player->status.': '.$Player->item->label.$Player->item->year.' '.$Player->item->imdbnumber.'</div>
		
		<table width="300" class="nostyle">
		 <tr>
		  <td rowspan="11" style="text-align: center; width: 100px">
		   <img class="poster" src="'.$Player->item->poster.'" />
		  </td>
		 </tr>
		 <tr>
		  <td style="width: 60px"><strong>Tagline:</strong></td>
		  <td>'.$Player->item->tagline.'</td>
		 </tr>
		 <tr>
		  <td><strong>Time:</strong></td>
		  <td>'.$Player->time->formatted.' of '.$Player->totaltime->formatted.'</td>
		 </tr>
		 <tr>
		  <td><strong>Audio:</strong></td>
		  <td>
		   '.$Player->currentaudiostream->channels.' channels /
		   '.$Player->currentaudiostream->codec.' ('.$Player->currentaudiostream->name.')
		  </td>
		 </tr>
		 <tr>
		  <td><strong>Rating:</strong></td>
		  <td>'.number_format($Player->item->rating, 2).' based on '.$Player->item->votes.' votes</td>
		 </tr>
		 <tr>
		  <td><strong>Country:</strong></td>
		  <td>'.$Player->item->country.'</td>
		 </tr>
		 <tr>
		  <td><strong>Studio:</strong></td>
		  <td>'.$Player->item->studio.'</td>
		 </tr>
		 <tr>
		  <td><strong>MPAA:</strong></td>
		  <td>'.$Player->item->mpaa.'</td>
		 </tr>
		 <tr>
		  <td><strong>Subtitle:</strong></td>
		  <td>'.$Player->currentsubtitle.'</td>
		 </tr>
		 <tr>
		  <td><strong>File:</strong></td>
		  <td>'.$Player->item->file.'</td>
		 </tr>
		</table>
		<br />
			
		<div class="head">Plot</div>
		'.$Player->item->plot."\n";
	}
	else {
		echo '<div class="notification information">Unable to decipher what is playing at the moment</div>';
	}
	
	if(sizeof($Player->item->cast)) {
		echo '<br /><br />
			
		<div class="head">Cast</div>
		<table style="width:520px">
		 <thead>
		 <tr>
		  <th style="width:250px; text-align:right">Actor</th>
		  <th style="width:20px">&nbsp;</th>
		  <th style="width:250px">Role</th>
		 </tr>
		 </thead>'."\n";
		
		$SupportingCast = '';
		foreach($Player->item->cast AS $Cast) {
			if(!empty($Cast->name) && !empty($Cast->role)) {
				echo '
				<tr>
				 <td style="text-align: right">'.$Cast->name.'</td>
				 <td style="text-align: center">as</td>
				 <td>'.$Cast->role.'</td>
				</tr>'."\n";
			}
			else {
				$SupportingCast .= $Cast->name.', ';
			}
		}
		
		if(strlen($SupportingCast)) {
			echo '
			<tr>
			 <td colspan="3" style="text-align: center; font-weight: bold">Supporting actors</td>
			</tr>
			<tr>
			 <td colspan="3" style="text-align: center">'.trim($SupportingCast, ', ').'</td>
			</tr>'."\n";
		}
		
		echo '</table>'."\n";
	}
}
?>