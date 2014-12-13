<?php
	error_reporting(E_ALL);
	define('CONFIG_FILE', 'config.php');
	if(file_exists(CONFIG_FILE)) {
		include_once(CONFIG_FILE);
	}
	
	require_once("includes/Configurator.class.php");
	require_once("includes/Cogestione.class.php");
	require_once("functions.php");
	require_once("nav.php");
	
	session_start();	
?>