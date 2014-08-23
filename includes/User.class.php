<?php
class User {
	
	private $id = null;
	private $name;
	private $surname;
	private $classe;
	
	public function __construct($id, $name, $surname, $classe)
	{
		$this->id = (int)$id;
		$this->name = $name;
		$this->surname = $surname;
		$this->classe = $classe;
	}
	
	public function id() {
		return (int)$this->id;
	}
	
	public function name() {
		return $this->name;
	}
	
	public function surname() {
		return $this->surname;
	}
	
	public function fullName() {
		return $this->name() . ' ' . $this->surname();
	}
	
	public function classe() {
		return $this->classe;
	}
	
}
	
?>