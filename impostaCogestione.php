<?php
require("nav.php");
$css = Array('includes/StiliCogestione.css');
$js = Array('http://code.jquery.com/jquery-1.10.2.min.js', 'includes/imposta.js');

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
	
	/* DEBUG! Unsecure! */
	// $validated=TRUE;
	
	if($validated) {
		/* Registro i dati... */
		$activities = $bl = $deleteAct = Array();
		foreach($_POST['activity'] as $act) {
			if(!empty($act['id'])) {
				/*echo "2";*/
				$id = intval($act['id']);
				$activities[$id]['block'] = intval($act['block']);
				$activities[$id]['max'] = intval($act['max']);
				$activities[$id]['title'] = $db->real_escape_string(stripslashes(htmlspecialchars_decode($act['title'], ENT_QUOTES)));
				$activities[$id]['vm'] = intval(!empty($act['vm']));
				if(!empty($act['delete']))
					$deleteAct[] = $id;
			}
		}
		
		foreach($_POST['block'] as $b) {
			if(!empty($b['id'])) {
				$id = intval($b['id']);
				$bl[$id] = $db->real_escape_string(stripslashes(htmlspecialchars_decode($b['title'], ENT_QUOTES)));
			}
		}
		
		
		// Delete activities
		if(count($deleteAct)) {
			$deleteString = '(' . implode(', ', $deleteAct) . ')';
			$query = "DELETE FROM attivita
					WHERE id IN $deleteString;";
			$res = $db->query($query);
			if(!$res) die("Problem1!");
		}
		
		// Inserimento dati. $activities clean for MySQL
		foreach($activities as $k => $in) {
			if(in_array($k, $deleteAct))
				continue;
			$query = "REPLACE INTO attivita (id, time, max, title, vm) VALUES ("
			. "'" . $k . "', "
			. "'" . $in['block'] . "', "
			. "'" . $in['max'] . "', "
			. "'" . $in['title'] . "', "
			. "'" . $in['vm'] . "'"
			. ');';
			$res = $db->query($query);
			if(!$res) die("Problem2!");
		}
		
		foreach($bl as $k => $v) {
			$query = "REPLACE INTO blocchi (id, title) VALUES ("
			. "'" . $k . "', "
			. "'" . $v . "'"
			. ');';
			$res = $db->query($query);
			if(!$res) die("Problem3!");
		}
			
		echo "<p class=\"error\">I dati sono stati registrati con successo.</p>";
		
	
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
<!--
<fieldset style="width:50%;">
<label>Modalità abilitazione form:</label>
<table id="modeTable">
<tr>
<td style="width:70%;"><input type="radio" name="enableMode" value="auto" />Automatico</td>
<td style="width:30%;"><input type="radio" name="enableMode" value="manual" />Manuale</td>
</tr>
<tr>
<td>Inizio:
<input type="text" name="beginYear" id="beginYear" size="4" placeholder="YYYY" />-<input type="text" name="beginMonth" id="beginMonth" size="2" placeholder="MM" />-<input type="text" name="beginDay" id="beginDay" size="2" placeholder="DD" />
<input type="text" name="beginHour" id="beginHour" size="2" placeholder="HH" />:<input type="text" name="beginMinute" id="beginMinute" size="2" placeholder="MM" />
</td>
<td rowspan="2"><input type="checkbox" name="manualEnabled" />Abilita</td>
</tr>
<tr>
<td>Fine:
<input type="text" name="endYear" id="endYear" size="4" placeholder="YYYY" />-<input type="text" name="endMonth" id="endMonth" size="2" placeholder="MM" />-<input type="text" name="endDay" id="endDay" size="2" placeholder="DD" />
<input type="text" name="endHour" id="endHour" size="2" placeholder="HH" />:<input type="text" name="endMinute" id="endMinute" size="2" placeholder="MM" />
</td>
</tr>
</table>
</fieldset>
-->
<fieldset id="truncateField" style="width:50%; min-height:50px; padding:10px;">
<label style="width:50%; display:block; float:left;">Se vuoi cancellare le prenotazioni effettuate, conferma.
I dati non potranno essere recuperati.</label>
<input type="checkbox" name="confermaTruncate" value="Cancella prenotazioni" />
</fieldset>

<?php      
/* Stampa la griglia */
echo '<table id="ActivityTable" class="wideTable">';
/* Intestazione con blocchi */
/* Ottiene i nomi delle colonne (blocchi) */
$blocks = blocchi($db);
echo '<tr>';
foreach($blocks as $i => $b) {
	echo "\n<th>"
		. "<input type=\"text\" size=\"1\" name=\"block[$i][id]\" id=\"block-id-$i\" value=\"$i\" />"
		. "<input type=\"text\" size=\"35\" name=\"block[$i][title]\" id=\"block-title-$i\" value=\"". htmlspecialchars($b, ENT_QUOTES, "UTF-8", false) . "\" /></th>";
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
		echo "\n<div class=\"activity\" id=\"activity-$id\">\n"
			. "<input type=\"hidden\" name=\"activity[$id][id]\" value=\"$id\" />\n"
			. "<input type=\"hidden\" name=\"activity[$id][block]\" value=\"$i\" />\n"
			. "<span class=\"id-box\" id=\"activity-id-$id\">$id</span>\n"
			. "<input type=\"text\" size=\"35\" id=\"activity-title-$id\" name=\"activity[$id][title]\" value=\"$title\" "
			. "id=\"activity-title-$id\" />\n"
			. "<input type=\"text\" size=\"1\" id=\"activity-max-$id\" name=\"activity[$id][max]\" value=\""
			. $row['max'] . "\" />\n"
			. "<input id=\"activity-vm-$id\" name=\"activity[$id][vm]\" type=\"checkbox\" "
			. ($row['vm'] ? 'checked="checked"' : '')
			. "/><label for=\"activity-vm-$id\">VM18</label>"
			. "<input class=\"notnew\" id=\"activity-delete-$id\" name=\"activity[$id][delete]\" type=\"checkbox\" />"
			. "<label class=\"notnew\" for=\"activity-delete-$id\">DEL</label>"
			. "\n</div>\n";
	}
	echo '<a class="addButton" id="addToBlock-' . $i . '">Aggiungi</a>';
	echo '</td>';
}
echo "</tr></table>\n";
echo '<input type="submit" name="confermaTutto" value="Salva modifiche orario" />' . "\n";
echo "</form>\n";
echo '<div class="hidden" id="lastID">' . lastID($db) . '</div>';

showFooter('ca-nstab-imposta');
$db->close();
?>