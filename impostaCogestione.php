<?php
require_once("functions.php");
require("nav.php");
$css = Array('css/StiliCogestione.css');
$js = Array('http://code.jquery.com/jquery-1.10.2.min.js');

showHeader("Impostazioni cogestione", $css, $js);

$db = initDB();

$validated = FALSE;

if(isset($_POST['confermaTutto'])) {
	if(authenticated($coge_users)) {
		$activities = $bl = $deleteAct = $deleteBlocks = Array();
		
		// Escaping dati attività
		foreach($_POST['activity'] as $act) {
			if(!empty($act['id'])) {
				$id = intval($act['id']);
				$activities[$id]['block'] = intval($act['block']);
				$activities[$id]['max'] = intval($act['max']);
				$activities[$id]['title'] = $db->real_escape_string(htmlspecialchars_decode($act['title'], ENT_QUOTES));
				$activities[$id]['vm'] = intval(!empty($act['vm']));
				$activities[$id]['description'] = $db->real_escape_string(htmlspecialchars_decode($act['description'], ENT_QUOTES));
				if(!empty($act['delete']))
					$deleteAct[] = $id;
			}
		}
		
		// Escaping dati blocchi
		foreach($_POST['block'] as $b) {
			if(!empty($b['id'])) {
				$id = intval($b['id']);
				$bl[$id]['title'] = $db->real_escape_string(htmlspecialchars_decode($b['title'], ENT_QUOTES));
				$bl[$id]['newRows'] = intval($b['newRows']);
				if(!empty($b['delete']))
					$deleteBlocks[] = $id;
			}
		}
		
		// Cancella le attività da cancellare.
		deleteActivities($deleteAct, $db);
		
		// Modifica dati attività.
		foreach($activities as $id => $in) {
			if(in_array($id, $deleteAct))
				continue;
			replaceActivity($db, $id, $in['block'], $in['max'], $in['title'], $in['vm'], $in['description']);
		}
		
		// Cancella i blocchi da cancellare
		deleteBlocks($deleteBlocks, $db);
		
		// Modifica dati blocchi
		foreach($bl as $k => $b) {
			if(in_array($k, $deleteBlocks))
				continue;
			replaceBlock($db, intval($k), $b['title']);
			
			// Nuove righe attività
			if($b['newRows']>0) {
				addNewActivities($b['newRows'], $k, $db);
			}
		}
		
		// Nuovi blocchi
		$newBlocks = intval($_POST['newBlocks']);
		addNewBlocks($newBlocks, $db);
		
		// Cleanup (order *is* important)
		cleanOrphanActivities($db);
		cleanOrphanPrenotations($db);
			
		printError('I dati sono stati registrati con successo.');
		
		if(isset($_POST['confermaTruncate'])) {
			clearReservations();
			echo printError('Prenotazioni cancellate.');
		}
	} else {
		printError('Autenticazione fallita! Capra!');
		echo '<img src="http://www.controcopertina.com/wp-content/uploads/2012/09/sgarbi-vittorio-foto.png" alt="Sgarbi insulta" width="300" />';
	}
}    
?>
<div id="desc">
Cambia le impostazioni della cogestione utilizzando il form sottostante.
Le modifiche saranno applicate soltanto dopo aver confermato cliccando sul pulsante <b>Salva modifiche orario</b> in fondo alla pagina.

<p>
Per <b>aggiungere un nuovo blocco o una nuova attività</b> occorre dunque:
</p>
<ol>
	<li>incrementare gli appositi contatori;</li>
	<li>salvare le modifiche;</li>
	<li>modificare i dati dei nuovi elementi creati.</li>
</ol>
<p>
Per <b>cancellare un blocco o un'attività</b>, spuntare la casella <b>"DEL"</b> relativa e poi confermare. Saranno automaticamente cancellate:
</p>
<ol>
	<li>le attività non appartenenti ad alcun blocco;</li>
	<li>le prenotazioni non riferite ad un blocco esistente;</li>
	<li>le prenotazioni non riferite ad un'attività esistente.</li>
