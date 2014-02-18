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
        $this->db->trans_begin();
        $ticket = $this->read_ticket($number, TRUE);
        if ($ticket) {
            $to = $this->input->get_post('to');
            $area = $this->input->get_post('area');
            $this->db->where('number', $number)->update('ticket', array('state' => 1, 'fulfill_time' => time()));
            $this->db->insert('job', array(
                'create_time' => time(),
                'commit_time' => 0,
                'ticket_number' => $ticket['number'],
                'to' => $to,
                'area' => $area,
                'locking_on' => NULL,
                'retried' => 0,
                'result' => 0,
                'reason' => NULL
            ));
            $success = TRUE;
        }
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $success = FALSE;
        } else {
            $this->db->trans_commit();
        }
        echo json_encode(array('success' => $success));
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

    private function get_fields($product_id, $subcate) {
        $fields = array();
        $areas = $this->db->select(array('id', 'name'))->get_where('area', array('product_id' => $product_id, 'state' => 0))->result_array();
        if (count($areas) > 0) {
            $fields['area'] = array(
                'text' => '服务区',
                'type' => 'select',
                'items' => $areas
            );
        }
        $subcate = $this->db->get_where('subcate', array('id' => $subcate))->row_array();
        $fields['to'] = array(
            'text' => $subcate['target_name'],
            'type' => 'text'
        );
        return $fields;
    }

    private function read_ticket($number, $lock = FALSE) {
        $this->load->helper('ticket');
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
        $ticket['fields'] = $this->get_fields($product['id'], $product['subcate']);
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