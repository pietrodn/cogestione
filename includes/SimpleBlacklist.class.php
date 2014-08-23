<?php

require_once('Blacklist.class.php');

class SimpleBlacklist extends Blacklist {
	
	public function check($token){
		// Returns FALSE if token is bad, TRUE if it's good.
		foreach($this->bad_list as $bad_word) {
			if(stripos($token, $bad_word) !== FALSE) {
				return FALSE;
			}
		}
		return TRUE;
	}
}

?>