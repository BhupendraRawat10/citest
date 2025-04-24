<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_verify extends CI_Hooks 
{
    public $CI;
    public function __construct()
    {
        parent::__construct();
        $this->CI =& get_instance();
    }
    public function verifyEmail(){
        if(isset($this->CI->session->UserLoggedin['user_id'])){
            $user_id = $this->CI->session->UserLoggedin['user_id'];

        }else{
            $user_id = NULL;
        }
        $user = $this->CI->db->get_where('users', array('id' => $user_id))->row();
        // print_r($user->email_verified_at == NULL);exit;
        $lastSeg = end($this->CI->uri->segments);
        $verifyAcc = $this->CI->uri->segments[1] ?? '';
        if(isset($this->CI->session->UserLoggedin) 
        && $user->email_verified_at == NULL 
        && $this->CI->session->UserLoggedin['user_role_id'] == 2
        && $lastSeg != "verify-email" 
        && $lastSeg != "verification-notification"
        && $verifyAcc != "verify-account"
        && $lastSeg != "contact"
        && $lastSeg != "404_override"){
           redirect('/verify-email');
        }
    }

}