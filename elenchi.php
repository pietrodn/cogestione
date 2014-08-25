<?php
require_once("common.php");
require_once("includes/UserListView.class.php");

$css = Array(
	'css/elenchi.css',
	'css/tablesorter/theme.bootstrap.css',
	'css/tablesorter/theme.bootstrap_2.css'
);
$js = Array(
	'js/tablesorter/jquery.tablesorter.min.js',
	'js/tablesorter/jquery.tablesorter.widgets.min.js',
	'js/elenchi.js'
);
showHeader('ca-nstab-elenchi', "Elenco prenotazioni cogestione", $css, $js);

$authenticated = !empty($_SESSION['auth']);
$cogestione = new Cogestione();

$blocks = $cogestione->blocchi();

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
foreach($cogestione->classi() as $cl) {
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
	echo "\n<p>Attività: <b>" . htmlspecialchars($act->title()) . "</b>.</p>\n";
	echo "\n<p>Quando: <b>" . htmlspecialchars($act->block()->title()) . "</b></p>";
	if ($act->location()) {
		echo "\n<p>Dove:
		<b>" . $act->location() . "</b></p>";
	}
	if ($act->description()) {
		echo "\n<blockquote id=\"desc-box\">" . $act->description() . "</blockquote>";
	}
	
	echo "\n<p>Partecipanti: <b>" . intval($act->prenotati()) . ($act->size() ? '/' . intval($act->size()) : '') . '</b></p>';
	
	if($act->prenotati()>0)
	{	
		$user_list = $cogestione->getUsersForActivity($act);
								
		echo "<p>Elenco dei partecipanti:</p>\n
			<ol id=\"partecipanti\" class=\"well\">";
		foreach($user_list as $u) {
			echo "\n<li><a href=\"?uid=" . $u->id() . "\">"
				. htmlspecialchars($u->surname()) . ' '
				. htmlspecialchars($u->name())
				. '</a> (<a href="?cercastud=1&class=' . $u->classe()->id() . '">' . $u->classe()->name() . "</a>)</li>";
		}
		echo "\n</ol>";
		
	}
	echo "\n</div></div>";
	
} else if(isset($_GET['uid'])) {
	
	$users = Array($cogestione->getUser((int)$_GET['uid']));
	$view = new UserListView($users);
	$view->setAuthenticated($authenticated);
	$view->render();
	
} else if(isset($_GET['cercastud'])
	AND (!empty($_GET['name']) OR !empty($_GET['surname']) OR !empty($_GET['class']))) {
		
	// Se si cerca uno studente
	$name = (!empty($_GET['name'])) ? $_GET['name'] : NULL;
	$surname = (!empty($_GET['surname'])) ? $_GET['surname'] : NULL;
	$class = (!empty($_GET['class'])) ? $_GET['class'] : NULL;
	
	$users = $cogestione->findUser($name, $surname, $class);
	$view = new UserListView($users);
	$view->setAuthenticated($authenticated);
	$view->render();
}


showFooter();
?>