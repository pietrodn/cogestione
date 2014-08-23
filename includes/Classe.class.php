<?php
class Classe {
	
	private $id = null;
	private $year;
	private $section;
	
	public function __construct($id, $year, $section)
	{
		$this->id = (int)$id;
		$this->year = (int)$year;
		$this->section = $section;
	}
	
	public function id() {
		return (int)$this->id;
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
	
	public static function parseClass($cl_name) {
		$cl = new Classe(null, intval($cl_name), substr($cl_name, 1));
		return $cl;
	}
}
	
?>