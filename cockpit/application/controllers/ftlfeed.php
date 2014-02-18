<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ftlfeed extends CI_Controller {

	private function response($success, $data = NULL) {
		$this->output->set_content_type('application/json');
		if (!$success) {
			$this->output->set_output(json_encode(array('success' => 0, 'error' => $data)));
		} else {
			$this->output->set_output(json_encode(array('success' => 1, 'response' => $data)));
		}
	}

	private function cleanslate() {
		$this->db->where('locking_on', $this->instance)->update('job', array('locking_on' => NULL));
		$this->response(TRUE);
	}

	private function pull() {
		$error = NULL;
		$this->db->trans_begin();
		$job = $this->db->where('locking_on IS NULL')->where('result', 0)
			->lock(LOCK_EXCLUSIVE)->limit(1)->get('job')->row_array();
		if (count($job) > 0) {
			$job['ticket'] = $this->db->where('number', $job['ticket_number'])->get('ticket')->row_array();
			$this->db->where('id', $job['id'])->update('job', array('locking_on' => $this->instance));
		}
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$this->response(FALSE, 'Transaction failed.');
		} else {
			$this->db->trans_commit();
			$this->response(TRUE, $job);
		}
	}

	private function drop() {
		$this->id = $this->input->get_post('id');
		if (!$this->id) {
			$this->response(FALSE, 'No id specified.');
			return;
		}
		$this->db->where('locking_on', $this->instance)
			->where('id', $this->id)->update('job', array('locking_on' => NULL));
		if ($this->db->affected_rows() > 0) {
			$this->response(TRUE);
		} else {
			$this->response(FALSE, "No object updated.");
		}
	}

	private function commit() {
		$this->id = $this->input->get_post('id');
		$this->result = $this->input->get_post('result');
		$this->retried = $this->input->get_post('retried');
		$this->reason = $this->input->get_post('reason');
		if (!$this->id) {
			$this->response(FALSE, 'No id specified.');
			return;
		}
		if ($this->result === FALSE) {
			$this->response(FALSE, 'No result specified.');
			return;
		}
		if ($this->retried === FALSE) {
			$this->response(FALSE, 'No retried specified.');
			return;
		}
		$this->db->where('locking_on', $this->instance)
			->where('id', $this->id)
			->update('job', array(
				'locking_on' => NULL, 'commit_time' => time(), 
				'result' => $this->result, 'retried' => $this->retried, 'reason' => $this->reason));
		if ($this->db->affected_rows() > 0) {
			$this->response(TRUE);
		} else {
			$this->response(FALSE, "No object updated. (" . $this->instance . ")");
		}
	}

	public function api($action) {
		$this->instance = $this->input->get_post('instance');
		if (!$this->instance) {
			$this->response(FALSE, 'No instance specified.');
			return;
		}
		if (!method_exists($this, $action)) {
			$this->response(FALSE, 'Method not found.');
			return;
		}
		$this->$action();
	}

}

/* End of file */