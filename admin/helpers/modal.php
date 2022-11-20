<?php


class modalHelper {
	
	public $size;
	public $name;
	public $title;
	public $buttons;
	
	public $unique;

	private $_vars;
	private $_body;
	
	public function __construct($vars) {
		$this->_vars = $vars;
	}
	
	public function setBody($file) {
		ob_start();
		include $file;
		$this->body = ob_get_contents();
		ob_end_clean();
	}
	
	public function execute() {
		echo $this->body;
		exit;
	}
	
}
