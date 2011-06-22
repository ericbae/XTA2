<?php
$login = array(
	'name'	=> 'login',
	'id'	=> 'login',
	'value' => set_value('login'),
	'maxlength'	=> 80,
	'size'	=> 30,
);
if ($login_by_username AND $login_by_email) {
	$login_label = 'Email or login';
} else if ($login_by_username) {
	$login_label = 'Login';
} else {
	$login_label = 'Email';
}
$password = array(
	'name'	=> 'password',
	'id'	=> 'password',
	'size'	=> 30,
);
$remember = array(
	'name'	=> 'remember',
	'id'	=> 'remember',
	'value'	=> 1,
	'checked'	=> set_value('remember'),
	'style' => 'margin:0;padding:0',
);
$captcha = array(
	'name'	=> 'captcha',
	'id'	=> 'captcha',
	'maxlength'	=> 8,
);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml" >
	<head>
		<title>XTA2 - CI Tank Auth Authentication with 3rd party plugins.</title>
		<!-- google friend connect -->
		<script type="text/javascript" src="http://www.google.com/jsapi"></script>
		<script type="text/javascript">google.load('friendconnect', '0.8');</script>
		<script type="text/javascript">
			google.friendconnect.container.setParentUrl('/' /* location of rpc_relay.html and canvas.html */);
			google.friendconnect.container.initOpenSocialApi({
			  site: '<?php echo $this->config->item('google_app_id'); ?>',
			  onload: function(securityToken) { initAllData(); }
			});
		
			// main initialization function for google friend connect
			function initAllData() 
			{
				var req = opensocial.newDataRequest();
			  	req.add(req.newFetchPersonRequest("OWNER"), "owner_data");
			  	req.add(req.newFetchPersonRequest("VIEWER"), "viewer_data");
			  	var idspec = new opensocial.IdSpec({
			      	'userId' : 'OWNER',
			      	'groupId' : 'FRIENDS'
			  	});
			  	req.add(req.newFetchPeopleRequest(idspec), 'site_friends');
			  	req.send(onData);
			};
			
			// main function for handling user data
			function onData(data) 
			{
			  	// getting the site data, we don't need this for now
			  	//if (!data.get("owner_data").hadError()) 
			  	//{
			    //	var owner_data = data.get("owner_data").getData();
			    //	document.getElementById("site-name").innerHTML = owner_data.getDisplayName();
			    	//alert('user is logging in');
			  	//}
			
			  	var viewer_info = document.getElementById("viewer-info");
			  	if (data.get("viewer_data").hadError()) 
			  	{
			    	google.friendconnect.renderSignInButton({ 'id': 'gfc-button', 'text':'Click here to join', 'style': 'long' });
			    	document.getElementById('gfc-button').style.display = 'block';
			    	viewer_info.innerHTML = '';
			    	//alert('there has been an error here');
			  	} 
			  	else 
			  	{
			    	document.getElementById('gfc-button').style.display = 'none';
			    	var viewer = data.get("viewer_data").getData();
			    	//viewer_info.innerHTML = "Hello, " + viewer.getDisplayName() + " " +
			        //						"<a href='#' onclick='google.friendconnect.requestSettings()'>Settings</a> | " +
					//				        "<a href='#' onclick='google.friendconnect.requestInvite()'>Invite</a> | " +
					//				        "<a href='#' onclick='google.friendconnect.requestSignOut()'>Sign out</a>";
					//alert('user has been loaded');
					//alert(viewer.getDisplayName());
					//alert(viewer.getId());
					
					// let's redirect the user to our login_google action in auth_other controller
					window.location = "<?php echo site_url('auth_other/gfc_signin/'); ?>" + "/" + viewer.getId();
			  	}
			
				// for displaying friends, but we don't need this for now
			  	//if (!data.get("site_friends").hadError()) 
			  	//{
			    //	var site_friends = data.get("site_friends").getData();
			    //	var list = document.getElementById("friends-list");
			    //	list.innerHTML = "";
			    //	site_friends.each(function(friend) 
			    //	{
			    // 		list.innerHTML += "<li>" + friend.getDisplayName() + "</li>";
			    //	});
			  	//}
			};
		</script>
	</head>
	
	<body>
		<table><tr>
			<td><h4><?php echo anchor('/', 'XTA2 DEMO'); ?></h4></td>
			<td><h4><?php echo anchor('welcome/about', 'About + Discuss'); ?></h4></td>
			<td><h4><?php echo anchor('welcome/readme', 'Installation guide'); ?></h4></td>
		</tr></table>
			
		<h1>XTA2 Demo - Extended Tank_Auth with Facebook, Twitter and OpenID (Google, Yahoo etc)</h1>
		<?php echo form_open($this->uri->uri_string()); ?>
		<table>
			<tr>
				<td><?php echo form_label($login_label, $login['id']); ?></td>
				<td><?php echo form_input($login); ?></td>
				<td style="color: red;"><?php echo form_error($login['name']); ?><?php echo isset($errors[$login['name']])?$errors[$login['name']]:''; ?></td>
			</tr>
			<tr>
				<td><?php echo form_label('Password', $password['id']); ?></td>
				<td><?php echo form_password($password); ?></td>
				<td style="color: red;"><?php echo form_error($password['name']); ?><?php echo isset($errors[$password['name']])?$errors[$password['name']]:''; ?></td>
			</tr>
		
			<?php if ($show_captcha) {
				if ($use_recaptcha) { ?>
			<tr>
				<td colspan="2">
					<div id="recaptcha_image"></div>
				</td>
				<td>
					<a href="javascript:Recaptcha.reload()">Get another CAPTCHA</a>
					<div class="recaptcha_only_if_image"><a href="javascript:Recaptcha.switch_type('audio')">Get an audio CAPTCHA</a></div>
					<div class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type('image')">Get an image CAPTCHA</a></div>
				</td>
			</tr>
			<tr>
				<td>
					<div class="recaptcha_only_if_image">Enter the words above</div>
					<div class="recaptcha_only_if_audio">Enter the numbers you hear</div>
				</td>
				<td><input type="text" id="recaptcha_response_field" name="recaptcha_response_field" /></td>
				<td style="color: red;"><?php echo form_error('recaptcha_response_field'); ?></td>
				<?php echo $recaptcha_html; ?>
			</tr>
			<?php } else { ?>
			<tr>
				<td colspan="3">
					<p>Enter the code exactly as it appears:</p>
					<?php echo $captcha_html; ?>
				</td>
			</tr>
			<tr>
				<td><?php echo form_label('Confirmation Code', $captcha['id']); ?></td>
				<td><?php echo form_input($captcha); ?></td>
				<td style="color: red;"><?php echo form_error($captcha['name']); ?></td>
			</tr>
			<?php }
			} ?>
		
			<tr>
				<td colspan="3">
					<?php echo form_checkbox($remember); ?>
					<?php echo form_label('Remember me', $remember['id']); ?>
					<?php echo anchor('/auth/forgot_password/', 'Forgot password'); ?>
					<?php if ($this->config->item('allow_registration', 'tank_auth')) echo anchor('/auth/register/', 'Register'); ?>
				</td>
			</tr>
			
		</table>
		<?php echo form_submit('submit', 'Let me in'); ?>
		<?php echo form_close(); ?>
		
		<br/>
		<table>
			<tr>
				<td>
					<fb:login-button v="2" perms="" length="long" onlogin='window.location="https://graph.facebook.com/oauth/authorize?client_id=<?php echo $this->config->item('facebook_app_id'); ?>&redirect_uri=<?php echo site_url('auth_other/fb_signin'); ?>&amp;r="+window.location.href;'></fb:login-button>
				</td>
				<td>
					<a class="twitter" href="<?php echo site_url('auth_other/twitter_signin'); ?>">
						<img style="margin-top:5px;" src="<?php echo base_url(); ?>images/twitter_login_button.gif" alt="twitter login" border="0"/>
					</a>
				</td>
				<td>
					<div id="gfc-button"></div>
					(Google Friend Connect)
				</td>				
				<td>
					<a href="<?php echo site_url('auth_other/google_openid_signin'); ?>">
						<img style="margin-top:5px;" src="<?php echo base_url(); ?>images/google_connect_button.png" alt="google open id" border="0"/>
					</a>
				</td>
				<td>
					<a href="<?php echo site_url('auth_other/yahoo_openid_signin'); ?>">
						<img style="margin-top:5px;" src="<?php echo base_url(); ?>images/yahoo_openid_connect.png" alt="yahoo open id" border="0"/>
					</a>
				</td>				
			</tr>
		</table>
		
		<p id="viewer-info"></p>
		
		<div id="fb-root"></div>
		<script src="http://connect.facebook.net/en_US/all.js"></script>
		<script type="text/javascript">
		  	FB.init({appId: "<?php echo $this->config->item('facebook_app_id'); ?>", status: true, cookie: true, xfbml: true});
		  	FB.Event.subscribe('auth.sessionChange', function(response) {
		    	if (response.session) 
		    	{
		      		// A user has logged in, and a new cookie has been saved
					//window.location.reload(true);
		    	} 
		    	else 
		    	{
		      		// The user has logged out, and the cookie has been cleared
		    	}
		  	});
		</script>
	</body>
</html>