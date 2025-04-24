<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('create_slug')) {
	function create_slug($string){
	   $slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
	   return $slug;
	}
}

if (!function_exists('create_lower_slug')) {
	function create_lower_slug($string){
	   $slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
	   return strtolower($slug);
	}
}

if (!function_exists('market_categories_menu')) {
	function market_categories_menu($country_id){
	   $CI =& get_instance();
	   $res = $CI->db->select('m_cat_id, m_cat_name')->get_where('market_primary_category', array('m_cat_status' => 1));
	   return $res->result_array();
	}
}

if (! function_exists('old')) {
	function old($field, $default=null){
		return set_value($field, $default);
	}
}

if (! function_exists('dd')) {
	function dd($arr, $param=null){
		echo "<pre>";
		   print_r($arr);
		echo "</pre>";
		if(!$param){
			exit;
		}
	}
}

// This function is used to generate unique payment pins.

if (! function_exists('generatePin')) {
	function generatePin($prefix = "PP", $length = 12){		
		$randomBytes = random_bytes(($length - strlen($prefix)) / 2);		
		$hexRandom = bin2hex($randomBytes);
		$pin = $prefix . strtoupper($hexRandom);
		$CI =& get_instance();
        $CI->db->where('pin', $pin);
        $query = $CI->db->get('payment_pins');	 
        if($query->num_rows() > 0){
			generatePin();
		}
		return $pin;
	}
}

// This function is used to fetch user info.

if (! function_exists('user')) {
	function user(){		
		$CI =& get_instance();
		if(isset($CI->session->userdata['UserLoggedin']['user_id']) && !empty($CI->session->userdata['UserLoggedin']['user_id'])){
			$user_id = $CI->session->userdata['UserLoggedin']['user_id'];
			$CI->db->where('id', $user_id);
			return $query = $CI->db->get('users')->result_array();
		}
		return false;
	}
}

if(! function_exists('insertData')) {
	function insertData($tableName, $data) {
		$ci = &get_instance();

		return $ci->db->insert($tableName, $data);
	}
}
if(! function_exists('insertDataRetID')) {
	function insertDataRetID($tableName, $data) {
		$ci = &get_instance();

		$ci->db->insert($tableName, $data);
		return $ci->db->insert_id();
	}
}
if(! function_exists('updateData')) {
	function updateData($tableName, $where, $data) {
		$ci = &get_instance();

		$ci->db->where($where);
		return $ci->db->update($tableName, $data);
	}
}
if(! function_exists('deleteData')) {
	function deleteData($tableName, $where) {
		$ci = &get_instance();

		$ci->db->where($where);
		return $ci->db->delete($tableName);
	}
}

if (!function_exists('create_notification')) {
    function create_notification($user_id, $action_username, $action_type, $link, $message = '') {
        // Get the CI instance
		// $action_type = 'request_join', 'cancel_req_join, 'joined_group', 'left_group', 'commented', 'removed_group', 'replied_comment'
        $CI =& get_instance();
        
        // Load the database library if not already loaded
        $CI->load->database();
        
        // Default message if not provided
        if (empty($message)) {
            switch ($action_type) {
                case 'accept_join':
                    $message = "{$action_username} has accepted your join request.";
                    break;
				case 'request_join':
                    $message = "{$action_username} has request join your group.";
                    break;
				case 'cancel_req_join':
					$message = "{$action_username} has cancelled your request to join the group.";
					break;
				case 'joined_group':
                    $message = "{$action_username} has joined your group.";
                    break;
                case 'left_group':
                    $message = "{$action_username} has left your group.";
                    break;
                case 'commented':
                    $message = "{$action_username} commented on your post.";
                    break;
				case 'replied_comment':
					$message = "{$action_username} replied on your comment.";
					break;
                default:
                    $message = "You have a new notification from {$action_username}.";
                    break;
            }
        }
        
        // Prepare the data for insertion
        $data = array(
            'user_id' => $user_id,  
            'action_type' => $action_type,
            'message' => $message,
            'action_link' => $link,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        );
        
        // Insert the notification into the database
        $CI->db->insert('notifications', $data);
        
        // Check if the notification was inserted successfully
        if ($CI->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
}

function increment_member_count($group_id) {
	$CI =& get_instance();

	// Increment the post count
	$CI->db->set('member_count', 'member_count + 1', FALSE);
	$CI->db->where('id', $group_id);
	$CI->db->update('groups');
}

function decrement_member_count($group_id) {
	$CI =& get_instance();

	// Decrement the post count
	$CI->db->set('member_count', 'member_count - 1', FALSE);
	$CI->db->where('id', $group_id);
	$CI->db->update('groups');
}

function increment_post_count($group_id) {
	$CI =& get_instance();

	// Increment the post count
	$CI->db->set('post_count', 'post_count + 1', FALSE);
	$CI->db->where('id', $group_id);
	$CI->db->update('groups');
}

function decrement_post_count($group_id) {
	$CI =& get_instance();

	// Decrement the post count
	$CI->db->set('post_count', 'post_count - 1', FALSE);
	$CI->db->where('id', $group_id);
	$CI->db->update('groups');
}

function increment_post_view_count($post_id) {
	$CI =& get_instance();

	// Increment the post count
	$CI->db->set('view_count', 'view_count + 1', FALSE);
	$CI->db->where('id', $post_id);
	$CI->db->update('posts');
}

// time difference helper
function time_ago($datetime) {
    $time_ago = strtotime($datetime);
    $current_time = time();
    $time_difference = $current_time - $time_ago;

    // Calculate time differences in seconds
    if ($time_difference < 1) return 'Just now';
    
    $seconds = $time_difference;
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440); // ~30 days
    $years = round($seconds / 31553280); // ~365 days

    if ($seconds < 60) {
        return "$seconds seconds ago";
    } elseif ($minutes < 60) {
        return "$minutes minutes ago";
    } elseif ($hours < 24) {
        return "$hours hours ago";
    } elseif ($days < 7) {
        return "$days days ago";
    } elseif ($weeks < 4) {
        return "$weeks weeks ago";
    } elseif ($months < 12) {
        return "$months months ago";
    } else {
        return "$years years ago";
    }
}
