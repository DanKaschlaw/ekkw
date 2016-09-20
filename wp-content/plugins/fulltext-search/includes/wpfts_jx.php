<?php

class WPFTS_jxResponse {
	
	protected $xresponse = array();
	
	function console($msg) {
		$this->xresponse[] = array('cn', $msg);
	}
	
	function alert($msg) {
		$this->xresponse[] = array('al', $msg);
	}

	function assign($id, $data) {
		$this->xresponse[] = array('as', $id, $data);
	}
	
	function redirect($url = '', $delay = 0) {
		$this->xresponse[] = array('rd', $url, $delay);
	}
	
	function reload(){
		$this->xresponse[] = array('rl');
	}
	
	function script($script = '') {
		$this->xresponse[] = array('js', $script);
	}
	
	function variable($var, $value) {
		$this->xresponse[] = array('vr', $var, $value);
	}
	
	function setResponse($a) {
		$this->xresponse = $a;
	}
	
	function getJSON() {
		return json_encode($this->xresponse);
	}
	
	function getData() {
		if ((isset($_POST['__xr'])) && ($_POST['__xr'] == 1)) {
			$post = isset($_POST['z']) ? json_decode(stripslashes($_POST['z']), true) : array();
			return $post;
		} else {
			return false;
		}
	}
}
