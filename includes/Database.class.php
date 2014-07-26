<?php
class Database {
	static protected $instance = null;
	private $db = null;
	
	private function __construct() {
		
		/* Loads database login credentials.
		   Variabili necessarie:
			$db_host = MySQL host
			$db_name = database name
			$db_user = user name
			$db_password = password
		*/
		global $db_host, $db_user, $db_password, $db_name;
		// Inits the database
		$this->db = new mysqli($db_host, $db_user, $db_password, $db_name) or die($this->db->error);
		$this->db->set_charset('utf8');
	}
	
	function __destruct() {
		$this->db->close();
	}
	
	public static function database() {
		if(self::$instance === null) {
			self::$instance = new Database();
		}
		
		return self::$instance;
	}
	
	public function query($query) {
		// Extreme debugging
		// echo $query;
		$res = $this->db->query($query) or die($this->db->error);
		
		if($res === TRUE || $res === FALSE) {
			return $res;
		} else {
			$rows = Array();
			while($row = $res->fetch_assoc()) {
				$rows[]=$row;
			}
			$res->free();
			return $rows;
		}
	}
	
	public function escape($str) {
		return $this->db->real_escape_string($str);
	}
	
	public function insert_id() {
		return $this->db->insert_id;
	}
}

?>