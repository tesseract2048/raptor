<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function get_username() {
    $CI =& get_instance();
    $CI->load->library('Session');
    $username = $CI->Session->userdata('username');
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
    return $CI->db->get_where('agent', array('username' => $username))->row_array();
}


/* End of file */