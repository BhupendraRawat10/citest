<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('user_id')) {
            redirect('auth/login');
        }
        $this->load->model('User_model');
    }

    public function index()
    {
        $user = $this->User_model->get_user_by_id($this->session->userdata('user_id'));
        $data['user'] = $user;
        $this->load->view('profile', $data);
    }

    public function update()
    {
          // Retrieve input data
          $name = $this->input->post('name');
          $email = $this->input->post('email');
          $password = $this->input->post('password');
          $profile_picture = ''; // Initialize profile picture variable
          
          // Handle profile picture upload
          if (!empty($_FILES['profile_picture']['name'])) {
              // Upload configuration
              $upload_dir = './uploads/profile_pictures/';
              if (!is_dir($upload_dir)) {
                  mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
              }
      
              // Validate file type (only allow images)
              $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
              $file_type = $_FILES['profile_picture']['type'];
              if (!in_array($file_type, $allowed_types)) {
                  // Show error and return
                  echo json_encode(['success' => false, 'error' => 'Only image files are allowed.']);
                  return;
              }
      
              // Validate file size (e.g., max size: 2MB)
              $max_size = 2 * 1024 * 1024; // 2MB in bytes
              if ($_FILES['profile_picture']['size'] > $max_size) {
                  // Show error and return
                  echo json_encode(['success' => false, 'error' => 'File size exceeds the maximum limit of 2MB.']);
                  return;
              }
      
              // Generate unique file name and upload
              $file_name = uniqid('profile_', true) . '.' . pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
              $upload_path = $upload_dir . $file_name;
      
              if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                  $profile_picture = $upload_path; // Store the relative path in the database
              } else {
                  echo json_encode(['success' => false, 'error' => 'Failed to upload profile picture.']);
                  return;
              }
          }
      
          // Prepare user data for update
          $user_data = [
              'name' => $name,
              'email' => $email
          ];
          
          // Update password if provided
          if (!empty($password)) {
              $user_data['password'] = password_hash($password, PASSWORD_BCRYPT);
          }
          
          // Update profile picture if uploaded
          if ($profile_picture) {
              $user_data['profile_picture'] = $profile_picture;
          }
          
          // Update user in the database
          $user_id = $this->session->userdata('user_id');
          $update_result = $this->User_model->update_user($user_id, $user_data);
      
        redirect('dashboard');
    }
}
