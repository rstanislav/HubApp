<div id="login"> 
 <a href="http://hubapp.net/"><img src="images/login/logo.png" /></a> 

 <div class="box">
  <?php
  $Error = (isset($_SESSION['LoginError']) && !empty($_SESSION['LoginError'])) ? $_SESSION['LoginError'] : '';
  if($Error) {
  	echo '<span style="color: red">'.$Error.'</span>'."\n";
  	
  	unset($_SESSION['LoginError']);
  }
  ?>
  <form name="hublogin" method="post" action="load.php?page=Login">
   <h2>Login</h2>
   <p><label>Username:</label><input maxlength="64" class="text" type="text" name="HubUser" /></p> 
   <p><label>Password:</label><input maxlength="64" class="text" type="password" name="HubPass" /></p>
   <p><input class="submit" type="submit" name="submit" value="Log in" /></p> 
   <div class="break"></div> 
  </form>
 </div> 
	
 <div id="footer"> 
  <!--<a href="#!/Password/">Forgot your password?</a><br/>//-->
  <a href="http://twitter.com/realhubapp/">Follow @realhubapp on Twitter</a>   
 </div> 

</div>