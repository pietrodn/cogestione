<?php
require_once('config.php');

function isEnabled() {
	/* This function tells if the application is enabled for inserts. */
	
	/* Checks manual override */
	if(COGE_MANUAL) {
		return COGE_MANUAL_ENABLED;
	}
	
	/* Ore di inizio e di fine */
	$dtz = new DateTimeZone('Europe/Rome');
	$beginTime = new DateTime(START_TIME, $dtz);
	$endTime = new DateTime(END_TIME, $dtz);

	$now = new DateTime(null, $dtz);
	
	return ($now >= $beginTime AND $now <= $endTime);
}

function initDB() {
	/* Loads database login credentials.
	   Variabili necessarie:
		$db_host = MySQL host
		$db_name = database name
		$db_user = user name
		$db_password = password
	*/
			
	// Inits the database
	$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$db->set_charset('utf8');
	if(!$db) {
		printError('Errore nella connessione al database!');
		die();
	}
	return $db;
}

function blocchi($db) {
	static $blocks = FALSE; // Cache
	if($blocks === FALSE) {
		// Ottiene id e nome dei blocchi come array associativo.
		$res = $db->query("SELECT * FROM block ORDER BY block_id;");
		if(!$res) {
			printError("Errore nella selezione dei blocchi!");
			die();
		}
		$blocks = Array();
		while($row = $res->fetch_assoc()) {
			$blocks[$row['block_id']] = $row['block_title'];
		}
	}
    return $blocks;
}

