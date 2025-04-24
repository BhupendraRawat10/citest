<?php
/*
* Unauthorized copying of this file, via any medium is strictly prohibited
* Proprietary and confidential
*/

class Utility {
        private $ci;
        public function __construct()
        {
            $this->ci = &get_instance();
        }
    public function send_mail($receiver_email=array(),$htmlContent=NULL,$subject=NULL,$sender_email=NULL,$username=NULL,$attachment_path=NULL){
          
        $receiver_email = $receiver_email;
        $subject = $subject;
        if(empty($sender_email)) {
            $sender_email='tsting.cwsinfotec@gmail.com';
        } else {
            $sender_email = $sender_email;  
        }
        $message = $htmlContent;
        $this->ci->load->library('email');
        $config = Array(
           'protocol' => 'smtp',
           'smtp_host' => 'smtp.gmail.com',
           'smtp_port' => '587',
           'smtp_crypto' => 'tls',
           'smtp_timeout' => '7',
           // 'smtp_user' => 'tsting.cwsinfotec@gmail.com',
           'smtp_user' => 'admin@allmarkets.org',
           // 'smtp_pass' => 'lhsrdoxrktawsaeo',        
           'smtp_pass' => 'qlthudvnojmnyegr',        
           'charset' => 'iso-8859-1',
           'mailtype'  => 'html',
        );
        $this->ci->email->initialize($config);
        $this->ci->email->set_newline("\r\n");

        // $this->ci->email->from($sender_email);
		$this->ci->email->from('admin@allmarkets.org', 'All Markets', 'admin@allmarkets.org');
        $this->ci->email->to($receiver_email);
        $this->ci->email->subject($subject);
        $this->ci->email->message($htmlContent);

        // If File Attached //
        if(!empty($attachment_path)){ $this->ci->email->attach($attachment_path); }
        // $mail_result=$this->ci->email->send();
		if (! $this->ci->email->send())
		{
			$this->ci->load->helper('url');
			echo $this->ci->email->print_debugger(); 
			// die();
            return FALSE;			
		}else{
			return TRUE;
		}
    }

public function uplaod_file($filename , $dir ,$hidden_input_file=NULL){
        $timestamp = time();
        $type = @$_FILES[$fileName]['type'];
        if ($_FILES[$fileName]['tmp_name']) {
            $name      = $_FILES[$fileName]['name'];
            $fileRandName   = uniqid() . "_" . time(); // 5dab1961e93a7_1571494241
            $extension  = pathinfo( $name, PATHINFO_EXTENSION ); // jpg
            $new_name   = $fileRandName . '.' . $extension; // 5dab1961e93a7_1571494241.jpg	
			$config['upload_path'] = './'.$dir;
			$config['allowed_types'] = 'gif|jpg|jpeg|png';
			$config['file_name']  = $new_name;
			$config['max_size']  = 2000;
			$config['max_width']  = 2024;
			$config['max_height'] = 2024;
			$this->load->library('upload', $config);
			if ( ! $this->upload->do_upload($fileName))
			{
					$data['error'] = array('error' => $this->upload->display_errors());
					echo '<pre>';
					print_r($data['error']);
				die();
                redirect(base_url('profile/officer'));				
					
			}
		}else{
			$new_name  =  $hidden_input_file;
		}
		return $new_name;          
    }
    
}
?>