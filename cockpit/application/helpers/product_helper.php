<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function get_areas($product_id) {
    $CI =& get_instance();
    $areas = $CI->db->select(array('id', 'name'))->get_where('area', array('product_id' => $product_id, 'state' => 0))->result_array();
    return $areas;
}

function get_subcate($subcate) {
    $CI =& get_instance();
    $subcate = $CI->db->get_where('subcate', array('id' => $subcate))->row_array();
    return $subcate;
}

function get_product_fields($product) {
    $fields = array();
    $areas = get_areas($product['id']);
    if (count($areas) > 0) {
        $fields['area'] = array(
            'text' => '服务区',
            'type' => 'select',
            'items' => $areas
        );
    }
    $subcate = get_subcate($product['subcate']);
    if ($subcate['target_name']) {
        $fields['to'] = array(
            'text' => $subcate['target_name'],
            'type' => 'text'
        );
    }
    return $fields;
}

function create_job($ticket, $values) {
    $CI =& get_instance();
    $product = $ticket['product'];
    $to = $values['to'];
    if ($product['cate'] == 4) {
        $this->load->helper('user');
        $user = get_user();
        if (!$user) {
            return 'LOGIN_REQUIRED';
        }
        // hack self business here
        if ($product['subcate'] == 900) {
            // increase balance
            $this->db->set("credit_balance", "credit_balance + " . $product['norm_value'])->where('name', $username)->update('agent');
        }
        if ($product['subcate'] == 910) {
            // upgrade agent
            $from = $product['province'] % 10;
            $to = int($product['province'] / 10);
            if ($user['level'] != $from) {
                return 'INVALID_CURRENT_LEVEL';
            }
            $this->db->update('agent', array('level' => $to), array('name' => $username));
        }
    }
    else {
        $this->db->insert('job', array(
            'create_time' => time(),
            'commit_time' => 0,
            'ticket_number' => $ticket['number'],
            'to' => $values['to'],
            'area' => $values['area'],
            'product_id' => $product['id'],
            'locking_on' => NULL,
            'retried' => 0,
            'result' => 0,
            'reason' => NULL
        ));
    }
    return 'SUCCESS';
}

function make_job($ticket, $values) {
    $CI =& get_instance();
    $product = $ticket['product'];
    $to = $values['to'];
    if ($product['cate'] == 1 && (!is_numeric($to) || strlen($to) != 11)) {
        return 'INVALID_NUMBER';
    }
    if ($product['cate'] == 2 && (!is_numeric($to) || strlen($to) < 5)) {
        return 'INVALID_NUMBER';
    }
    if ($product['cate'] == 3 && !$to) {
        return 'INVALID_NUMBER';
    }
    return create_job($ticket, $values);
}

/* End of file */