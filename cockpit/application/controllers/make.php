<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Make extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('ticket');
        $this->load->helper('misc');
        $this->load->helper('user');
        $this->load->helper('product');
        $this->user = get_user();
        if (!$this->user) {
            die;
        }
    }

    public function index() {
        // list products and prices
        $cates = make_dict($this->db->get('cate')->result_array());
        $subcates = make_dict($this->db->get('subcate')->result_array());
        $products = $this->db->get('product')->result_array();
        $price = make_dict($this->db->get_where('price', array('agent_level' => $this->user['level']))->result_array(), 'product_id');
        $tree = array();
        foreach ($products as $product) {
            $cate_id = $product['cate'];
            $subcate_id = $product['subcate'];
            if (!isset($tree[$cate_id])) {
                $tree[$cate_id] = $cates[$cate_id];
                $tree[$cate_id]['subcates'] = array();
            }
            if (!isset($tree[$cate_id]['subcates'][$subcate_id])) {
                $tree[$cate_id]['subcates'][$subcate_id] = $subcates[$subcate_id];
                $tree[$cate_id]['subcates'][$subcate_id]['products'] = array();
            }
            $product['stock_price'] = $price[$product['id']]['stock_price'];
            $tree[$cate_id]['subcates'][$subcate_id]['products'][$product['id']] = $product;
        }
        $this->load->vars('tree', $tree);
        $this->load->view('make');
    }

	public function create($product, $product_count, $ticket_count) {
        $product = $this->db->get_where('product', array('id' => $product))->row_array();
        if ($product_count > $product['count_max'] || $product_count < $product['count_min']) {
            echo json_encode(array('success' => 0, 'max' => $product['count_max'], 'min' => $product['count_min']));
            return;
        }
        $tickets = array();
        for ($i = 0; $i < $ticket_count; $i ++) {
            $number = new_ticket_number($product['id']);
            $price = get_price($product['id'], $this->user['level']);
            $this->db->insert('ticket', array(
                "number" => $number,
                "create_time" => time(),
                "credit" => $price * $product_count,
                "product_id" => $product['id'],
                "count" => $product_count,
                "state" => 0,
                "fulfill_time" => 0,
                "agent" => $this->user['id']
            ));
            $tickets[] = $number;
        }
        echo json_encode(array('success' => 1, 'tickets' => $tickets));
	}

}

/* End of file */