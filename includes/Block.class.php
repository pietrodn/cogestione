<?php
class Block {
	
	private $id = null;
	private $title;
	
	public function __construct($id, $title)
	{
		$this->id = (int)$id;
		$this->title = $title;
	}
	
	public function id() {
		return (int)$this->id;
	}
	
	public function title() {
		return $this->title;
	}
	
}
	
?>