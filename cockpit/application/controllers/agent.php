<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Agent extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('Session');
        $this->load->helper('user');
    }

    public function index() {
        $user = get_user();
        if (!$user) {
            $this->load->view('agent_logging');
        } else {
            $this->load->vars('user', $user);
            $this->load->view('agent_control');
        }
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

    public function changepwd() {
        $current_password = $this->input->get_post('current_password');
        $new_password = $this->input->get_post('new_password');
        $user = get_user();
        if (!$user) {
            echo json_encode(array('success' => 0));
            return;
        }
        if ($user['passhash'] != md5($current_password)) {
            echo json_encode(array('success' => 0));
            return;
        }
        $this->db->update('agent', array('passhash' => md5($new_password)), array('name' => $user['name']));
        echo json_encode(array('success' => 1));
    }

    public function status() {
        $user = get_user();
        if (!$user) {
            echo json_encode(array('success' => 0));
            return;
        }
        echo json_encode(array('name' => $user['name'], 'success' => 1, 'code' => $user['code'], 'level' => $user['level'], 'balance' => $user['credit_balance']));
    }

    public function log($page=0) {
        $user = get_user();
        if (!$user) {
            echo json_encode(array('success' => 0));
            return;
        }
        $logs = $this->db->where('agent', $user['id'])->order_by('time', 'DESC')->limit(50, $page*50)->get('creditlog')->result_array();
        echo json_encode(array('success' => 1, 'logs' => $logs));
    }

}

/* End of file */