<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function get_username() {
    $CI =& get_instance();
    $CI->load->library('Session');
    $username = $CI->session->userdata('username');
    if (!$username) {
        return FALSE;
    }
    return $username;
}

function get_user() {
    $CI =& get_instance();
    $username = get_username();
    if (!$username) {
        return FALSE;
    }
    return $CI->db->get_where('agent', array('name' => $username))->row_array();
}

function get_user_by_id($id, $lock = FALSE) {
    $CI =& get_instance();
    if ($lock) {
        $this->db->lock(LOCK_EXCLUSIVE);
    }
    $user = $CI->db->get_where('agent', array('id' => $id))->row_array();
    return $user;
}


/* End of file */