<?php

abstract class ListView {
	
	protected $list = Array();
	
	public function __construct($list) {
		$this->list = $list;
	}
	
	public function getList() {
		return $this->list;
	}
	
	public function setList($list) {
		$this->list = $list;
	}
	
	public abstract function render();

}

?>