<?php

class auth_other extends CI_Controller 
{	
	function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
		$this->load->model('tank_auth/users');

		// for google open id
        parse_str($_SERVER['QUERY_STRING'],$_GET);		
	}
	
	// handle when users log in using facebook account
	function fb_signin()
	{
		// load facebook library
		//$this->load->library('facebook'); // this has been loaded in autoload.php
		
		// get the facebook user and save in the session
		$fb_user = $this->facebook->getUser();
		if( isset($fb_user))
		{
			$this->session->set_userdata('facebook_id', $fb_user['id']);
			$user = $this->user_model->get_user_by_sm(array('facebook_id' => $fb_user['id']), 'facebook_id');
			if( sizeof($user) == 0) 
			{ 
				redirect('auth_other/fill_user_info', 'refresh'); 
			}
			else
			{
				// simulate what happens in the tank auth
				$this->session->set_userdata(array(	'user_id' => $user[0]->id, 'username' => $user[0]->username,
													'status' => ($user[0]->activated == 1) ? STATUS_ACTIVATED : STATUS_NOT_ACTIVATED));
				//$this->tank_auth->clear_login_attempts($user[0]->email); can't run this when doing FB
				$this->users->update_login_info( $user[0]->id, $this->config->item('login_record_ip', 'tank_auth'), 
												 $this->config->item('login_record_time', 'tank_auth'));
				redirect('auth', 'refresh');
			}
		}
		else 
		{ 
			echo 'cannot find the Facebook user'; 
		}
	}
	
	// function to allow users to log in via twitter
	function twitter_signin()
	{
		// It really is best to auto-load this library!
		//$this->load->library('tweet'); // automatically loaded in the autoload!
		
		// Enabling debug will show you any errors in the calls you're making, e.g:
		$this->tweet->enable_debug(TRUE);
		
		// If you already have a token saved for your user
		// (In a db for example) - See line #37
		// 
		// You can set these tokens before calling logged_in to try using the existing tokens.
		// $tokens = array('oauth_token' => 'foo', 'oauth_token_secret' => 'bar');
		// $this->tweet->set_tokens($tokens);
		if ( !$this->tweet->logged_in() )
		{
			// This is where the url will go to after auth.
			// ( Callback url )
			
			$this->tweet->set_callback(site_url('auth_other/twitter_signin'));
			
			// Send the user off for login!
			$this->tweet->login();
		}
		else
		{
			// You can get the tokens for the active logged in user:
			// $tokens = $this->tweet->get_tokens();
			
			// 
			// These can be saved in a db alongside a user record
			// if you already have your own auth system.
			
			// get the user id from twitter authentication and save to session
			$user = $this->tweet->call('get', 'account/verify_credentials');
			$twitter_id = $user->id;
			$this->session->set_userdata('twitter_id', $twitter_id);	
			
			// now see if the user exists with the given twitter id	
			$user = $this->user_model->get_user_by_sm(array('twitter_id' => $twitter_id), 'twitter_id');
			if( sizeof($user) == 0 ) 
			{ 
				redirect('auth_other/fill_user_info', 'refresh'); 
			}
			else
			{
				// simulate what happens in the tank auth
				$this->session->set_userdata(array(	'user_id' => $user[0]->id, 'username' => $user[0]->username,
													'status' => ($user[0]->activated == 1) ? STATUS_ACTIVATED : STATUS_NOT_ACTIVATED));
				//$this->tank_auth->clear_login_attempts($user[0]->email); can't run this when doing twitter
				redirect('auth', 'refresh');	
			}			
		}		
	}
	
	// handle when users log in using google friend connect
	function gfc_signin()
	{
		// we passed the user id in the URL, let's get it!
		$gfc_id = $this->uri->segment(3);
		if( !is_null($gfc_id))
		{
			$this->session->set_userdata('gfc_id', $gfc_id);
			$user = $this->user_model->get_user_by_sm(array('gfc_id' => $gfc_id), 'gfc_id');
			if( sizeof($user) == 0) 
			{ 
				redirect('auth_other/fill_user_info', 'refresh'); 
			}
			else
			{
				// simulate what happens in the tank auth
				$this->session->set_userdata(array(	'user_id' => $user[0]->id, 'username' => $user[0]->username,
													'status' => ($user[0]->activated == 1) ? STATUS_ACTIVATED : STATUS_NOT_ACTIVATED));
				//$this->tank_auth->clear_login_attempts($user[0]->email); can't run this when doing FB
				$this->users->update_login_info( $user[0]->id, $this->config->item('login_record_ip', 'tank_auth'), 
												 $this->config->item('login_record_time', 'tank_auth'));
				redirect('auth', 'refresh');
			}
		}
		else 
		{ 
			echo 'cannot find the Google Friend Connect ID!'; 
		}
	}

	// function for logging in via google open id
    function google_openid_signin()
    {
    	//$this->load->library('lightopenid'); // loaded in aotoload.php
    	
    	// these are some of the attributes we can get from open id
    	// refer to 
    	// http://code.google.com/p/lightopenid/wiki/GettingMoreInformation
    	// for more
    	$required_attr = array('namePerson/friendly', 'contact/email', 
    						   'namePerson/first', 'namePerson/last', 
    						   'contact/country/home', 'contact/email', 'pref/language');
    	try 
    	{
			if(!isset($_GET['openid_mode'])) 
    		{
    			$lightopenid = new Lightopenid;
    			$lightopenid->identity = 'https://www.google.com/accounts/o8/id';
    			$lightopenid->required = $required_attr;
    			redirect($lightopenid->authUrl(), 'refresh');
    		}
    		elseif($_GET['openid_mode'] == 'cancel')
    		{
    			echo 'User has cancelled authentication!';
    		}
    		else 
    		{
    			$lightopenid = new Lightopenid;
    			$lightopenid->required = $required_attr;
    			if($lightopenid->validate())
    			{
    				#Here goes the code that gets interpreted after successful login!!!
    				//print_r($lightopenid);
    				//echo '<br/>';
    				//print_r($lightopenid->identity);
    				//print_r($lightopenid->getAttributes());
    				
    				
					$google_open_id = $lightopenid->identity;
					$this->session->set_userdata('google_open_id', $google_open_id);	
					    				
    				// does this user exist?
					$user = $this->user_model->get_user_by_sm(array('google_open_id' => $google_open_id), 'google_open_id');
					if( sizeof($user) == 0 ) 
					{ 
						redirect('auth_other/fill_user_info', 'refresh'); 
					}
					else
					{
						// simulate what happens in the tank auth
						$this->session->set_userdata(array(	'user_id' => $user[0]->id, 'username' => $user[0]->username,
															'status' => ($user[0]->activated == 1) ? STATUS_ACTIVATED : STATUS_NOT_ACTIVATED));
						//$this->tank_auth->clear_login_attempts($user[0]->email); can't run this when doing twitter
						redirect('auth', 'refresh');	
					}
    			}
    			else 
    			{
    				echo 'User has not logged in.';
    			}
    		}
    	}
    	catch(ErrorException $e) 
    	{
    		echo $e->getMessage();
    	}
    }
    
	// function for logging in via google open id
    function yahoo_openid_signin()
    {
    	//$this->load->library('lightopenid'); // loaded in aotoload.php
    	
    	// these are some of the attributes we can get from open id
    	// refer to 
    	// http://code.google.com/p/lightopenid/wiki/GettingMoreInformation
    	// for more
    	$required_attr = array('namePerson/friendly', 'contact/email', 
    						   'namePerson/first', 'namePerson/last', 
    						   'contact/country/home', 'contact/email', 'pref/language');
    	try 
    	{
			if(!isset($_GET['openid_mode'])) 
    		{
    			$lightopenid = new Lightopenid;
    			$lightopenid->identity = 'http://me.yahoo.com';
    			$lightopenid->required = $required_attr;
    			redirect($lightopenid->authUrl(), 'refresh');
    		}
    		elseif($_GET['openid_mode'] == 'cancel')
    		{
    			echo 'User has cancelled authentication!';
    		}
    		else 
    		{
    			$lightopenid = new Lightopenid;
    			$lightopenid->required = $required_attr;
    			if($lightopenid->validate())
    			{
    				#Here goes the code that gets interpreted after successful login!!!
    				//print_r($lightopenid);
    				//echo '<br/>';
    				//print_r($lightopenid->identity);
    				//print_r($lightopenid->getAttributes());
    				
    				$yahoo_open_id = $lightopenid->identity;
					$this->session->set_userdata('yahoo_open_id', $yahoo_open_id);	
					    				
    				// does this user exist?
					$user = $this->user_model->get_user_by_sm(array('yahoo_open_id' => $yahoo_open_id), 'yahoo_open_id');
					if( sizeof($user) == 0 ) 
					{ 
						redirect('auth_other/fill_user_info', 'refresh'); 
					}
					else
					{
						// simulate what happens in the tank auth
						$this->session->set_userdata(array(	'user_id' => $user[0]->id, 'username' => $user[0]->username,
															'status' => ($user[0]->activated == 1) ? STATUS_ACTIVATED : STATUS_NOT_ACTIVATED));
						//$this->tank_auth->clear_login_attempts($user[0]->email); can't run this when doing twitter
						redirect('auth', 'refresh');	
					}
    			}
    			else 
    			{
    				echo 'User has not logged in.';
    			}
    		}
    	}
    	catch(ErrorException $e) 
    	{
    		echo $e->getMessage();
    	}
    }    
	
	// called when user logs in via facebook/twitter for the first time
	function fill_user_info()
	{
		// load validation library and rules
		$this->load->config('tank_auth', TRUE);
		$this->load->library('form_validation');
		$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean|min_length['.$this->config->item('username_min_length', 'tank_auth').']|callback_username_check');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email|callback_email_check');
		
		// Run the validation
		if ($this->form_validation->run() == false ) 
		{
			$this->load->view('auth_other/fill_user_info'); 
		}
		else
		{
			$username = mysql_real_escape_string($this->input->post('username'));
			$email = mysql_real_escape_string($this->input->post('email'));
			
			/*
			 * We now must create a new user in tank auth with a random password in order
			 * to insert this user and also into user profile table with tank auth id
			 */
			$password = $this->generate_password(9, 8);
			$data = $this->tank_auth->create_user($username, $email, $password, false);
			$user_id = $data['user_id'];
			if( $this->session->userdata('facebook_id')) 
			{ 
				$this->user_model->update_user_profile($user_id, array('facebook_id' => $this->session->userdata('facebook_id')));
			}
			else if( $this->session->userdata('twitter_id'))
			{
				$this->user_model->update_user_profile($user_id, array('twitter_id' => $this->session->userdata('twitter_id')));
			}
			else if( $this->session->userdata('gfc_id'))
			{
				$this->user_model->update_user_profile($user_id, array('gfc_id' => $this->session->userdata('gfc_id')));
			}
			else if( $this->session->userdata('google_open_id'))
			{
				$this->user_model->update_user_profile($user_id, array('google_open_id' => $this->session->userdata('google_open_id')));
			}
			else if( $this->session->userdata('yahoo_open_id'))
			{
				$this->user_model->update_user_profile($user_id, array('yahoo_open_id' => $this->session->userdata('yahoo_open_id')));
			}			
			// let the user login via tank auth
			$this->tank_auth->login($email, $password, false, false, true);
			redirect('auth', 'refresh');
		}
	}
	
	// a logout function for 3rd party
	function logout()
	{
		$redirect = site_url('auth/logout');
		if( $this->session->userdata('gfc_id') && $this->session->userdata('gfc_id') != '') { $redirect = null; }
		
		// set all user data to empty
		$this->session->set_userdata(array('facebook_id' => '', 
										   'twitter_id' => '', 
										   'gfc_id' => '',
										   'google_open_id' => '',
										   'yahoo_open_id' => ''));
		if( $redirect ) { redirect($redirect, 'refresh'); } 
		else { $this->load->view('gfc_logout'); }
	}
		
	// function to validate the email input field
	function email_check($email)
	{
		$user = $this->users->get_user_by_email($email);
		if ( sizeof($user) > 0) 
		{
			$this->form_validation->set_message('email_check', 'This %s is already registered.');
			return false;
		}
		else { return true; }
	}
	function username_check($username)
	{
		$user = $this->users->get_user_by_username($username);
		if ( sizeof($user) > 0) 
		{
			$this->form_validation->set_message('username_check', 'This %s is already registered.');
			return false;
		}
		else { return true; }
	}
	// generates a random password for the user
	function generate_password($length=9, $strength=0) 
	{
		$vowels = 'aeuy';
		$consonants = 'bdghjmnpqrstvz';
		if ($strength & 1) { $consonants .= 'BDGHJLMNPQRSTVWXZ'; }
		if ($strength & 2) { $vowels .= "AEUY"; }
		if ($strength & 4) { $consonants .= '23456789'; }
		if ($strength & 8) { $consonants .= '@#$%'; }
	 
		$password = '';
		$alt = time() % 2;
		for ($i = 0; $i < $length; $i++) 
		{
			if ($alt == 1) 
			{
				$password .= $consonants[(rand() % strlen($consonants))];
				$alt = 0;
			} 
			else 
			{
				$password .= $vowels[(rand() % strlen($vowels))];
				$alt = 1;
			}
		}
		return $password;
	}
}

/* End of file main.php */
/* Location: ./freally_app/controllers/main.php */