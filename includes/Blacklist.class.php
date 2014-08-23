<?php

abstract class Blacklist {
	protected $bad_list;
	
	function __construct($list) {
		$this->bad_list = $list;
	}
	
	// Returns FALSE if token is bad, TRUE if it's good.
	public abstract function check($token);
	
	public function checkMultiple($tokens) {
		foreach($tokens as $tok) {
			if(!$this->check($tok)) {
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	/* Checks a Blacklistable object */
	public function checkObject($object) {
		$tokens = $object->blacklistTokens();
		return $this->checkMultiple($tokens);
	}
	
	public function getList() {
		return $this->bad_list;
	}
}

?>