</ol>
<p>
Per segnare un'attività come <b>riservata alle quarte o alle quinte</b>, spuntare la casella <b>"VM18"</b> relativa e poi confermare.
</p>
<p>
Per motivi di coerenza dei dati, è consigliabile azzerare le prenotazioni dopo aver modificato le attività.
</p>
</div>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<fieldset style="width:50%;">
<p>Per effettuare modifiche al software devi autenticarti.</p>
<label for="username">Username: </label><input type="text" name="username" id="username" size="20" placeholder="utente" /><br />
<label for="password">Password: </label><input type="password" name="password" id="password" size="20" placeholder="password" />
</fieldset>
<fieldset id="truncateField" style="width:50%; min-height:50px; padding:10px;">
<label style="width:50%; display:block; float:left;">
<?php
	echo 'Ci sono <b>' . getSubscriptionsNumber($db) . ' prenotazioni</b> effettuate.';
?> Se vuoi cancellarle, spunta la casella.
I dati non potranno essere recuperati.</label>
<input type="checkbox" name="confermaTruncate" value="Cancella prenotazioni" />
</fieldset>

<?php
// New blocks
echo '<label>Aggiungi <input type="number" min="0" name="newBlocks" value="0" /> nuovi blocchi</label>';

/* Stampa la griglia */
echo '<table id="ActivityTable" class="wideTable">';
/* Intestazione con blocchi */
/* Ottiene i nomi delle colonne (blocchi) */
$blocks = blocchi($db);
echo '<tr>';
foreach($blocks as $id => $b) {
	$id = intval($id);
	echo "\n<th>"
		. "<input type=\"hidden\" name=\"block[$id][id]\" value=\"$id\" />\n"
		. "<input type=\"text\" size=\"35\" name=\"block[$id][title]\" id=\"block-title-$id\" value=\"". htmlspecialchars($b, ENT_QUOTES, "UTF-8", false) . "\" />"
		. "<br /><input type=\"checkbox\" id=\"block-delete-$id\" name=\"block[$id][delete]\" />"
		. "<label for=\"block-delete-$id\">DEL</label>"
		. "</th>";
}
echo "\n</tr><tr>";
/* Procede colonna per colonna */
foreach($blocks as $i => $b) {
	echo '<td id="block-' . $i . '">';
	$activities = getActivitiesForBlock($db, $i);
	
	/* Stampa tutte le attività che si svolgono contemporaneamente */
	foreach($activities as $row) {
		$title = htmlspecialchars($row['activity_title'], ENT_QUOTES, "UTF-8", false);
		$id = $row['activity_id'];
		$placeholder = htmlspecialchars('Descrizione per "' . $row['activity_title'] . '"');
		echo "\n<div class=\"set-activity\" id=\"activity-$id\">\n"
			. "<input type=\"hidden\" name=\"activity[$id][id]\" value=\"$id\" />\n"
			. "<input type=\"hidden\" name=\"activity[$id][block]\" value=\"$i\" />\n"
			. "<input type=\"text\" class=\"activity-set-title\" id=\"activity-title-$id\" name=\"activity[$id][title]\" value=\"$title\" /><br />\n"
			. "<input type=\"number\" min=\"0\" id=\"activity-max-$id\" name=\"activity[$id][max]\" value=\""
			. intval($row['activity_size']) . "\" />\n"
			. "<input id=\"activity-vm-$id\" name=\"activity[$id][vm]\" type=\"checkbox\" "
			. ($row['activity_vm'] ? 'checked="checked"' : '')
			. "/><label for=\"activity-vm-$id\">VM18</label>"
			. "<input id=\"activity-delete-$id\" name=\"activity[$id][delete]\" type=\"checkbox\" />"
			. "<label for=\"activity-delete-$id\">DEL</label>"
			. "<textarea rows=\"4\" name=\"activity[$id][description]\" placeholder=\"$placeholder\">" . htmlspecialchars($row['activity_description']) . "</textarea>"
			. "\n</div>\n";
	}
	echo '</td>';
}
echo '</tr><tr>';
foreach($blocks as $i => $title) {
	echo '<td>';
	echo '<label>Aggiungi <input type="number" min="0" name="block[' . intval($i) . '][newRows]" value="0" /> nuove attività</label>';
	echo '</td>';
}

