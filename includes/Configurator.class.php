<?php

require_once('includes/RegexBlacklist.class.php');

class Configurator {

	private $manualMode = TRUE;
	private $manualOn = FALSE;
	private $coge_users = Array();
	private $startTime;
	private $endTime;
	private $blacklist;
	
	private $db;
	
	static protected $instance = null;
	
	private function __construct() {
		/* Loads parameters from configuration file */
		require(CONFIG_FILE);
		
		$this->db = Database::database();
		
		$this->coge_users = $cgUsers;
		$this->loadFromDb();
	}
	
	private function loadFromDb() {
		$res = $this->db->query("SELECT * FROM config;");
		$kvConf = Array();
		foreach($res as $row) {
			$kvConf[$row['config_key']] = $row['config_value'];
		}
		
		if(isset($kvConf['manualMode'])) {
			$this->manualMode = (bool)$kvConf['manualMode'];
		}
		if(isset($kvConf['manualOn'])) {
			$this->manualOn = (bool)$kvConf['manualOn'];
		}
		if(isset($kvConf['startTime'])) {
			$this->startTime = $kvConf['startTime'];
		}
		if(isset($kvConf['endTime'])) {
			$this->endTime = $kvConf['endTime'];
		}
		if(isset($kvConf['blacklist'])) {
			$this->blacklist = new RegexBlacklist(
				explode("\n", $kvConf['blacklist'])
			);
		}
	
	}
	
	public static function configurator() {
		if(self::$instance === null) {
			self::$instance = new Configurator();
		}
		
		return self::$instance;
	}
	
	public function isAuthenticated($user, $pass) {
		foreach($this->coge_users as $couple) {
			if($user === $couple['user'] && $pass === $couple['pass'])
				return TRUE;
		}
		return FALSE;
	}
	
	public function isEnabled() {
		/* This function tells if the application is enabled for inserts. */
	
		/* Checks manual override */
		if($this->manualMode) {
			return $this->manualOn;
		}
	
		/* Ore di inizio e di fine */
		$dtz = new DateTimeZone('Europe/Rome');
		$beginTime = new DateTime($this->startTime, $dtz);
		$endTime = new DateTime($this->endTime, $dtz);

		$now = new DateTime(null, $dtz);
	
		return ($now >= $beginTime AND $now <= $endTime);
	}
	
	private function saveToDb($key, $value) {
		$this->db->query("REPLACE config SET
							config_value = '" . $this->db->escape($value) . "', 
							config_key = '" . $this->db->escape($key) . "'");
	}
	
	public function getStartTime() {
		return $this->startTime;
	}
	
	public function getEndTime() {
		return $this->endTime;
	}
	
	public function getManualMode() {
		return $this->manualMode;
	}
	
	public function getManualOn() {
		return $this->manualOn;
	}
	
	public function getBlacklist() {
		return $this->blacklist;
	}
	
	public function setManualMode($on) {
		if($on != $this->manualMode) {
			$this->manualMode = (bool)$on;
			$this->saveToDb('manualMode', intval($this->manualMode));
		}
	}
	
	public function setManualOn($on) {
		if($on != $this->manualOn) {
			$this->manualOn = (bool)$on;
			$this->saveToDb('manualOn', intval($this->manualOn));
		}
	}
	
	public function setStartTime($time) {
		if($time != $this->startTime) {
			$this->startTime = $time;
			$this->saveToDb('startTime', $this->startTime);
		}
	}
	
	public function setEndTime($time) {
		if($time != $this->endTime) {
			$this->endTime = $time;
			$this->saveToDb('endTime', $this->endTime);
		}
	}
	
	public function setBlacklist($black) {
		if($black != $this->blacklist) {
			$this->blacklist = $black;
			$this->saveToDb('blacklist', implode("\n", $this->blacklist->getList()));
		}
	}

}
?>