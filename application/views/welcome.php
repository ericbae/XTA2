<html>
	<head>
		<title>XTA2 - CI Tank Auth Authentication with 3rd party plugins.</title>	
	</head>
	
	<body>
		
		Hi, <strong><?php echo $username; ?></strong>! You are logged in now. 
		
		<?php
			if( $this->session->userdata('facebook_id'))
			{
				$fb_cookie = $this->facebook->get_facebook_cookie();
		?>
				<a href="http://www.facebook.com/logout.php?api_key=<?php echo $this->config->item('facebook_app_id'); ?>&session_key=<?php echo $fb_cookie['session_key']; ?>&next=<?php echo site_url('auth_other/logout'); ?>">Log out</a>
		<?php		
			}
			else
			{
				echo anchor('auth_other/logout', 'Logout');
			}
		?>
	</body>
</html>