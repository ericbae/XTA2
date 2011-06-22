<html>
	<head>
		<script type="text/javascript" src="http://www.google.com/jsapi"></script>
		<script type="text/javascript">google.load('friendconnect', '0.8');</script>
		<script type="text/javascript">
			google.friendconnect.container.setParentUrl('/' /* location of rpc_relay.html and canvas.html */);
			google.friendconnect.container.loadOpenSocialApi(
			{
				site: '<?php echo $this->config->item('google_app_id'); ?>', /* put your site ID here */
				onload: function(securityToken) 
				{
					if (!window.timesloaded) 
					{
						window.timesloaded = 1;
						google.friendconnect.requestSignOut();
					} 
					else 
					{
						window.timesloaded++;
					}
					if (window.timesloaded > 1) 
					{
						window.top.location.href = "<?php echo site_url('auth/logout'); ?>";
					}
				}
			});
		</script>			
	</head>
	
	<body></body>
</html>