echo "</tr></table>\n";
echo '<input type="submit" name="confermaTutto" value="Salva modifiche orario" />' . "\n";
echo "</form>\n";

showFooter('ca-nstab-imposta');
$db->close();

function addNewActivities($n, $blk, $db) {
	// Adds $n new activities for block $blk.
	$query = "INSERT INTO activity (activity_time, activity_size, activity_title, activity_vm) VALUES ";
	$defaultRecord = "(" . intval($blk) . "," . "0,'Titolo',0)";
	for($i=0; $i<$n; $i++) {
		$query .= $defaultRecord . ','; // Multiple rows
	}
	$query = rtrim($query, ','); // Remove last comma
	$res = $db->query($query);
	if(!$res) die("Problem while adding new activities!");
}

function addNewBlocks($n, $db) {
	// Adds $n new blocks
	$defaultRecord = "('Nuovo blocco')";
	
	if($n > 0) {
		$query = "INSERT INTO block (block_title) VALUES ";
		for($i=0; $i<$n; $i++) {
			$query .= $defaultRecord . ','; // Multiple rows
		}
		$query = rtrim($query, ','); // Remove last comma
		$res = $db->query($query);
		if(!$res) die("Problem while adding $n new blocks!");
	}
}

function cleanOrphanActivities($db) {
	// Elimina le attività che non si trovano in nessun blocco
	$res = $db->query('DELETE FROM activity
		WHERE activity_time NOT IN (
			SELECT DISTINCT block_id
			FROM block );');
	if(!$res) die("Problem while cleaning orphan activities!");
}

function deleteBlocks($ids, $db) {
	// Cancella i blocchi da cancellare
	if(count($ids)>0) {
		$deleteString = '(' . implode(', ', $ids) . ')';
		$query = "DELETE FROM block
				WHERE block_id IN $deleteString;";
		$res = $db->query($query);
		if(!$res) die("Problem while deleting blocks $deleteString!");
	}
}

function deleteActivities($ids, $db) {
	// Cancella le attività da cancellare
	if(count($ids)>0) {
		$deleteString = '(' . implode(', ', $ids) . ')';
		$query = "DELETE FROM activity
				WHERE activity_id IN $deleteString;";
		$res = $db->query($query);
		if(!$res) die("Problem while deleting activities $deleteString!");
	}
}

function authenticated($users) {
	foreach($users as $k) {
		if($_POST['username'] == $k['user'] && $_POST['password'] == $k['pass']) {
			return TRUE;
		}
	}
	return FALSE;
}

function replaceActivity($db, $act_id, $act_time, $act_size, $act_title, $act_vm, $act_description) {
	// Replaces the activity associated with the id $act_id with the new values.
	$query = "REPLACE INTO activity (activity_id, activity_time, activity_size, activity_title, activity_vm, activity_description) VALUES ("
		. $act_id . ','
		. $act_time . ','
		. $act_size . ','
		. "'" . $act_title . "', "
		. $act_vm . ','
		. "'" . $act_description . "'"
		. ');';
		$res = $db->query($query);
		if(!$res) die("Problem while REPLACEing an activity!");
}

function replaceBlock($db, $blk_id, $blk_title) {
	// Replaces the title of block $blk_id.
	$query = "REPLACE INTO block (block_id, block_title) VALUES ("
		. "'" . intval($blk_id) . "', "
		. "'" . $blk_title . "'"
		. ');';
	$res = $db->query($query);
	if(!$res) die("Problem while replacing a block!");
}

function clearReservations($db) {
	$db->query("TRUNCATE TABLE prenotazioni;");
	$db->query("TRUNCATE TABLE prenotazioni_attivita;");
}

function cleanOrphanPrenotations($db) {
	/* TODO!
	// Elimina le prenotazioni riferite ad attività o blocchi inesistenti
	$res = $db->query('DELETE FROM prenotazioni
		WHERE time NOT IN (
			SELECT DISTINCT id
			FROM blocchi )
		OR activity NOT IN (
			SELECT DISTINCT id
			FROM attivita );');
	if(!$res) die("Problem while cleaning orphan prenotations!");
	*/
}
?>