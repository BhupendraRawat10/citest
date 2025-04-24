<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

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
        // dd($data);
        $this->load->view('dashboard', $data);
    }
}
