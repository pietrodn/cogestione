<?php
require("nav.php");
$css = Array('includes/StiliCogestione.css', 'includes/elenchi.css');
showHeader("Elenco prenotazioni cogestione", $css);

$db = initDB();

// MAIN
$studentiTot = 1040;

$blocks = blocchi($db);
$classi = classi($db);
$validated = FALSE;

$nPrenot = getSubscriptionsNumber($db);
echo '<p class="noprint">Numero di prenotazioni: ' . $nPrenot . ' (' . round($nPrenot/$studentiTot*100) . '% degli studenti)</p>';

// Cerca studente
echo '<h2 class="noprint">Cerca uno studente</h2>';
echo '<form class="noprint" action="'. $_SERVER['PHP_SELF'] . '" method="get">
	<table id="fieldTable">
	<tr><td><label for="name">Nome: </label></td>
	<td><input class="iField" type="text" name="name" id="name" placeholder="Mario" ' .
	(!empty($_GET['name']) ? 'value="' . htmlspecialchars($_GET['name']) . '" ' : '') .
	'/></td></tr>
	<tr><td><label for="surname">Cognome: </label></td>
	<td><input class="iField" type="text" name="surname" id="surname" placeholder="Rossi" ' .
	(!empty($_GET['surname']) ? 'value="' . htmlspecialchars($_GET['surname']) . '" ' : '') .
	'/></td></tr>
	<tr><td><label for="class">Classe: </label></td><td><select class="iField" name="class" id="class"><option value="" disabled selected>Seleziona la classe</option>';
	
// Selettore classe	   
foreach($classi as $cl) {
	if(isset($_GET['class']) && $cl == $_GET['class'])
		$selected = 'selected';
	else
		$selected = '';
	echo "\n<option value=\"$cl\" $selected>$cl</option>";
}		
		
echo "\n</select></td></tr>";
echo '<tr><td colspan="2"><input id="submit" type="submit" name="cercastud" value="Cerca" /></td></tr>';
echo "</table>\n</form>\n";
			
if(isset($_GET['activity'])) // Se si seleziona un'attività
{
	// Visualizza elenco partecipanti
	$activity = $db->real_escape_string($_GET['activity']);
	$aRow = getActivityInfo($activity, $db);
	echo "\n<h2>" . $blocks[$aRow['time']] . ' – ' . $aRow['title'] . '</h2>';
	echo "\n<div id=\"output\">\nAttività: <b>" . $aRow['title']
		. "</b>.\n<br />Quando: <b>" . $blocks[$aRow['time']]
		. "</b>\n<br />Partecipanti: <b>" . $aRow['prenotati'] . ($aRow['max'] ? '/' . $aRow['max'] : '') . '</b>';
	
	if($aRow['prenotati']>0)
	{
		$res = $db->query("SELECT *
								FROM prenotazioni
								WHERE activity=$activity
								ORDER BY timestamp;");
								
		echo "<br />Elenco dei partecipanti:\n<ol id=\"partecipanti\">";
		while($row = $res->fetch_assoc()) {
			echo "\n<li>" . htmlspecialchars($row['surname']) . ' ' . htmlspecialchars($row['name']) . ' (' . $row['class'] . ") </li>";
		}
		echo "\n</ol>";
		
	} else {
		echo "<br/>Nessun partecipante!";
	}
	echo "\n</div>";
	
} else if(isset($_GET['cercastud'])
	AND (isset($_GET['name']) OR isset($_GET['surname']) OR isset($_GET['class']))) {
		
	// Se si cerca uno studente
	
	$conditions = Array();
	if(!empty($_GET['name'])) {
		$name = $db->real_escape_string($_GET['name']);
		$conditions[] = "prenotazioni.name=\"$name\"";
	}
	if(!empty($_GET['surname'])) {
		$surname = $db->real_escape_string($_GET['surname']);
		$conditions[] = "prenotazioni.surname=\"$surname\"";
	}
	if(!empty($_GET['class'])) {
		$class = $db->real_escape_string($_GET['class']);
		$conditions[] = "prenotazioni.class=\"$class\"";
	}
	$conditionString = implode($conditions, ' AND ');
	
	$studenti = $db->query("SELECT DISTINCT name, surname, class FROM prenotazioni
		WHERE $conditionString
		ORDER BY prenotazioni.timestamp;");
	
	if($studenti->num_rows) {
		$riepilogo = '';
		$riepilogo .= '<table id="ActivityTable">';
		$riepilogo .= '<tr><th>Nome</th><th>Cognome</th><th>Classe</th>';
		foreach($blocks as $b) {
			$riepilogo .= "\n<th>$b</th>";
		}
		$riepilogo .= "\n</tr>";
		while($row = $studenti->fetch_assoc()) {
			$riepilogo .= "\n<tr>";
			$riepilogo .= "\n<td>" . htmlspecialchars($row['name']) . '</td>';
			$riepilogo .= "\n<td>" . htmlspecialchars($row['surname']) . '</td>';
			$riepilogo .= "\n<td>" . htmlspecialchars($row['class']) . '</td>';
			
			$studentName = $db->real_escape_string($row['name']);
			$studentSurname = $db->real_escape_string($row['surname']);
			$studentClass = $db->real_escape_string($row['class']);
			
			$prenotazione = $db->query("SELECT attivita.title AS title
				FROM attivita, prenotazioni
				WHERE prenotazioni.activity = attivita.id
				AND prenotazioni.name=\"$studentName\"
				AND prenotazioni.surname=\"$studentSurname\"
				AND prenotazioni.class=\"$studentClass\"
				ORDER BY attivita.time;");
			
			while($p = $prenotazione->fetch_assoc()) {
				$riepilogo .= "\n<td><div class=\"activity\">" . htmlspecialchars($p['title']) . '</div></td>';
			}
		}
		$riepilogo .= '</tr></table>';
		echo $riepilogo;
	} else {
		echo '<p class="error">Nessuno studente trovato!</p>';
	}
}
			
// Selettore attività

echo '<h2 class="noprint">Cerca un\'attività</h2>';
echo '<table id="ActivityTable" class="noprint">';
echo '<tr>';
foreach($blocks as $b) {
	echo "\n<th>$b</th>";
}
echo "\n</tr><tr>";
foreach($blocks as $i => $b) {
	echo '<td>';
	$res = $db->query('SELECT attivita.*, COUNT(prenotazioni.id) AS prenotati
						FROM attivita
						LEFT JOIN prenotazioni ON attivita.id=prenotazioni.activity
						WHERE attivita.time=' . $i . '
						GROUP BY attivita.id
						ORDER BY attivita.id;');
	while($row = $res->fetch_assoc()) {
		$url = $_SERVER['PHP_SELF'] . '?activity=' . $row['id'];
		echo "\n<div class=\"activity\"><span class=\"posti\">[" . $row['prenotati'] . ($row['max']!=0?'/' . $row['max']:'') . "]</span> <a href=\"" . $url . "\">" . $row['title'] . '</a></div>';
	}
	
	echo '</td>';
}
echo '</tr></table>';
showFooter('ca-nstab-elenchi');
$db->close();
?>