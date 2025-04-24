<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
		$this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->library('upload');
    }
    public function createuser()
    {
        // Input Data
        $name = $this->input->post('name');
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $profile_picture = null; // Initialize profile picture variable
        
        // Basic Validation
        if (empty($name) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'error' => 'All fields are required.']);
            return;
        }
    
        // Check if email already exists
        $existing_user = $this->User_model->get_user_by_email($email);
        if ($existing_user) {
            echo json_encode(['success' => false, 'error' => 'Email already registered.']);
            return;
        }
    
        // Password Hashing
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        // Upload profile picture if provided
        if (!empty($_FILES['profile_picture']['name'])) {
            // Define the upload directory
            $upload_dir = 'uploads/profile_pictures/';
            
            // Make sure the directory exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
            }
    
            // Generate a unique name for the file (to avoid overwriting existing files)
            $file_name = uniqid('profile_', true) . '.' . pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
    
            // Move the uploaded file to the upload directory
            $upload_path = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                $profile_picture = $upload_path; // Store the relative path in the database
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to upload profile picture.']);
                return;
            }
        }
        
        // Save user to DB
        $user_data = [
            'name' => $name,
            'email' => $email,
            'password' => $hashed_password,
            'profile_picture' => $profile_picture // Store the file path in the database
        ];
        
        $this->User_model->insert_user($user_data);
        
        echo json_encode(['success' => true, 'message' => 'User registered successfully.']);
    }
    
    
    
    

    // Registration form
    public function index()
    {
        $this->load->view('register');
    }

    // Register user

    // Login form
    public function login()
    {
        $this->load->view('login');
    }

    // Handle login
    public function authenticate()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');
    
        $user = $this->User_model->get_user_by_email($email);
    
        if ($user && password_verify($password, $user['password'])) {
            $this->session->set_userdata('user_id', $user['id']);
            redirect('dashboard');
        } else {
            $this->session->set_flashdata('login_error', 'Invalid email or password. Please try again.');
            redirect('auth/login');
        }
    }
    

    public function logout()
{
    $this->session->unset_userdata('user_id');
    $this->session->sess_destroy();

    redirect('login'); 
}

}
