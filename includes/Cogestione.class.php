<?php
include('Database.class.php');

class Cogestione {
	private $db;
	private $blocks = null; // Block cache
	private $classi = null; // Class cache
	private $activityInfo = Array();
	
	function __construct() {
		$this->db = Database::database();
	}
	
	public function blocchi() {
		if($this->blocks === null) {
			// Ottiene id e nome dei blocchi come array associativo.
			$arr = $this->db->query("SELECT * FROM block ORDER BY block_id;");
			$this->blocks = Array();
			foreach($arr as $row) {
				$this->blocks[$row['block_id']] = $row['block_title'];
			}
		}
		return $this->blocks;
	}
	
	public function classi() {
		/* Returns an array like this: {class_id => [class_year, class_section]} */
		if($this->classi === null) {
			// Ottiene l'array delle classi.
			$res = $this->db->query("SELECT * FROM class ORDER BY class_year, class_section;");
			$this->classi = Array();
			foreach($res as $row) {
				$this->classi[$row['class_id']] = Array(
					'class_year' => intval($row['class_year']),
					'class_section' => $row['class_section'],
					'class_name' => strval(intval($row['class_year'])) . $row['class_section'],
				);
			}
		}
		return $this->classi;
	}
	
	public function getClassInfo($id) {
		/* Ottiene anno e sezione della classe con id $id.
		*/
		$this->classi(); // Sets the internal array if necessary
		return $this->classi[$id];
	}
	
