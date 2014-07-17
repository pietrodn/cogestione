<?php
require_once("common.php");
$css = Array('css/StiliCogestione.css', 'css/elenchi.css');
showHeader("Elenco prenotazioni cogestione", $css);

$cogestione = new Cogestione();

// MAIN

$blocks = $cogestione->blocchi();
$classi = $cogestione->classi();
$validated = FALSE;

$postiTot = $cogestione->getTotalSeats();
$nPrenot = $cogestione->getSubscriptionsNumber();
echo '<p class="noprint">Numero di prenotazioni: '
	. $nPrenot . '/' . $postiTot
	. ' (' . round($nPrenot/$postiTot*100)
	. '% degli studenti)</p>';

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
	<tr><td><label for="class">Classe: </label></td>
	<td><select class="iField" name="class" id="class">
	<option value="" selected>Tutte le classi</option>';
	
// Selettore classe	   
foreach($classi as $cl) {
	if(isset($_GET['class']) && $cl == $_GET['class'])
		$selected = 'selected';
	else
		$selected = '';
	$cl = htmlspecialchars($cl);
	echo "\n<option value=\"$cl\" $selected>$cl</option>";
}		
		
echo "\n</select></td></tr>";
echo '<tr><td colspan="2">
	<input id="submit" type="submit" name="cercastud" value="Cerca" />
	</td></tr>';
echo "</table>\n</form>\n";
			
if(isset($_GET['activity'])) // Se si seleziona un'attività
{
	// Visualizza elenco partecipanti
	$activity = intval($_GET['activity']);
	$aRow = $cogestione->getActivityInfo($activity);
	echo "\n<h2>" . htmlspecialchars($blocks[$aRow['activity_time']]) . ' – ' . htmlspecialchars($aRow['activity_title']) . '</h2>';
	echo "\n<div id=\"output\">\nAttività: <b>" . htmlspecialchars($aRow['activity_title'])
		. "</b>.\n<br />Descrizione: <br /><div class=\"descriptionBox\">" . $aRow['activity_description']
		. "</div>\n<br />Quando: <b>" . htmlspecialchars($blocks[$aRow['activity_time']])
		. "</b>\n<br />Partecipanti: <b>" . intval($aRow['prenotati']) . ($aRow['activity_size'] ? '/' . intval($aRow['activity_size']) : '') . '</b>';
	
	if($aRow['prenotati']>0)
	{	
		$user_list = $cogestione->getUsersForActivity($activity);
								
		echo "<br />Elenco dei partecipanti:\n
			<ol id=\"partecipanti\">";
		foreach($user_list as $row) {
			echo "\n<li>"
				. htmlspecialchars($row['user_surname']) . ' '
				. htmlspecialchars($row['user_name'])
				. ' (' . $row['user_class'] . ") </li>";
		}
		echo "\n</ol>";
		
	} else {
		echo "<br/>Nessun partecipante!";
	}
	echo "\n</div>";
	
} else if(isset($_GET['cercastud'])
	AND (!empty($_GET['name']) OR !empty($_GET['surname']) OR !empty($_GET['class']))) {
		
	// Se si cerca uno studente
	
	$studenti = $cogestione->findUser($_GET['name'], $_GET['surname'], $_GET['class']);
	
	if(count($studenti)) {
		$riepilogo = '';
		$riepilogo .= '<table id="ActivityTable">';
		$riepilogo .= '<tr><th>UID</th><th>Nome</th><th>Cognome</th><th>Classe</th>';
		foreach($blocks as $blockTitle) {
			$blockTitle = htmlspecialchars($blockTitle);
			$riepilogo .= "\n<th>$blockTitle</th>";
		}
		$riepilogo .= "\n</tr>";
		foreach($studenti as $row) {
			$riepilogo .= "\n<tr>";
			$riepilogo .= "\n<td>" . htmlspecialchars($row['user_id']) . '</td>';
			$riepilogo .= "\n<td>" . htmlspecialchars($row['user_name']) . '</td>';
			$riepilogo .= "\n<td>" . htmlspecialchars($row['user_surname']) . '</td>';
			$riepilogo .= "\n<td>" . htmlspecialchars($row['user_class']) . '</td>';
			
			$studentId = intval($row['user_id']);
			
			$prenotazione = $cogestione->getReservationsForUser($studentId);
			
			foreach($blocks as $i => $b) {
				$riepilogo .= "\n<td>" . htmlspecialchars($prenotazione[$i]) . '</td>';
			}
		}
		$riepilogo .= '</tr></table>';
		echo $riepilogo;
	} else {
		printError('Nessuno studente trovato!');
	}
}
			
// Selettore attività

echo '<h2 class="noprint">Cerca un\'attività</h2>';
echo '<table id="ActivityTable" class="noprint">';
echo '<tr>';
foreach($blocks as $b) {
	$b = htmlspecialchars($b);
	echo "\n<th>$b</th>";
}
echo "\n</tr><tr>";
foreach($blocks as $i => $b) {
	echo '<td>';
	$activities = $cogestione->getActivitiesForBlock($i);
	foreach($activities as $row) {
		$url = $_SERVER['PHP_SELF'] . '?activity=' . intval($row['activity_id']);
		echo "\n<div class=\"activity\"><span class=\"posti\">[" . intval($row['prenotati']) . ($row['activity_size']!=0?'/' . intval($row['activity_size']):'') . "]</span> 
			<a href=\"" . $url . "\">" . htmlspecialchars($row['activity_title']) . '</a></div>';
	}
	
	echo '</td>';
}
echo '</tr></table>';
showFooter('ca-nstab-elenchi');
?>