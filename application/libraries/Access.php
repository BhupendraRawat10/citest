<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Access
{
    public $CI;
    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /** 
    * check if the user is logged in if not then it saves the current route and redirect the user to login page.
    *
    * @param  string|null  $key
    * @param  string|null  $msg
    * 
    * @return void
    */
    public function auth($key=null,$msg=null,$login_url=null)
    {
        if(!$this->CI->session->UserLoggedin){
            if(is_null($key) || is_null($msg)){
                $this->CI->session->set_flashdata('msg_success', 'You need to login before accessing this page.');
            }else{
                $this->CI->session->set_flashdata($key, $msg);
            }
            $last_request_page = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        	$this->CI->session->set_userdata('returning_url', $last_request_page);
			if(isset($login_url)){
			   redirect($login_url);	
			}else{
			   redirect('/login');	
			}			
		}
    }

    /** 
    * check if the user is not logged in if user is logged in then it redirect the user to the given route or home.
    *
    * @param  string|null  $route 
    * 
    * @return void
    */
    public function guest($route=null)
    {
        if($this->CI->session->UserLoggedin){
			is_null($route) ? redirect('/') : redirect($route);
		}
    }

    /** 
    * check user is logged in and has the role of admin if not then redirect the user to 404 route.
    *
    * @param  string|null  $key
    * @param  string|null  $msg
    * 
    * @return void
    */
    public function admin($key=null,$msg=null)
    {
		$key = 'msg';
		$msg = 'Please login';
		$login_url = 'admin';
		$this->auth($key,$msg,$login_url);
        $roleId = $this->CI->session->UserLoggedin['user_role_id'];
        if($roleId == 2 || $roleId == 3){
            redirect('404_override');
        }

    }
	
    public function admin_roles($module_name)
    {		
		$key = 'msg';
		$msg = 'Please login';
		$login_url = 'admin';
		$this->auth($key,$msg,$login_url);
        $roleId = $this->CI->session->UserLoggedin['user_role_id'];
       		
		$data['access'] = false;	
		$data['read'] = '';	
		$data['write'] = '';	
		$data['delete'] = '';		
		
        if($roleId ==1){
			$data['access'] = TRUE;	
			$data['read'] = 1;	
			$data['write'] = 1;	
			$data['delete'] = 1;					    
			return $data;
        }else{
			if($roleId !=2 && $roleId !=3){			
				$id = $this->CI->session->UserLoggedin['user_id'];
				$this->CI->db->where('id', $id);
				$this->CI->db->where('status', 1);
				$query = $this->CI->db->get('users');   
				$row = $query->result_array();
				$access_modules = json_decode($row[0]['access_modules']);
				// echo "<pre>";
				// print($_SESSION);
				// print_r($access_modules);
				if(in_array($module_name,$access_modules)){
					// if($module_name == 'Pin Payments')
					// print_r($access_modules);
					$access_permissions = json_decode($row[0]['access_permissions']);	
				    $data['access'] = TRUE;	
				    $data['read'] = $access_permissions->read;	
				    $data['write'] = $access_permissions->write;	
				    $data['delete'] = $access_permissions->delete;					    
					return $data;
				}else{
					return $data;
				}                								
			}else{
				return $data;
			}
		}

    }	

    /** 
    * check user is logged in and has the role of supplier if not then redirect the user to 404 route.
    *
    * @param  string|null  $key
    * @param  string|null  $msg
    * 
    * @return void
    */
    public function supplier($key=null,$msg=null)
    {
        $this->auth($key,$msg);
        $roleId = $this->CI->session->UserLoggedin['user_role_id'];
        if($roleId != 2){
            redirect('404_override');
        }
    }

    /** 
    * check user is logged in and has the role of user if not then redirect the user to 404 route.
    *
    * @param  string|null  $key
    * @param  string|null  $msg
    * 
    * @return void
    */
    public function user($key=null,$msg=null)
    {

        $this->auth($key,$msg);
        $roleId = $this->CI->session->UserLoggedin['user_role_id'];
        if($roleId != 3){
            redirect('404_override');
        }

    }
    public function mentor($key=null,$msg=null)
    {

		$currentURL = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$this->CI->session->set_userdata('returning_url', $currentURL);		
		$is_active = $this->active_module('Mentorship');
		if($is_active){
			$login_url = base_url('mentorship/login');
			$this->auth($key,$msg,$login_url);
			$roleId = $this->CI->session->UserLoggedin['user_role_id'];
	
			/*
			* Normal User + Supplier :- user_role_id 3 && module_roles_id = 4  (One user can add or post the service and also can access posted service by other users in same module)
			* Normal user :- user_role_id 3 (Can Access services and products)
			* Supplier user :- user_role_id 2 (Can Add services or products)
			* Admin user :- user_role_id 1 ( 1 role is an Admin )
			*/	
			
			if($is_active[0]['module_roles_id'] != 5 && $roleId != 2){
				redirect('404_override');
			}			
		}else{
			
			/*
			* Please set your module_name from table `modules` and check thier your module name, if there is no module * name in table, Please add it via admin or manually.
			*
			*/				
			$activation_values = array(
				"user_id"=>$this->CI->session->UserLoggedin['user_id'],
				"user_role_id"=>$this->CI->session->UserLoggedin['user_role_id'],
				"module_name"=>"Mentorship",
				"module_user_role_id"=>5,
				"module_user_type"=>"Mentor",
				"module_status"=>1			
			);
			$this->CI->session->set_userdata('module',$activation_values);
			redirect('activate/module');		
		}

        // $this->auth($key,$msg);
        // $roleId = $this->CI->session->UserLoggedin['user_role_id'];
        // if($roleId != 4){
            // redirect('404_override');
        // }

    }

    public function mentee($key=null,$msg=null)
    {
		$currentURL = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$this->CI->session->set_userdata('returning_url', $currentURL);		
		$is_active = $this->active_module('Mentorship');
		if($is_active){
			$login_url = base_url('mentorship/login');
			$this->auth($key,$msg,$login_url);
			$roleId = $this->CI->session->UserLoggedin['user_role_id'];
	
			/*
			* Normal User + Supplier :- user_role_id 3 && module_roles_id = 4  (One user can add or post the service and also can access posted service by other users in same module)
			* Normal user :- user_role_id 3 (Can Access services and products)
			* Supplier user :- user_role_id 2 (Can Add services or products)
			* Admin user :- user_role_id 1 ( 1 role is an Admin )
			*/	
			// echo $is_active[0]['module_roles_id'];
			// echo $roleId;
			// die();
			if($is_active[0]['module_roles_id'] != 4 && $roleId != 3){
				// redirect('404_override');
			}			
		}else{
			
			/*
			* Please set your module_name from table `modules` and check thier your module name, if there is no module * name in table, Please add it via admin or manually.
			*
			*/				
			$activation_values = array(
				"user_id"=>$this->CI->session->UserLoggedin['user_id'],
				"user_role_id"=>$this->CI->session->UserLoggedin['user_role_id'],
				"module_name"=>"Mentorship",
				"module_user_role_id"=>4,
				"module_user_type"=>"Mentee",
				"module_status"=>1			
			);
			$this->CI->session->set_userdata('module',$activation_values);
			redirect('activate/module');		
		}
		// $this->auth($key,$msg);
        // $roleId = $this->CI->session->UserLoggedin['user_role_id'];
        // if($roleId != 5){
            // redirect('404_override');
        // }

    }

    public function business($key=null,$msg=null)
    {
        // $this->auth($key,$msg);
        // $roleId = $this->CI->session->UserLoggedin['user_role_id'];
        // if($roleId != 6){
        //     redirect('404_override');
        // }
        $currentURL = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$this->CI->session->set_userdata('returning_url', $currentURL);		
		$is_active = $this->active_module('Buiness Investor');
		if($is_active){
			$login_url = base_url('business/login');
			$this->auth($key,$msg,$login_url);
			$roleId = $this->CI->session->UserLoggedin['user_role_id'];
	
			/*
			* Normal User + Supplier :- user_role_id 3 && module_roles_id = 4  (One user can add or post the service and also can access posted service by other users in same module)
			* Normal user :- user_role_id 3 (Can Access services and products)
			* Supplier user :- user_role_id 2 (Can Add services or products)
			* Admin user :- user_role_id 1 ( 1 role is an Admin )
			*/	
			
			if($is_active[0]['module_roles_id'] != 6 && $roleId != 2){
				redirect('404_override');
			}			
		}else{
			
			/*
			* Please set your module_name from table `modules` and check thier your module name, if there is no module * name in table, Please add it via admin or manually.
			*
			*/				
			$activation_values = array(
				"user_id"=>$this->CI->session->UserLoggedin['user_id'],
				"user_role_id"=>$this->CI->session->UserLoggedin['user_role_id'],
				"module_name"=>"Buiness Investor",
				"module_id"=>3,
				"module_user_role_id"=>6,
				"module_user_type"=>"Buiness Investor",
				"module_status"=>1			
			);
			$this->CI->session->set_userdata('module',$activation_values);
			redirect('activate/module');		
		}

    }

    public function investor($key=null,$msg=null)
    {
        // $this->auth($key,$msg);
        // $roleId = $this->CI->session->UserLoggedin['user_role_id'];
        // if($roleId != 7){
        //     redirect('404_override');
        // }

        $currentURL = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$this->CI->session->set_userdata('returning_url', $currentURL);		
		$is_active = $this->active_module('Buiness Investor');
		if($is_active){
			$login_url = base_url('business/login');
			$this->auth($key,$msg,$login_url);
			$roleId = $this->CI->session->UserLoggedin['user_role_id'];
			$module_roles_id = $this->CI->session->set_userdata('module_roles_id',4);
	
			/*
			* Normal User + Supplier :- user_role_id 3 && module_roles_id = 4  (One user can add or post the service and also can access posted service by other users in same module)
			* Normal user :- user_role_id 3 (Can Access services and products)
			* Supplier user :- user_role_id 2 (Can Add services or products)
			* Admin user :- user_role_id 1 ( 1 role is an Admin )
			*/	
			
			if($is_active[0]['module_roles_id'] != 7 && $roleId != 3){
				redirect('404_override');
			}			
		}else{
			
			/*
			* Please set your module_name from table `modules` and check thier your module name, if there is no module * name in table, Please add it via admin or manually.
			*
			*/				
			$activation_values = array(
				"user_id"=>$this->CI->session->UserLoggedin['user_id'],
				"user_role_id"=>$this->CI->session->UserLoggedin['user_role_id'],
				"module_name"=>"Buiness Investor",
				"module_id"=>3,
				"module_user_role_id"=>7,
				"module_user_type"=>"Buiness Investor",
				"module_status"=>1			
			);
			$this->CI->session->set_userdata('module',$activation_values);
			redirect('activate/module');		
		}

    }

     /** 
    * To check role is not for student, redirect to  .
    *
    */
    public function students($key=null,$msg=null)
    {
		$currentURL = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$this->CI->session->set_userdata('returning_url', $currentURL);		
		$is_active = $this->active_module('students');
		if($is_active){
			$login_url = base_url('students/login');
			$this->auth($key,$msg,$login_url);
			$roleId = $this->CI->session->UserLoggedin['user_role_id'];
	
			/*
			* Normal User + Supplier :- user_role_id 3 && module_roles_id = 4  (One user can add or post the service and also can access posted service by other users in same module)
			* Normal user :- user_role_id 3 (Can Access services and products)
			* Supplier user :- user_role_id 2 (Can Add services or products)
			* Admin user :- user_role_id 1 ( 1 role is an Admin )
			*/	
			
			if($is_active[0]['module_roles_id'] != 8 && $roleId != 3){
				redirect('404_override');
			}			
		}else{
			
			/*
			* Please set your module_name from table `modules` and check thier your module name, if there is no module * name in table, Please add it via admin or manually.
			*
			*/				
			$activation_values = array(
				"user_id"=>$this->CI->session->UserLoggedin['user_id'],
				"user_role_id"=>$this->CI->session->UserLoggedin['user_role_id'],
				"module_name"=>"Students",
				"module_user_role_id"=>3,
				"module_user_type"=>"Students",
				"module_status"=>1			
			);
			$this->CI->session->set_userdata('module',$activation_values);
			redirect('activate/module');		
		}        
    }  
	
	
     /** 
    *   Method to check user has activated or not activated the visting module
    *
    * @param  string|null  $key
    * @param  string|null  $msg
    * 
    * @return void
    */
    public function active_module($name,$id=NULL)
    {
        // $this->auth();
        if(isset($this->CI->session->UserLoggedin['user_id'])){
        $id = $this->CI->session->UserLoggedin['user_id'];
        }
        $this->CI->db->where('module_name', $name);
        if(isset($id)){
        $this->CI->db->where('user_id', $id);
        }
        $this->CI->db->where('module_status', 1);
        $query = $this->CI->db->get('active_modules');   
        $row = $query->result_array();
        return $row;        
    }	
    
    public function professional_collaboration($key=null,$msg=null)
    {
        // $this->auth($key,$msg);
        // $roleId = $this->CI->session->UserLoggedin['user_role_id'];
        // if($roleId != 9){
        //     redirect('404_override');
        // }

        $currentURL = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$this->CI->session->set_userdata('returning_url', $currentURL);		
		$is_active = $this->active_module('Professional Buiness');
		if($is_active){
			$login_url = base_url('professional_collaboration/login');
			$this->auth($key,$msg,$login_url);
			$roleId = $this->CI->session->UserLoggedin['user_role_id'];
	
			/*
			* Normal User + Supplier :- user_role_id 3 && module_roles_id = 4  (One user can add or post the service and also can access posted service by other users in same module)
			* Normal user :- user_role_id 3 (Can Access services and products)
			* Supplier user :- user_role_id 2 (Can Add services or products)
			* Admin user :- user_role_id 1 ( 1 role is an Admin )
			*/	
			
			if($is_active[0]['module_roles_id'] != 9 && $roleId != 3){
				redirect('404_override');
			}			
		}else{
			
			/*
			* Please set your module_name from table `modules` and check thier your module name, if there is no module * name in table, Please add it via admin or manually.
			*
			*/				
			$activation_values = array(
				"user_id"=>$this->CI->session->UserLoggedin['user_id'],
				"user_role_id"=>$this->CI->session->UserLoggedin['user_role_id'],
				"module_name"=>"Professional Buiness",
				"module_id"=>4,
				"module_user_role_id"=>9,
				"module_user_type"=>"Professional Buiness",
				"module_status"=>1			
			);
			$this->CI->session->set_userdata('module',$activation_values);
			redirect('activate/module');		
		}

    }  

     /** 
    * To check dating profile of user is completed or not, if not, redirect to dating profile page to .
    *
    */
    public function checkDatingProfile()
    {
        $this->auth();
        $id = $this->CI->session->UserLoggedin['user_id'];
        $this->CI->db->where('user_id', $id);
        $query = $this->CI->db->get('dating_profile'); 
        $num = $query->num_rows();       
		if($num ==0){
			$this->CI->session->set_flashdata('msg_success', 'You need to complete your dating profile before accessing dating module.');
			redirect('users/DatingProfile');
		}
    }
    public function isDatingProfileCompleted($id)
    {
        $this->CI->db->where('user_id', $id);
        $query = $this->CI->db->get('dating_profile'); 
        $num = $query->num_rows();       
		if($num ==0){
            return FALSE;
		}else return TRUE;
    }
	

     /** 
    * check supplier status active or not if not then redirect the user to user-status route.
    *
    * @param  string|null  $key
    * @param  string|null  $msg
    * 
    * @return void
    */
    public function supplierstatus()
    {
        $this->auth();
        $id = $this->CI->session->UserLoggedin['user_id'];
        $this->CI->db->where('supplier_id', $id);
        $query = $this->CI->db->get('supplier_applied_supplies');   
        $row = $query->result_array();
        return $row;        
    }

    public function fetch_user_pic($id=null)
    {
        $query="select profile_pic from users 
            where users.id = {$id}"; 
            return $this->CI->db->query($query)->row(); 

    }
    
    public function fetch_dating_pic($user_id=null)
    {
        $query="select new_dating_profile,dating_banner from dating_profile 
            where dating_profile.user_id = {$user_id}"; 
            return $this->CI->db->query($query)->row(); 

    }
     public function fetch_user_name($id=null)
    {
        $query="select first_name from users 
            where users.id = {$id}"; 
            return $this->CI->db->query($query)->row(); 

    }

     public function fetch_last_login($id=null)
    {
        $query="select last_login from users 
            where users.id = {$id}"; 
            return $this->CI->db->query($query)->row(); 

    }

    public function fetch_complete_profile($id = null){
        $query="select * from users 
            where users.id = {$id}"; 
            return $this->CI->db->query($query)->row(); 
    }
	
    public function active_plans($user_id,$product_type){

        $arr = $this->CI->db->select('subscriptions.*,DATEDIFF(end_date, CURDATE()) AS date_difference')
        ->from('subscriptions')
        ->where('subscriptions.user_id',$user_id)
        ->where('subscriptions.product_type',$product_type)
        ->where('subscriptions.is_active',1)
        ->where('subscriptions.payment_status',1)
		->where('CURDATE() < end_date')
		->order_by('subscriptions.updated_at','desc')
        ->get()->result_array(); 
        return $arr;
		
    }
    public function isJobProfileComplted($user_id){

        $arr = $this->CI->db->select('*')
        ->from('job_profile')
        ->where('job_profile.user_id',$user_id)
        ->get()->result_array(); 
        return $arr;
		
    }	
}