	public function getActivityInfo($id) {
		/* Ottiene title, time e n. prenotati di una data attività.
			Restituisce una riga siffatta:
			(id, time, max, title, vm, prenotati)
		*/
		if(!array_key_exists($id, $this->activityInfo)) {
			$res = $this->db->query('SELECT activity.*, COUNT(prenact_id) AS prenotati,
									(activity_size != 0 AND COUNT(prenact_id)>=activity_size) AS full
									FROM activity
									LEFT JOIN prenotazioni_attivita
									ON activity_id=prenact_activity
									WHERE activity_id=' . intval($id) . '
									GROUP BY activity_id;');
			$this->activityInfo[$id] = $res[0];
		}
		return $this->activityInfo[$id];
	}

	public function isSubscribed($name, $surname, $class_id) {
		// Has the user already subscribed?
		$res = $this->db->query('SELECT user_id
								FROM user
								WHERE user_name="' . $this->db->escape($name) . '"
								AND user_surname="' . $this->db->escape($surname) . '"
								AND user_class="' . $this->db->escape($class_id) . '";');
		$n = count($res);
		return ( $n ? TRUE : FALSE );
	}

	public function getSubscriptionsNumber()
	{
		// Gets the total number of subscriptions.
		$res = $this->db->query('SELECT pren_id FROM prenotazioni;');
		return count($res);
	}

	public function getTotalSeats()
	{
		// Total number of seats.
		$res = $this->db->query('SELECT MIN(time_seats) AS m FROM
			(SELECT SUM(activity_size) AS time_seats, activity_time
			FROM activity
			GROUP BY activity_time) AS tmp;');
		return intval($res[0]['m']);
	}

	public function lastID()
	{
		// Ottiene l'ultimo ID
		$res = $this->db->query('SELECT MAX(activity_id) AS m FROM activity;');
		$row = $res->fetch_assoc();
		return intval($row['m']);
	}

	public function getActivitiesForBlock($blk) {
		/* 	Returns an array of activities in the specified block.
			Each row contains:
			* activity_title
			* activity_time
			* activity_description
			* prenotati
			* activity_vm
			* activity_size
		*/
		
		$res = $this->db->query('SELECT activity.*, COUNT(prenact_id) AS prenotati
			FROM activity
			LEFT JOIN prenotazioni_attivita ON activity_id=prenact_activity
			WHERE activity_time=' . intval($blk) . '
			GROUP BY activity_id
			ORDER BY activity_id;');
	
		return $res;
	}

	public function inserisciPrenotazione($name, $surname, $class_id, $prenotazione) {
		/* $prenotazione array associativo "id blocco" => "id attività" */
	
		// Escaping
		$name = $this->db->escape($name);
		$surname = $this->db->escape($surname);
		$class_id = intval($class_id);
		// Inserimento dati
	
		$res = $this->db->query("INSERT INTO user (user_name, user_surname, user_class)
			VALUES ('$name', '$surname', $class_id);");
		
		$user_id = $this->db->insert_id();
		$res = $this->db->query("INSERT INTO prenotazioni (pren_user)
			VALUES ('$user_id');");
		$pren_id = $this->db->insert_id();
	
		foreach($prenotazione as $blocco_id => $attivita_id) {
			$blocco_id = intval($blocco_id);
			$attivita_id = intval($attivita_id);
		
			$res = $this->db->query("INSERT INTO prenotazioni_attivita (prenact_prenotation, prenact_activity)
				VALUES ('$pren_id', '$attivita_id');");
		}
		
		$res = $this->db->query("UPDATE user SET
			user_pren_latest = " . $pren_id . "
			WHERE user_id = " . $user_id . ";");
	}

	public function getReservationsForUser($userId) {
		$prenotazione = $this->db->query("SELECT activity_title, activity_time
			FROM activity
			LEFT JOIN prenotazioni_attivita
			ON prenact_activity = activity_id
			LEFT JOIN prenotazioni
			ON prenact_prenotation=pren_id
			WHERE pren_user = " . intval($userId) . "
			ORDER BY activity_time;");
	
		$activities = [];
		foreach($prenotazione as $p) {
			$activities[$p['activity_time']] = $p['activity_title'];
		}
		return $activities;
	}

	public function getUsersForActivity($activity_id) {
		$res = $this->db->query("SELECT user_name, user_surname, user_class, CONCAT(class_year, class_section) AS class_name
				FROM prenotazioni_attivita
				LEFT JOIN prenotazioni ON prenact_prenotation=pren_id
				LEFT JOIN user ON user_id=pren_user
				LEFT JOIN class ON user_class = class_id
				WHERE prenact_activity = " . intval($activity_id) . "
				ORDER BY pren_timestamp;");
								
		return $res;
	}

	public function findUser($user_name, $user_surname, $user_class) {
		$conditions = Array();
		if(!empty($user_name)) {
			$name = $this->db->escape($user_name);
			$conditions[] = "user_name=\"$name\"";
		}
		if(!empty($user_surname)) {
			$surname = $this->db->escape($user_surname);
			$conditions[] = "user_surname=\"$surname\"";
		}
		if(!empty($user_class)) {
			$class = intval($user_class);
			$conditions[] = "user_class=$class";
		}
		$conditionString = implode($conditions, ' AND ');
		$res = $this->db->query("SELECT DISTINCT user_id, user_name, user_surname, user_class, CONCAT(class_year, class_section) AS class_name
			FROM user
			LEFT JOIN class ON user_class = class_id
			WHERE $conditionString
			ORDER BY CONCAT(user_surname, user_name);");
	
		return $res;
	}
	
	public function getUser($user_id) {
		$res = $this->db->query("SELECT DISTINCT user_id, user_name, user_surname, user_class, CONCAT(class_year, class_section) AS class_name
			FROM user
			LEFT JOIN class ON user_class = class_id
			WHERE user_id = " . intval($user_id) . "
			LIMIT 1;");
		if(count($res) == 0) {
			return FALSE;
		}
		return $res[0];
	}
	
	public function activityOkForClass($activity_id, $class_id) {
		$act_info = $this->getActivityInfo($activity_id);
		$class_info = $this->getClassInfo($class_id);
		$year = $class_info['class_year'];
		if($act_info['activity_vm'] == 1 && $year != 4 && $year != 5) {
            return FALSE;
        }
        return TRUE;
	}

	public function addNewActivities($n, $blk) {
		// Adds $n new activities for block $blk.
		$query = "INSERT INTO activity (activity_time, activity_size, activity_title, activity_vm) VALUES ";
		$defaultRecord = "(" . intval($blk) . "," . "0,'Titolo',0)";
		for($i=0; $i<$n; $i++) {
			$query .= $defaultRecord . ','; // Multiple rows
		}
		$query = rtrim($query, ','); // Remove last comma
		$res = $this->db->query($query);
		return $res;
	}

	public function addNewBlocks($n) {
		// Adds $n new blocks
		$defaultRecord = "('Nuovo blocco')";
	
		if($n > 0) {
			$query = "INSERT INTO block (block_title) VALUES ";
			for($i=0; $i<$n; $i++) {
				$query .= $defaultRecord . ','; // Multiple rows
			}
			$query = rtrim($query, ','); // Remove last comma
			$res = $this->db->query($query);
			return $res;
		}
	}

	public function deleteBlocks($ids) {
		if(count($ids)>0) {
			$deleteString = '(' . implode(', ', $ids) . ')';
			$query = "DELETE FROM block
					WHERE block_id IN $deleteString;";
			$res = $this->db->query($query);
			return $res;
		}
	}

	public function deleteActivities($ids) {
		if(count($ids)>0) {
			$deleteString = '(' . implode(', ', $ids) . ')';
			$query = "DELETE FROM activity
					WHERE activity_id IN $deleteString;";
			$res = $this->db->query($query);
			return $res;
		}
	}

	public function updateActivity($act_id, $act_time, $act_size, $act_title, $act_vm, $act_description) {
		// Replaces the activity associated with the id $act_id with the new values.
		$query = "UPDATE activity SET "
			. 'activity_time = ' . intval($act_time) . ', '
			. 'activity_size = ' . intval($act_size) . ', '
			. "activity_title = '" . $this->db->escape($act_title) . "', "
			. 'activity_vm = ' . intval($act_vm) . ', '
			. "activity_description = '" . $this->db->escape($act_description) . "' "
			. ' WHERE activity_id = ' . intval($act_id) . ';';
		$res = $this->db->query($query);
		return $res;
	}

	public function updateBlock($blk_id, $blk_title) {
		// Replaces the title of block $blk_id.
		$query = "UPDATE block SET "
			. "block_title = '" . $this->db->escape($blk_title) . "' "
			. "WHERE block_id = " . intval($blk_id)
			. ';';
		$res = $this->db->query($query);
		return $res;
	}

	public function clearReservations() {
		// Deletes all rows. Doesn't use TRUNCATE TABLE because of foreign keys.
		// This also deletes rows from "prenotazioni" and "prenotazioni_attivita", automatically.
		$res = $this->db->query("DELETE FROM user;");
		return $res;
	}
	
	public function deleteUser($uid) {
		$res = $this->db->query("DELETE FROM user
								WHERE user_id = " . intval($uid) . ";");
		return $res;
	}
	
	public function setClasses($classes_arr) {
		/* 	This function updates the class table with the classes in $classes_arr.
			$classes_arr is an array of arrays [class_year, class_section] */
		
		// Removes deleted classes
		$toDelete = Array();
		foreach($this->classi() as $cl_id => $cl_val) {
			$needle = Array($cl_val['class_year'], $cl_val['class_section']);
			if(!array_search($needle, $classes_arr)) {
				$toDelete[] = intval($cl_id);
			}
		}
		if(count($toDelete)) {
			$query = "DELETE FROM class WHERE class_id IN (" . implode(',', $toDelete) . ');';
			$this->db->query($query);
		}
		
		// Insert new classes
		$query = "INSERT IGNORE INTO class
			(class_year, class_section)
			VALUES ";
		$tuples = Array();
		foreach($classes_arr as $cl_val) {
			$cl_year = intval($cl_val[0]);
			$cl_section = $this->db->escape($cl_val[1]);
			$tuples[] = "($cl_year, '$cl_section')";
		}
		$query .= implode(', ', $tuples);
		$query .= ';';
		$res = $this->db->query($query);
		
		$this->classi = null; // resets cache
		
	}
	
	public function deleteClass($cl_id) {
		/* This function deletes a single class with id $cl_id. */
		
		$this->classi = null; // resets cache
		
		$res = $this->db->query("DELETE FROM class WHERE class_id = " . intval($cl_id) . ";");
		return $res;
	}

}
?>