<?php

require_once('Blacklist.class.php');

class RegexBlacklist extends Blacklist {
	
	public function check($token){
		// Returns FALSE if token is bad, TRUE if it's good.
		foreach($this->bad_list as $regex) {
			$regex = '/' . trim($regex) . '/i'; // case insensitive
			if(preg_match($regex, $token) === 1) {
				return FALSE;
			}
		}
		return TRUE;
	}
}

?>