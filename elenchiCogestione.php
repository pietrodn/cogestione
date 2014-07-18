<?php
require_once("common.php");
$css = Array('css/StiliCogestione.css', 'css/elenchi.css');
showHeader('ca-nstab-elenchi', "Elenco prenotazioni cogestione", $css);

$authenticated = !empty($_SESSION['auth']);
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

if(isset($_GET['deleteUser'])) {
	if($authenticated) {
		/* Cancellazione di un utente singolo */
		$uid = intval($_GET['deleteUser']);
		$uInfo = $cogestione->getUser($uid);
		if($uInfo !== FALSE) {
			$result = $cogestione->deleteUser($uid);
			if($result === TRUE) {
				printSuccess("L'utente " . $uInfo['user_name'] . " " . $uInfo['user_surname']
				. " (" . $uInfo['user_class'] . ") è stato eliminato con successo.");
			} else {
				printError("L'utente $uid non ha potuto essere eliminato.");
			}
		} else {
			printError("L'utente con UID $uid non esiste!");
		}
	}
}

// Cerca studente
echo '<div class="panel panel-default noprint">
  <div class="panel-heading">
  <h3 class="panel-title">Cerca uno studente per visualizzare la sua prenotazione</h3>
  </div>
  <div class="panel-body">';
echo '<form class="form-inline" role="form" class="noprint" action="'. $_SERVER['PHP_SELF'] . '" method="get" style="margin-bottom: 10px;">
	<fieldset>
	<div class="form-group">
	<label for="name" class="sr-only">Nome: </label>
	<input class="form-control" type="text" name="name" id="name" placeholder="Nome" ' .
	(!empty($_GET['name']) ? 'value="' . htmlspecialchars($_GET['name']) . '" ' : '') .
	'/></div>
	<div class="form-group">
	<label for="surname" class="sr-only">Cognome: </label>
	<input class="form-control" type="text" name="surname" id="surname" placeholder="Cognome" ' .
	(!empty($_GET['surname']) ? 'value="' . htmlspecialchars($_GET['surname']) . '" ' : '') .
	'/>
	</div>
	<div class="form-group">
	<label for="class" class="sr-only">Classe: </label>
	<select class="form-control" name="class" id="class">
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
		
echo "\n</select></div>";
echo '<button style="margin-left: 5px;" type="submit" class="btn btn-primary" name="cercastud">Cerca</button>';
echo "</fieldset></form></div></div>\n";

// Selettore attività

echo '<div class="panel panel-default noprint">
  <div class="panel-heading"><h3 class="panel-title">Seleziona un\'attività per elencare i partecipanti</h3></div>
  <table class="table table-bordered noprint">';
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
echo '</tr></table></div>';

if(isset($_GET['activity'])) // Se si seleziona un'attività
{
	// Visualizza elenco partecipanti
	$activity = intval($_GET['activity']);
	$aRow = $cogestione->getActivityInfo($activity);
	echo '<div class="panel panel-default">
		<div class="panel-heading">
		<h3 class="panel-title">'
		. htmlspecialchars($blocks[$aRow['activity_time']]) . ' – ' . htmlspecialchars($aRow['activity_title'])
		.'</h3>
		</div>
		<div class="panel-body">';
	echo "\n
		Attività: <b>" . htmlspecialchars($aRow['activity_title'])
		. "</b>.\n";
	if ($aRow['activity_description']) {
		echo "<br />Descrizione: <br />
		<div class=\"descriptionBox\">" . $aRow['activity_description'] . "
		</div>";
	}
	echo "\n<br />Quando: <b>" . htmlspecialchars($blocks[$aRow['activity_time']])
		. "</b>\n<br />Partecipanti: <b>" . intval($aRow['prenotati']) . ($aRow['activity_size'] ? '/' . intval($aRow['activity_size']) : '') . '</b>';
	
	if($aRow['prenotati']>0)
	{	
		$user_list = $cogestione->getUsersForActivity($activity);
								
		echo "<br />Elenco dei partecipanti:\n
			<ol id=\"partecipanti\" class=\"well\">";
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
	echo "\n</div></div>";
	
} else if(isset($_GET['cercastud'])
	AND (!empty($_GET['name']) OR !empty($_GET['surname']) OR !empty($_GET['class']))) {
		
	// Se si cerca uno studente
	
	$studenti = $cogestione->findUser($_GET['name'], $_GET['surname'], $_GET['class']);
	
	if(count($studenti)) {
		$riepilogo = '';
		$riepilogo .= '<div class="panel panel-success noprint">
  			<div class="panel-heading">
  				<h3 class="panel-title">Prenotazioni trovate</h3>
  			</div>
  			<table class="table ">';
		$riepilogo .= '<tr class="active">' . ($authenticated ? '<th></th>' : '') . '<th>UID</th><th>Nome</th><th>Cognome</th><th>Classe</th>';
		foreach($blocks as $blockTitle) {
			$blockTitle = htmlspecialchars($blockTitle);
			$riepilogo .= "\n<th>$blockTitle</th>";
		}
		$riepilogo .= "\n</tr>";
		foreach($studenti as $row) {
			$riepilogo .= "\n<tr>";
			if($authenticated) {
				$riepilogo .= "\n<td>"
					. '<a class="btn btn-danger btn-xs" href="' . $_SERVER['PHP_SELF'] . '?deleteUser=' . intval($row['user_id']) .'">X</a>'
					. '</td>';
			}
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
		$riepilogo .= '</tr></table></div>';
		echo $riepilogo;
	} else {
		printSuccess('Nessuno studente trovato!');
	}
}

showFooter();
?>