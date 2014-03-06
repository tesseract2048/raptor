<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ticket extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('ticket');
    }

    public function index() {
        $this->load->view('fulfill');
    }

    public function fulfill() {
        $number = $this->input->get_post('number');
        $success = FALSE;
        $reason = 'NO_ERROR';
        $this->db->trans_begin();
        $ticket = $this->read_ticket($number, TRUE);
        if ($ticket) {
            $values = array();
            foreach ($ticket['fields'] as $key=>$field) {
                $values[$key] = $this->input->get_post($key);
            }
            $ret = make_job($ticket, $values);
            if ($ret == 'SUCCESS') {
                $this->db->where('number', $number)->update('ticket', array('state' => 1, 'fulfill_time' => time()));
                $success = TRUE;
            } else {
                $reason = $ret;
            }
        }
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $success = FALSE;
            $reason = 'TRANSACTION_FAILED';
        } else {
            $this->db->trans_commit();
        }
        echo json_encode(array('success' => $success, 'reason' => $reason));
    }

	public function create($product, $count) {
        $number = new_ticket_number($product);
        $product = $this->db->get_where('product', array('id' => $product))->row_array();
        $this->db->insert('ticket', array(
            "number" => $number,
            "create_time" => time(),
            "credit" => $product['norm_value'] * $count,
            "product_id" => $product['id'],
            "count" => $count,
            "state" => 0,
            "fulfill_time" => 0,
            "agent" => 0
        ));
		echo json_encode(array('product' => $product, 'number' => $number));
	}

    private function read_ticket($number, $lock = FALSE) {
        $this->load->helper('ticket');
        $this->load->helper('product');
        if (!valid_ticket_number($number)) {
            return FALSE;
        }
        if ($lock) {
            $this->db->lock(LOCK_EXCLUSIVE);
        }
        $ticket = $this->db->get_where('ticket', array('number' => $number))->row_array();
        if (count($ticket) == 0) {
            return FALSE;
        }
        if ($ticket['state'] != 0) {
            return FALSE;
        }
        $product = $this->db->get_where('product', array('id' => $ticket['product_id']))->row_array();
        $ticket['product'] = $product;
        $ticket['fields'] = get_product_fields($product);
        return $ticket;
    }

	public function valid($number) {
        $ticket = $this->read_ticket($number);
        if (!$ticket) {
            echo json_encode(array('valid' => FALSE));
            return;
        }
        echo json_encode(array(
            'valid' => TRUE, 'fields' => $ticket['fields'], 
            'name' => $ticket['product']['name'],
            'count' => $ticket['count'] * $ticket['product']['norm_value'],
            'scale' => $ticket['product']['scale']
        ));
	}

}

/* End of file */