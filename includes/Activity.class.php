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
		return (int)$this->id;
	}
	
	public function title() {
		return $this->title;
	}
	
	public function size() {
		return (int)$this->size;
	}
	
	public function block() {
		return $this->block;
	}
	
	public function vm() {
		return (bool)$this->vm;
	}
	
	public function description() {
		return $this->description;
	}
	
	public function prenotati() {
		return (int)$this->prenotati;
	}
	
	public function full() {
		if($this->size != 0 && $this->prenotati >= $this->size) {
			return TRUE;
		}
		return FALSE;
	}
	
	public function okForClass($class) {
		$year = $class->year();
		if($this->vm() == 1 && $year != 4 && $year != 5) {
            return FALSE;
        }
        return TRUE;
	}
	
}
	
?>