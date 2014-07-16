<?php
class Configurator {

	private $manualMode = TRUE;
	private $manualOn = FALSE;
	private $coge_users = Array();
	private $startTime;
	private $endTime;
	
	static protected $instance = null;
	
	private function __construct() {
		/* Loads parameters from configuration file */
		require('config.php');
		
		$this->manualMode = $cgManual;
		$this->manualOn = $cgManualEnabled;
		$this->startTime = $cgStartTime;
		$this->endTime = $cgEndTime;
		$this->coge_users = $cgUsers;
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
	
	public function getStartTime() {
		return $this->startTime;
	}
	
	public function getEndTime() {
		return $this->endTime;
	}

}
?>