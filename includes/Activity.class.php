<?php
class Activity {
	
	private $id = null;
	private $block;
	private $title;
	private $size;
	private $vm;
	private $description;
	private $prenotati;
	
	public function __construct($id, $block, $title, $size, $vm, $description, $prenotati)
	{
		$this->id = (int)$id;
		$this->block = $block;
		$this->title = $title;
		$this->size = (int)$size;
		$this->vm = (bool)$vm;
		$this->description = $description;
		$this->prenotati = $prenotati;
	}
	
	public function id() {
		return $this->id;
	}
	
	public function title() {
		return $this->title;
	}
	
	public function size() {
		return $this->size;
	}
	
	public function block() {
		return $this->block;
	}
	
	public function vm() {
		return $this->vm;
	}
	
	public function description() {
		return $this->description;
	}
	
	public function prenotati() {
		return $this->prenotati;
	}
	
	public function full() {
		if($this->size != 0 && $this->prenotati >= $this->size) {
			return true;
		}
		return false;
	}
	
}
	
?>