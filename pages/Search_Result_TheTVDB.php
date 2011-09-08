<script type="text/javascript">
$(document).ready(function() {
	var ShiftDown = false;
	$(document).keydown(function(event) {
		if(event.shiftKey) {
			$('a[id|="SerieAdd"]').each(function() {
				if(!$(this).hasClass('disabled')) {
					$(this).contents().find('.label').text('Add & Download');
				}
			});
					
			ShiftDown = true;
		}
	});

	$(document).keyup(function(event) {
		if(!event.shiftKey) {
			$('a[id|="SerieAdd"]').each(function() {
				if(!$(this).hasClass('disabled')) {
					if($(this).contents().find('.label').text() != 'Adding ...') {
						$(this).contents().find('.label').text('Add');
					}
				}
			});
		
			ShiftDown = false;
		}
	});

	$('a[id|="SerieAdd"]').click(function(event) {
		if(ShiftDown) {
			AjaxButton(this, 'WithEpisodes');
		}
		else {
			AjaxButton(this);
		}
	});
	
	$('a[id|="ShowPlot"]').click(function(event) {
		Action = $(this).attr('id').split('-');
		ID = Action[1];
		Action = Action[0];
		
		$('#' + Action + '-' + ID).remove();
		$('#FullPlot-' + ID).toggle();
	});
});
</script>
<?php
$SeriesObj->ConnectTheTVDB();
$Series = $SeriesObj->SearchTitle($Search);

if(is_object($Series)) {
	echo '
	<table>
	 <tr>
	  <th>Title</th>
	  <th>Plot</th>
	  <th style="text-align: center">First Aired</th>
	  <th>&nbsp;</th>
	 </tr>'."\n";
	
	function ShortText($Str, $Limit, $Reverse = FALSE) {
		if($Reverse) {
			$Str = substr($Str, $Limit, strlen($Str));
		}
		else {
			$Str = substr($Str, 0, $Limit);
		}
		
		return $Str;
	}
	
	foreach($Series AS $Serie) {
		$FirstAired      = (!empty($Serie->FirstAired)) ? date('d.m.y', strtotime($Serie->FirstAired)) : 'NA';
		$Serie->Overview = (strlen($Serie->Overview))   ? $Serie->Overview                             : 'NA';
		$ButtonState     = ($SeriesObj->SerieExists($Serie->SeriesName)) ? 'disabled' : 'positive';
		
		if(strlen($Serie->Overview) > 90) {
			$ShortPlot = ShortText($Serie->Overview, 90);
			$DetailedPlot = ShortText($Serie->Overview, 90, TRUE);
			
			$Plot = $ShortPlot.' <a id="ShowPlot-'.$Serie->id.'">… more</a><span id="FullPlot-'.$Serie->id.'" style="display: none;">'.$DetailedPlot.'</span>';
		}
		else {
			$Plot = $Serie->Overview;
		}
		
		echo '
		<tr>
		 <td style="width: 150px; vertical-align: top">
		  <a href="http://thetvdb.com/?tab=series&id='.$Serie->id.'" target="_blank">'.$Serie->SeriesName.'</a>
		 </td>
		 <td>'.$Plot.'</td>
		 <td style="width: 150px; text-align: center">'.$FirstAired.'</td>
		 <td style="width: 120px; text-align: right; vertical-align: top">
		  <a id="SerieAdd-'.$Serie->id.'" class="button '.$ButtonState.'"><span class="inner"><span class="label" style="min-width:50px;" nowrap="">Add</span></span></a>
		 </td>
		</tr>'."\n";
	}
	echo '
	</table>'."\n";
}
else {
	echo '<div class="notification">No results</div>';
}
?>