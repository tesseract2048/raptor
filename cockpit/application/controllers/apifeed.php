<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Apifeed extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('Chongzhi');
        $this->chongzhi->username('');
    }

    private function map_array($type, $row) {
        $mapping = array(
            "product" => array(
                "i" => "id", 
                "b" => "name", 
                "f" => "norm_value", 
                "u" => "scale", 
                "t" => "subcate", 
                "c" => "channel", 
                "v" => "province", 
                "q" => "cate", 
                "s" => "inventory", 
                "o" => "order", 
                "m" => function(&$arr, $val) {
                    $cols = explode("-", $val);
                    $arr['count_min'] = $cols[0];
                    $arr['count_max'] = $cols[1];
                }, 
                "p16" => "stock_price"
            ),
            "product_channel" => array(
                "i" => "id", 
                "c" => "name", 
                "r" => "comment"
            ),
            "product_area" => array(
                "i" => "product_id", 
                "t" => "name", 
                "v" => "id"
            )
        );
        $mapped = array();
        foreach ($row as $k => $v) {
            $map = $mapping[$type][$k];
            if (is_callable($map)) {
                $map($mapped, $v);
            } else {
                $mapped[$map] = $v;
            }
        }
        return $mapped;
    }

    public function product() {
        $product = $this->chongzhi->product();
        foreach ($product['list'] as $rows) {
            foreach ($rows as $row) {
                $mapped_row = $this->map_array('product', $row);
                $this->db->replace('product', $mapped_row);
            }
        }
    }

    public function channel() {
        $channel = $this->chongzhi->product_channel();
        foreach ($channel['list'] as $rows) {
            foreach ($rows as $row) {
                $mapped_row = $this->map_array('product_channel', $row);
                $this->db->replace('channel', $mapped_row);
            }
        }
    }

    public function area() {
        $area = $this->chongzhi->product_area();
        foreach ($area['list'] as $rows) {
            foreach ($rows as $row) {
                $mapped_row = $this->map_array('product_area', $row);
                $this->db->replace('area', $mapped_row);
            }
        }
    }

}

/* End of file */