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
<div id="form-wrap">
 <dl>
 
  <dt>E-mail</dt>
  <dd>
   <div class="field">
    <label>
     <input name="UserEMail" type="text" value="<?php echo $UserObj->UserEMail; ?>" />
     <span>Your current e-mail address</span>
    </label>
   </div>
  </dd>
  
  <div style="float: right">
   <a id="ProfileSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a>
  </div>
            
  <dd class="clear"></dd>
 
  <dt>Password Reset</dt>
  <dd>
   <div class="field">
    <label>
     <input name="UserCurrentPass" type="password" />
     <span>Your current password</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <input name="UserNewPass1" type="password" />
     <span>New password</span>
    </label>
   </div>
   
   <div class="field">
    <label>
     <input name="UserNewPass2" type="password" />
     <span>New password (repeat)</span>
    </label>
   </div>
  </dd>
  
  <div style="float: right">
   <a id="ProfileSave" class="button positive"><span class="inner"><span class="label" nowrap="">Save</span></span></a>
  </div>
            
  <dd class="clear"></dd>
 </dl>
 <div class="clear"></div>
</div>
</form>