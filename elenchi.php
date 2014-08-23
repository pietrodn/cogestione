<?php
require_once("common.php");
$css = Array('css/elenchi.css');
showHeader('ca-nstab-elenchi', "Elenco prenotazioni cogestione", $css);

$authenticated = !empty($_SESSION['auth']);
$cogestione = new Cogestione();

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
				printSuccess("L'utente ". htmlspecialchars($uInfo->fullName()) .
				" (" . htmlspecialchars($uInfo->classe()->name()) . ") è stato eliminato con successo.");
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
echo '<form class="form-inline noprint" role="form" action="'. $_SERVER['PHP_SELF'] . '" method="get" style="margin-bottom: 10px;">
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
	$cl_id = $cl->id();
	if(isset($_GET['class']) && $cl_id == $_GET['class'])
		$selected = 'selected';
	else
		$selected = '';
	echo "\n<option value=\"$cl_id\" $selected>" . htmlspecialchars($cl->name()) . "</option>";
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
	$bt = htmlspecialchars($b->title());
	echo "\n<th>$bt</th>";
}
echo "\n</tr><tr>";
foreach($blocks as $i => $b) {
	echo '<td>';
	$activities = $cogestione->getActivitiesForBlock($b);
	foreach($activities as $act) {
		$url = $_SERVER['PHP_SELF'] . '?activity=' . $act->id();
		echo "\n<div class=\"activity\"><span class=\"posti\">[" . (int)$act->prenotati() . ($act->size() != 0 ?'/' . intval($act->size()):'') . "]</span> 
			<a href=\"" . $url . "\">" . htmlspecialchars($act->title()) . '</a></div>';
	}
	
	echo '</td>';
}
echo '</tr></table></div>';

if(isset($_GET['activity'])) // Se si seleziona un'attività
{
	// Visualizza elenco partecipanti
	$activity_id = intval($_GET['activity']);
	$act = $cogestione->getActivityInfo($activity_id);
	echo '<div class="panel panel-default">
		<div class="panel-heading">
		<h3 class="panel-title">'
		. htmlspecialchars($act->block()->title()) . ' – ' . htmlspecialchars($act->title())
		.'</h3>
		</div>
		<div class="panel-body">';
	echo "\n
		Attività: <b>" . htmlspecialchars($act->title())
		. "</b>.\n";
	if ($act->description()) {
		echo "<br />Descrizione:
		<blockquote id=\"desc-box\">" . $act->description() . "
		</blockquote>";
	}
	echo "\nQuando: <b>" . htmlspecialchars($act->block()->title())
		. "</b>\n<br />Partecipanti: <b>" . intval($act->prenotati()) . ($act->size() ? '/' . intval($act->size()) : '') . '</b>';
	
	if($act->prenotati()>0)
	{	
		$user_list = $cogestione->getUsersForActivity($act);
								
		echo "<br />Elenco dei partecipanti:\n
			<ol id=\"partecipanti\" class=\"well\">";
		foreach($user_list as $u) {
			echo "\n<li>"
				. htmlspecialchars($u->surname()) . ' '
				. htmlspecialchars($u->name())
				. ' (' . $u->classe()->name() . ") </li>";
		}
		echo "\n</ol>";
		
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
  			<table class="table">';
		$riepilogo .= '<tr class="active">' . ($authenticated ? '<th></th>' : '') . '<th>UID</th><th>Nome</th><th>Cognome</th><th>Classe</th>';
		foreach($blocks as $b) {
			$blockTitle = htmlspecialchars($b->title());
			$riepilogo .= "\n<th>$blockTitle</th>";
		}
		$riepilogo .= "\n</tr>";
		foreach($studenti as $u) {
			$riepilogo .= "\n<tr>";
			if($authenticated) {
				$riepilogo .= "\n<td>"
					. '<a class="btn btn-danger btn-xs" href="' . $_SERVER['PHP_SELF'] . '?deleteUser=' . intval($u->id()) .'">X</a>'
					. '</td>';
			}
			$riepilogo .= "\n<td>" . htmlspecialchars($u->id()) . '</td>';
			$riepilogo .= "\n<td>" . htmlspecialchars($u->name()) . '</td>';
			$riepilogo .= "\n<td>" . htmlspecialchars($u->surname()) . '</td>';
			$riepilogo .= "\n<td>" . htmlspecialchars($u->classe()->name()) . '</td>';
			
			$prenotazione = $cogestione->getReservationsForUser($u);
			
			foreach($blocks as $i => $b) {
				$riepilogo .= "\n<td>" . htmlspecialchars($prenotazione[$i]->title()) . '</td>';
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