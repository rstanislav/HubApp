<script type="text/javascript">
$('#LoginPage').click(function() {
	$.ajax({
		method: 'get',
		url:    'pages/Login.php',
		success: function(html) {
			$('#login').html(html);
		}
	});
});
</script>

<div id="login"> 
 <a href="http://hubapp.net/"><img src="images/logo.png" /></a> 

 <div class="box"> 
  <h2>Password recovery</h2>
  <p>Use this form to have a password recovery link sent to you by mail.</p> 
 </div>
 <div class="box">
  <form method="post" action="load.php?page=ForgotPassword">
   <p><label>Enter your username...</label><input maxlength="64" class="text" type="text" name="HubUser" /></p> 
   <p><label><strong>or</strong> your e-mail address</label><input maxlength="64" class="text" type="text" name="HubEMail" /></p> 
   <p><input class="submit" type="submit" name="submit" value="Reset" /></p> 
   <div class="break"></div> 
  </form>
 </div> 

 <div id="footer"> 
  <a id="LoginPage" href="#!/">Go back</a> 
 </div>
			
</div>