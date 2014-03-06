<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ticket extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('Session');
    }

    private function check_login($name, $password) {
        $row = $this->db->get_where('agent', array('name' => $name, 'passhash' => md5($password)))->row_array();
        if (!$row || count($row) == 0) {
            return FALSE;
        }
        $this->db->where('name', $name)->update('agent', array('last_login_time' => time(), 'last_login_ip' => $this->input->ip_address()));
        return TRUE;
    }

    public function logout() {
        $this->session->unset_userdata('username');
        echo json_encode(array('success' => 1));
    }

    public function login() {
        $name = $this->input->get_post('name');
        $password = $this->input->get_post('password');
        if ($this->check_login($name, $password)) {
            $this->session->set_userdata('username', $name);
            echo json_encode(array('success' => 1));
        } else {
            echo json_encode(array('success' => 0));
        }
    }

}

/* End of file */