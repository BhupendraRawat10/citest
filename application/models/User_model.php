<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database(); // ✅ This is what loads $this->db
    }

    public function insert_user($data)
    {
        $this->db->insert('users', $data);
    }

    public function get_user_by_email($email)
    {
        $query = $this->db->get_where('users', ['email' => $email]);
        return $query->row_array();
    }

    public function get_user_by_id($id)
    {
        $query = $this->db->get_where('users', ['id' => $id]);
        return $query->row_array();
    }

    public function update_user($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('users', $data);
    }
    public function get_all_users() {
        return $this->db->get('users')->result();
    }
}
