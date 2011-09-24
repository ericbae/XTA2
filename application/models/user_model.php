<?php

class user_model extends CI_Model 
{
	// get user by their social media id
	function get_user_by_sm($data, $sm_id)
	{
		$this->db->select("u.*, up." . $sm_id);
		$this->db->from("users AS u");
		$this->db->join("user_profiles AS up", "u.id=up.user_id");
		$this->db->where($data);
		$query = $this->db->get();
		return $query->result();
	}

	// Returns user by its email
	function get_user_by_email($email)
	{
		$query = $this->db->query("SELECT * FROM users u, user_profiles up WHERE u.email='$email' and u.id = up.user_id");
		return $query->result();
	}
	
	function get_user_by_username($username)
	{
		$query = $this->db->query("SELECT * FROM users u, user_profiles up WHERE u.username='$username' and u.id = up.user_id");
		return $query->result();
	}
	// a generic update method for user profile
	function update_user_profile($user_id, $data)
	{
		$this->db->where('user_id', $user_id);
		$this->db->update('user_profiles', $data); 
	}

	// return the user given the id
	function get_user($user_id)
	{
		$query = $this->db->query("SELECT users.*, user_profiles.* FROM users, user_profiles WHERE " .
								  "users.id='$user_id' AND user_profiles.user_id='$user_id'");
		return $query->result();
	}
}
?>