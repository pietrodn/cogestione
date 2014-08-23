<?php
class Classe {
	
	private $id = null;
	private $year;
	private $section;
	
	public function __construct($id, $year, $section)
	{
		$this->id = intval($id);
		$this->year = intval($year);
		$this->section = $section;
	}
	
	public function id() {
		return $this->id;
	}
	
	public function year() {
		return $this->year;
	}
	
	public function section() {
		return $this->section;
	}
	
	public function name() {
		return strval($this->year) . $this->section;
	}
	
}
	
?>