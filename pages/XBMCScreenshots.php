<div class="head">XBMC Screenshots <small style="font-size: 12px;">(<a href="#!/Help/Screenshots">?</a>)</small></div>

<?php
$Screenshots = glob('screenshots/*.png');
rsort($Screenshots);

if(sizeof($Screenshots)) {
	$i = 1;
	echo '
	<table class="nostyle">
	 <tr>'."\n";
	foreach($Screenshots AS $Screenshot) {
		$ScreenshotInfo  = pathinfo($Screenshot);
		$ScreenshotThumb = './screenshots/thumbnails/'.$ScreenshotInfo['filename'].'_thumb.'.$ScreenshotInfo['extension'];
		
		if(!is_file($ScreenshotThumb)) {
			$SeriesObj->MakeThumbnail($Screenshot, $ScreenshotThumb, 480, 270);
		}
		
		echo '
		<td style="text-align: center">
		 <a href="'.$Screenshot.'"><img class="poster" src="'.$ScreenshotThumb.'" /></a>
		</td>'."\n";
		
		if($i++ % 2 == 0) {
			echo '
			</tr>
			<tr>'."\n";
		}
	}
	echo '
	</tr>
	</table>';
}
else {
	echo '<div class="notification">No screenshots available</div>';
}
?>