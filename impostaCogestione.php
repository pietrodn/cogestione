<?php
require("nav.php");
$css = Array('includes/StiliCogestione.css');
$js = Array('http://code.jquery.com/jquery-1.10.2.min.js');

showHeader("Impostazioni cogestione", $css, $js);

$db = initDB();

$validated = FALSE;

if(isset($_POST['confermaTutto'])) {
	foreach($coge_users as $k) {
		if($_POST['username'] == $k['user'] && $_POST['password'] == $k['pass']) {
			$validated=TRUE;
			break;
		}
	}
	
	if($validated) {
		// Escaping dati attività
		$activities = $bl = $deleteAct = $deleteBlocks = Array();
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
				$bl[$id]['title'] = $db->real_escape_string(stripslashes(htmlspecialchars_decode($b['title'], ENT_QUOTES)));
				$bl[$id]['newRows'] = intval($b['newRows']);
				if(!empty($b['delete']))
					$deleteBlocks[] = $id;
			}
		}
		
		// Cancella le attività da cancellare
		if(count($deleteAct)>0) {
			$deleteString = '(' . implode(', ', $deleteAct) . ')';
			$query = "DELETE FROM attivita
					WHERE id IN $deleteString;";
			$res = $db->query($query);
			if(!$res) die("Problem1!");
		}
		
		// Modifica dati attività.
		foreach($activities as $k => $in) {
			if(in_array($k, $deleteAct))
				continue;
			$query = "REPLACE INTO attivita (id, time, max, title, vm, description) VALUES ("
			. $k . ','
			. $in['block'] . ','
			. $in['max'] . ','
			. "'" . $in['title'] . "', "
			. $in['vm'] . ','
			. "'" . $in['description'] . "'"
			. ');';
			$res = $db->query($query);
			if(!$res) die("Problem2!");
		}
		
		// Cancella i blocchi da cancellare
		if(count($deleteBlocks)>0) {
			$deleteString = '(' . implode(', ', $deleteBlocks) . ')';
			$query = "DELETE FROM blocchi
					WHERE id IN $deleteString;";
			$res = $db->query($query);
			if(!$res) die("Problem3!");
		}
		
		// Modifica dati blocchi
		foreach($bl as $k => $b) {
			if(in_array($k, $deleteBlocks))
				continue;
			$query = "REPLACE INTO blocchi (id, title) VALUES ("
			. "'" . $k . "', "
			. "'" . $b['title'] . "'"
			. ');';
			$res = $db->query($query);
			if(!$res) die("Problem4!");
			
			// Nuove righe attività
			if($b['newRows']>0) {
				$query = "INSERT INTO attivita (time, max, title, vm) VALUES ";
				$defaultRecord = "(" . $k . "," . "0,'Titolo',0)";
				for($i=0; $i<$b['newRows']; $i++) {
					$query .= $defaultRecord . ','; // Multiple rows
				}
				$query = rtrim($query, ','); // Remove last comma
				$res = $db->query($query);
				if(!$res) die("Problem5!");
			}
		}
		
		// Nuovi blocchi
		$newBlocks = intval($_POST['newBlocks']);
		if($newBlocks > 0) {
			$query = "INSERT INTO blocchi (title) VALUES ";
			$defaultRecord = "('Nuovo blocco')";
			for($i=0; $i<$newBlocks; $i++) {
				$query .= $defaultRecord . ','; // Multiple rows
			}
			$query = rtrim($query, ','); // Remove last comma
			$res = $db->query($query);
			if(!$res) die("Problem6!");
		}
		
		// Elimina le attività che non si trovano in nessun blocco
		$res = $db->query('DELETE FROM attivita
			WHERE time NOT IN (
				SELECT DISTINCT id
				FROM blocchi );');
		if(!$res) die("Problem7!");
			
		echo '<p class="error">I dati sono stati registrati con successo.</p>';
		
	
		if(isset($_POST['confermaTruncate'])) {
			$db->query("TRUNCATE TABLE prenotazioni;");
			echo '<p class="error">Prenotazioni cancellate.</p>';
		}
	} else {
		echo '<p class="error">Autenticazione fallita! Capra!</p>';
		echo '<img src="http://www.controcopertina.com/wp-content/uploads/2012/09/sgarbi-vittorio-foto.png" alt="Sgarbi insulta" width="300" />';
	}
}    
?>

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
?> Se vuoi cancellarle, conferma.
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
	$res = $db->query('SELECT attivita.*, COUNT(prenotazioni.id) AS prenotati
						FROM attivita
						LEFT JOIN prenotazioni ON attivita.id=prenotazioni.activity
						WHERE attivita.time=' . $i . '
						GROUP BY attivita.id
						ORDER BY attivita.id;');
	
	/* Stampa tutte le attività che si svolgono contemporaneamente */
	while($row = $res->fetch_assoc()) {
		$title = htmlspecialchars($row['title'], ENT_QUOTES, "UTF-8", false);
		$id = $row['id'];
		$placeholder = htmlspecialchars('Descrizione per "' . $row['title'] . '"');
		echo "\n<div class=\"set-activity\" id=\"activity-$id\">\n"
			. "<input type=\"hidden\" name=\"activity[$id][id]\" value=\"$id\" />\n"
			. "<input type=\"hidden\" name=\"activity[$id][block]\" value=\"$i\" />\n"
			. "<input type=\"text\" class=\"activity-set-title\" id=\"activity-title-$id\" name=\"activity[$id][title]\" value=\"$title\" "
			. "id=\"activity-title-$id\" /><br />\n"
			. "<input type=\"number\" min=\"0\" id=\"activity-max-$id\" name=\"activity[$id][max]\" value=\""
			. intval($row['max']) . "\" />\n"
			. "<input id=\"activity-vm-$id\" name=\"activity[$id][vm]\" type=\"checkbox\" "
			. ($row['vm'] ? 'checked="checked"' : '')
			. "/><label for=\"activity-vm-$id\">VM18</label>"
			. "<input id=\"activity-delete-$id\" name=\"activity[$id][delete]\" type=\"checkbox\" />"
			. "<label for=\"activity-delete-$id\">DEL</label>"
			. "<textarea rows=\"4\" name=\"activity[$id][description]\" placeholder=\"$placeholder\">" . htmlspecialchars($row['description']) . "</textarea>"
			. "\n</div>\n";
	}
	echo '</td>';
}
echo '</tr><tr>';
foreach($blocks as $i => $title) {
	echo '<td>';
	echo '<label>Aggiungi <input type="number" min="0" name="block[' . $i . '][newRows]" value="0" /> nuove attività</label>';
	echo '</td>';
}

echo "</tr></table>\n";
echo '<input type="submit" name="confermaTutto" value="Salva modifiche orario" />' . "\n";
echo "</form>\n";

showFooter('ca-nstab-imposta');
$db->close();
?>