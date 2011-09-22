<script type="text/javascript">
$('#ProfileSave').click(function() {
	$('form[id=' + $(this).parents('form:eq(0)').attr('id') + ']').ajaxSubmit({
		beforeSubmit: function() {
			$('#ProfileSave').contents().find('.label').text('Saving ...');
		},
		success: function(data) {
			if(data != '') {
				$('#ProfileSave').contents().find('.label').text('Error!');
				
				jAlert(data, 'Something went wrong...');
			}
			else {
				$('#ProfileSave').contents().find('.label').text('Saved!');
			}
		},
		error: function() {
			$('#ProfileSave').contents().find('.label').text('Error!');
		}
	});
});
</script>

<div class="head">Profile <small style="font-size: 12px;">(<a href="#!/Help/Main">?</a>)</small></div>

<form id="ProfileForm" name="UserProfile" method="post" action="load.php?page=ProfileSave">
<table> 
 <tbody> 
  <tr> 
   <th scope="row" class="column1">
    E-Mail<br />
    <small>Your current e-mail address</small>
   </th> 
   <td><input name="UserEMail" type="text" value="<?php echo $UserObj->UserEMail; ?>" /></td> 
  </tr>	
  <tr> 
   <th scope="row" class="column1">
    Current Password<br />
    <small>Your current password</small>
   </th> 
   <td><input name="UserCurrentPass" type="password" /></td> 
  </tr>	
  <tr> 
   <th scope="row" class="column1">
    New Password<br />
    <small>Your desired password</small>
   </th> 
   <td><input name="UserNewPass1" type="password" /></td> 
  </tr>	
  <tr> 
   <th scope="row" class="column1">
    Repeat New Password<br />
    <small>Your desired password again</small>
   </th> 
   <td><input name="UserNewPass2" type="password" /></td> 
  </tr>
  <tr> 
   <td colspan="2" style="text-align: right"><a id="ProfileSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a></td> 
  </tr>
 </tbody> 
</table>
</form>