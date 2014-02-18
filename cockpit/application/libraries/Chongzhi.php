<?php

class CI_Chongzhi {

	var $api_version = 3;
	var $api_url = "http://api.chongzhi.com";

	public function username($username) {
		$this->username = $username;
	}

	public function key($key) {
		$this->key = $key;
	}

	private function sign($name, $args, $with_key = false) {
		$keys = array();
		foreach ($args as $k => $v) {
			$keys[] = $k;
		}
		sort($keys);
		$str = "";
		foreach ($keys as $k) {
			if ($str) {
				$str .= "&";
			}
			$str .= $k . "=" . $args[$k];
		}
		$key = 'sign';
		if ($with_key) {
			$str .= "&" . $this->key;
			$key = 'signkey';
		}
		$args[$key] = md5(urlencode($name . "?" . $str));
		return $args;
	}

	private function call($name, $args, $sign_mode = 0) {
        $args['username'] = $this->username;
        $args['timestamp'] = time();
        $args['ver'] = $this->api_version;
        $args['format'] = 'json';
        if ($sign_mode) {
	        $args = $this->sign($name, $args, $sign_mode == 2);
	    }
	    $query_string = "";
	    foreach ($args as $k => $v) {
	    	$query_string .= $k . "=" . urlencode($v) . "&";
	    }
	    $resp = file_get_contents($this->api_url . $name . "?" . $query_string);
	    $obj = json_decode($resp, TRUE);
	    if (!isset($obj['sududa'])) {
	    	return NULL;
	    }
	    return $obj['sududa'];
	}

	public function product() {
		return $this->call('/api/product', array('power' => 16), 1);
	}

	public function product_channel() {
        return $this->call('/api/product_channel', array(), 1);
	}

	public function product_area() {
        return $this->call('/api/product_area', array(), 1);
	}

	public function sys_phone($phone) {
        return $this->call('/api/sys_phone', array('phone' => $phone), 1);
	}

}

/* End of file */
