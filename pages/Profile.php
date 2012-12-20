<div class="head">Profile</div>

<form id="ProfileForm" name="UserProfile" method="post" action="load.php?page=ProfileSave">
<div id="form-wrap">
 <dl>
 
  <dt>E-mail</dt>
  <dd>
   <div class="field">
	<label>
	 <input name="UserEMail" type="text" value="" />
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