function getActivityInfo($db, $id) {
    /* Ottiene title, time e n. prenotati di una data attività.
        Restituisce una riga siffatta:
        (id, time, max, title, vm, prenotati)
    */
    $res = $db->query('SELECT activity.*, COUNT(prenact_id) AS prenotati
                            FROM activity
                            LEFT JOIN prenotazioni_attivita
                            ON activity_id=prenact_activity
                            WHERE activity_id=' . intval($id) . '
                            GROUP BY activity_id;');
    if(!$res) {
    	printError("Error while fetching activity info!");
    	die();
    }
    $row = $res->fetch_assoc();
    return $row;
}


function classi($db) {
	static $classi = FALSE; // Cache
	if($classi === FALSE) {
		// Ottiene l'array delle classi.
		$res = $db->query("SELECT * FROM class ORDER BY class_name;");
		if(!$res) {
			printError("Error while selecting classes!");
			die();
		}
		$classi = Array();
		while($row = $res->fetch_assoc()) {
			$classi[] = $row['class_name'];
		}
	}
    return $classi;
}

function isSubscribed($db, $name, $surname, $class) {
    // Has the user already subscribed?
    $res = $db->query('SELECT user_id
                            FROM user
                            WHERE user_name="' . $db->real_escape_string($name) . '"
                            AND user_surname="' . $db->real_escape_string($surname) . '"
                            AND user_class="' . $db->real_escape_string($class) . '";');
    if(!$res) {
    	printError('Errore nella ricerca di una persona!');
    	die();
    }
    $n = $res->num_rows;
    return ( $n ? TRUE : FALSE );
}

function getSubscriptionsNumber($db)
{
	// Gets the total number of subscriptions.
	$res = $db->query('SELECT pren_id FROM prenotazioni;');
	if(!$res) {
		printError("Errore nell'ottenere il numero di prenotazioni!");
		die();
	}
	return $res->num_rows;
}

function getTotalSeats($db)
{
	// Total number of seats.
	$res = $db->query('SELECT MIN(time_seats) AS m FROM
		(SELECT SUM(activity_size) AS time_seats, activity_time
		FROM activity
		GROUP BY activity_time) AS tmp;');
	if(!$res) {
		printError("Errore nell'ottenere il numero di posti totali!");
		die();
	}
	$row = $res->fetch_assoc();
	return intval($row['m']);
}

function lastID($db)
{
	// Ottiene l'ultimo ID
	$res = $db->query('SELECT MAX(activity_id) AS m FROM activity;');
	if(!$res) {
		printError("Errore nell'ottenere l'ultimo ID");
		die();
	}
	$row = $res->fetch_assoc();
	return intval($row['m']);
}

function getActivitiesForBlock($db, $blk) {
	/* 	Returns an array of activities in the specified block.
		Each row contains:
		* activity_title
		* activity_time
		* activity_description
		* prenotati
		* activity_vm
		* activity_size
	*/
	$rows = [];
	$res = $db->query('SELECT activity.*, COUNT(prenact_id) AS prenotati
		FROM activity
		LEFT JOIN prenotazioni_attivita ON activity_id=prenact_activity
		WHERE activity_time=' . intval($blk) . '
		GROUP BY activity_id
		ORDER BY activity_id;');
	
	while($row = $res->fetch_assoc()) {
		$rows[] = $row;
	}
	return $rows;
}

function inserisciPrenotazione($db, $name, $surname, $class, $prenotazione) {
	/* $prenotazione array associativo "id blocco" => "id attività" */
	
	// Escaping
	$name = $db->real_escape_string($name);
	$surname = $db->real_escape_string($surname);
	$class = $db->real_escape_string($class);
	// Inserimento dati
	
	$res = $db->query("INSERT INTO user (user_name, user_surname, user_class)
		VALUES ('$name', '$surname', '$class');");
	if(!$res) {
		printError("Errore nell'inserimento della prenotazione!");
		die();
	}
		
	$user_id = $db->insert_id;
	$res = $db->query("INSERT INTO prenotazioni (pren_user)
		VALUES ('$user_id');");
	if(!$res) {
		printError("Errore nell'inserimento della prenotazione!");
		die();
	}
	$pren_id = $db->insert_id;
	
	foreach($prenotazione as $blocco_id => $attivita_id) {
		$blocco_id = intval($blocco_id);
		$attivita_id = intval($attivita_id);
		
		$res = $db->query("INSERT INTO prenotazioni_attivita (prenact_prenotation, prenact_activity)
			VALUES ('$pren_id', '$attivita_id');");
		if(!$res) {
			printError("Errore nell'inserimento della prenotazione!");
			die();
		}
	}
}

function getReservationsForUser($db, $userId) {
	$prenotazione = $db->query("SELECT activity_title, activity_time
		FROM activity
		LEFT JOIN prenotazioni_attivita
		ON prenact_activity = activity_id
		LEFT JOIN prenotazioni
		ON prenact_prenotation=pren_id
		WHERE pren_user = " . intval($userId) . "
		ORDER BY activity_time;");
	
	$activities = [];
	while($p = $prenotazione->fetch_assoc()) {
		$activities[$p['activity_time']] = $p['activity_title'];
	}
	return $activities;
}

function getUsersForActivity($db, $activity_id) {
	$res = $db->query("SELECT user_name, user_surname, user_class
			FROM prenotazioni_attivita
			LEFT JOIN prenotazioni
			ON prenact_prenotation=pren_id
			LEFT JOIN user
			ON user_id=pren_user
			WHERE prenact_activity = " . intval($activity_id) . "
			ORDER BY pren_timestamp;");
								
	$users = [];
	while($row = $res->fetch_assoc()) {
		$users[] = $row;
	}
	
	return $users;
}

function findUser($db, $user_name, $user_surname, $user_class) {
	$conditions = Array();
	if(!empty($user_name)) {
		$name = $db->real_escape_string($user_name);
		$conditions[] = "user_name=\"$name\"";
	}
	if(!empty($user_surname)) {
		$surname = $db->real_escape_string($user_surname);
		$conditions[] = "user_surname=\"$surname\"";
	}
	if(!empty($user_class)) {
		$class = $db->real_escape_string($user_class);
		$conditions[] = "user_class=\"$class\"";
	}
	$conditionString = implode($conditions, ' AND ');
	$res = $db->query("SELECT DISTINCT user_id, user_name, user_surname, user_class
		FROM user
		WHERE $conditionString
		ORDER BY CONCAT(user_surname, user_name);");
	$users = [];
	while($row = $res->fetch_assoc()) {
		$users[] = $row;
	}
	
	return $users;
}

function printError($message) {
	echo '<p class="error">' . htmlentities($message) . '</p>';
}

?>