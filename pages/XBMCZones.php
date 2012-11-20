<div class="head-control">
 <a id="ZoneAdd" class="button positive"><span class="inner"><span class="label" nowrap="">Add Zone</span></span></a>
</div>

<div class="head">Zones</div>

<?php
$Zones = json_decode($Hub->Request('xbmc/zones/'));

if(is_object($Zones) && is_object($Zones->error)) {
	echo '<div class="notification warning">'.$Zones->error->message.'</div>'."\n";
}
else {
	echo '
	<table id="tbl-zones">
	 <thead>
	 <tr>
	  <th style="width:60px">Since</th>
	  <th>Name</th>
	  <th style="width:125px">Host</th>
	  <th style="width:45px">Port</th>
	  <th style="width:75px">Username</th>
	  <th style="width:75px">Password</th>
	  <th style="width:34px">&nbsp;</th>
	 </tr>
	 </thead>'."\n";
	 
	foreach($Zones AS $Zone) {
		echo '
		 <tr>
		  <td>'.date('d.m.y', $Zone->Date).'</td>
		  <td>'.$Zone->Name.'</td>
		  <td>'.$Zone->XBMCHost.'</td>
		  <td>'.$Zone->XBMCPort.'</td>
		  <td>'.$Zone->XBMCUser.'</td>
		  <td>hidden</td>
		  <td style="text-align:right">
		   <a id="ZoneDelete-'.$Zone->ID.'" rel="ajax"><img src="images/icons/delete.png" /></a>
		  </td>
		 </tr>'."\n";
	}
}
?>