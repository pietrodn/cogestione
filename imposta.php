<?php
require_once("common.php");

require_once("includes/RegexBlacklist.class.php");
require_once("includes/Classe.class.php");

if(empty($_SESSION['auth'])) {
	header('Location: ./login.php');
	die();
}

$css = Array('css/imposta.css');
$js = Array('js/imposta.js');

showHeader('ca-nstab-imposta', "Impostazioni cogestione", $css, $js);

$configurator = Configurator::configurator();
$cogestione = new Cogestione();
if(isset($_POST['submitActivities'])) {
	activeTab('attivita');
	$activities = $bl = $deleteAct = $deleteBlocks = Array();
	
	// Escaping dati attività
	if(isset($_POST['activity'])) {
		foreach($_POST['activity'] as $act) {
			if(!empty($act['id'])) {
				$id = intval($act['id']);
				$activities[$id] = new Activity(
					$id,
					intval($act['block']),
					htmlspecialchars_decode($act['title'], ENT_QUOTES),
					intval($act['max']),
					intval(!empty($act['vm'])),
					htmlspecialchars_decode($act['description'], ENT_QUOTES),
					htmlspecialchars_decode($act['location'], ENT_QUOTES),
					null
				);
				
				if(!empty($act['delete'])) {
					$deleteAct[] = $id;
				}
			}
		}
	}
	
	// Escaping dati blocchi
	$newRows = Array();
	if(isset($_POST['block'])) {
		foreach($_POST['block'] as $b) {
			if(!empty($b['id'])) {
				$id = intval($b['id']);
				$bl[$id] = new Block(
					$id,
					htmlspecialchars_decode($b['title'], ENT_QUOTES)
				);
					
				$newRows[$id] = intval($b['newRows']);
				if(!empty($b['delete'])) {
					$deleteBlocks[] = $id;
				}
			}
		}
	}
	
	// Cancella le attività da cancellare.
	$cogestione->deleteActivities($deleteAct);
	
	// Modifica dati attività.
	foreach($activities as $id => $in) {
		if(in_array($id, $deleteAct))
			continue;
		$cogestione->updateActivity($in);
	}
	
	// Cancella i blocchi da cancellare
	$cogestione->deleteBlocks($deleteBlocks);
	
	// Modifica dati blocchi
	foreach($bl as $id => $b) {
		if(in_array($id, $deleteBlocks))
			continue;
		$cogestione->updateBlock($b);
		
		// Nuove righe attività
		if($newRows[$id]>0) {
			$cogestione->addNewActivities($newRows[$id], $id);
		}
	}
	
	// Nuovi blocchi
	$newBlocks = intval($_POST['newBlocks']);
	$cogestione->addNewBlocks($newBlocks);
		
	printSuccess('La tabella attività è stata modificata.');

} else if(isset($_POST['submitDelete'])) {
	activeTab('cancella');
	/* Cancellazione di tutte le prenotazioni */
	if(isset($_POST['confermaTruncate'])) {
		$res = $cogestione->clearReservations();
		if($res) {
			printSuccess('Tutte le prenotazioni sono state cancellate.');
		} else {
			printError('Errore nel cancellare le prenotazioni!');
		}
	} else if(isset($_POST['uid_delete']) && $_POST['uid_delete']) {
		/* Cancellazione di un utente singolo */
		$uid = intval($_POST['uid_delete']);
		$uInfo = $cogestione->getUser($uid);
		if($uInfo !== FALSE) {
			$result = $cogestione->deleteUser($uid);
			if($result === TRUE) {
				printSuccess("L'utente " . $uInfo->fullName()
				. " ($uid) è stato eliminato con successo.");
			} else {
				printError("L'utente $uid non ha potuto essere eliminato.");
			}
		} else {
			printError("L'utente con UID $uid non esiste!");
		}
	}

} else if(isset($_POST['submitEnable'])) {
	activeTab('abilita');
	/* Manual mode */
	if(isset($_POST['autoEnable'])) {
		$configurator->setManualMode(!(bool)$_POST['autoEnable']);
	}
	
	if(isset($_POST['manualOn'])) {
		$configurator->setManualOn((bool)$_POST['manualOn']);
	}
	
	/* Start and end times */
	if(isset($_POST['startTime'])) {
		$configurator->setStartTime($_POST['startTime']);
	}
	if(isset($_POST['endTime'])) {
		$configurator->setEndTime($_POST['endTime']);
	}
	
	printSuccess('Impostazioni modificate con successo.');

} else if(isset($_POST['submitClasses'])) {
	activeTab('classi');
	$raw_classes = $_POST['classes'];
	$classes_input = explode(';', $raw_classes);
	$classes_output = Array();
	foreach($classes_input as $cl_name) {
		$cl_name = trim($cl_name);
		if($cl_name) { // if not empty
			$classes_output[] = Classe::parseClass($cl_name);
		}
	}
	$cogestione->setClasses($classes_output);
	
	printSuccess('Classi aggiornate con successo.');
} else if(isset($_POST['submitBlacklist'])) {
	activeTab('blacklist');
	
	if(isset($_POST['blacklistRegex'])) {
		$configurator->setBlacklistRegex((bool)$_POST['blacklistRegex']);
	}
	
	$raw_blacklist = $_POST['blacklist'];
	$black_arr = explode("\n", $raw_blacklist);
	$blacklist = new RegexBlacklist($black_arr);
	$configurator->setBlacklist($blacklist);
	
	printSuccess('Blacklist aggiornata con successo.');
}
?>
<!--
<p>
Per <b>aggiungere un nuovo blocco o una nuova attività</b> occorre:
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
Per motivi di coerenza dei dati, è consigliabile azzerare le prenotazioni dopo aver cancellato attività o blocchi.
</p>
-->

<!-- Nav tabs -->
<div class="panel panel-default">
	<div class="panel-body">
		<ul class="nav nav-pills" role="tablist" id="imposta-tab">
			<li class="active"><a href="#abilitazione" role="tab" data-toggle="tab">Abilitazione prenotazioni</a></li>
			<li><a href="#classi" role="tab" data-toggle="tab">Classi</a></li>
			<li><a href="#cancella" role="tab" data-toggle="tab">Cancellazione prenotazioni</a></li>
			<li><a href="#attivita" role="tab" data-toggle="tab">Attività</a></li>
			<li><a href="#blacklist" role="tab" data-toggle="tab">Blacklist</a></li>
		</ul>
	</div>
</div>

<!-- Tab panes -->
<div class="tab-content">
	<div class="tab-pane active" id="abilitazione">
		<!-- Abilitation form -->
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Abilitazione delle prenotazioni</h3>
			</div>
			<ul class="list-group">
				<!-- Auto/manual switch -->
				<li class="list-group-item">
					<div class="radio">
						<label>
						<input id="automatic-switch" type="radio" name="autoEnable" value="1" <?php if(!$configurator->getManualMode()) echo "checked"; ?> />
						Automatica
						</label>
					</div>
					<div class="radio">
						<label>
						<input id="manual-switch" type="radio" name="autoEnable" value="0" <?php if($configurator->getManualMode()) echo "checked"; ?> />
						Manuale
						</label>
					</div>
				</li>
				
				<!-- Options for automatic handling -->
				<li class="list-group-item" id="automatic-panel">
					<fieldset class="form-inline">
						<div class="form-group">
							Date inizio e fine (solo modalità automatica):<br />
							<input class="form-control" type="datetime-local" name="startTime" value="<?php echo $configurator->getStartTime();?>" /> –
							<input class="form-control" type="datetime-local" name="endTime" value="<?php echo $configurator->getEndTime();?>" />
						</div>
					</fieldset>
				</li>
				
				<!-- Options for manual handling -->
				<li class="list-group-item" id="manual-panel">
					Switch on/off (solo modalità manuale):<br />
					<div class="radio">
						<label>
						<input type="radio" name="manualOn" value="1" <?php if($configurator->getManualOn()) echo "checked"; ?> />On
						</label>
					</div>
					<div class="radio">
						<label>
						<input type="radio" name="manualOn" value="0" <?php if(!$configurator->getManualOn()) echo "checked"; ?> />Off<br />
						</label>
					</div>
				</li>
				
				<!-- Submit button -->
				<li class="list-group-item">
					<button class="btn btn-primary" type="submit" name="submitEnable">Modifica impostazioni</button>
				</li>
			</ul>
		</div>
		</form>
	</div>
	<div class="tab-pane" id="classi">
		<!-- Classes form -->
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Classi</h3>
			</div>
			<ul class="list-group">
				<li class="list-group-item">
					<div class="form-group">
						<label for="classes-form">Inserisci le classi, separate da punto e virgola (";"). Gli spazi saranno ignorati.</label>
						<textarea class="form-control" rows="4" name="classes" id="classes-form" placeholder="1A; 1B; 1C; 2A; 2B"><?php
	$classes_array = $cogestione->classi();
	$class_names = Array();
	
	foreach($classes_array as $cl_id => $cl_val) {
		$class_names[] = $cl_val->name();
	}
	
	echo implode('; ', $class_names);
?></textarea>
					</div>
				</li>
				<li class="list-group-item">
					<button class="btn btn-primary" type="submit" name="submitClasses">Modifica classi</button>
				</li>
			</ul>
		</div>
		</form>
	</div>
	<div class="tab-pane" id="blacklist">
		<!-- Blacklist form -->
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Blacklist</h3>
			</div>
			<ul class="list-group">
				<li class="list-group-item">
					Scegli il tipo di blacklist:<br />
					<div class="radio">
						<label>
						<input type="radio" name="blacklistRegex" value="0" <?php if(!$configurator->getBlacklistRegex()) echo "checked"; ?> />Testo semplice<br />
						</label>
					</div>
					<div class="radio">
						<label>
						<input type="radio" name="blacklistRegex" value="1" <?php if($configurator->getBlacklistRegex()) echo "checked"; ?> />Espressioni regolari
						</label>
					</div>
				</li>
				<li class="list-group-item">
					<div class="form-group">
						<label for="blacklist-form">Inserisci le espressioni vietate, una per riga.
						Puoi usare la sintassi delle <a href="//it.wikipedia.org/wiki/Espressione_regolare">espressioni regolari</a>.</label>
						<textarea class="form-control" rows="10" name="blacklist" id="blacklist-form" placeholder="parolacce"><?php
	echo htmlspecialchars(implode("\n", $configurator->getBlacklist()->getList()));
?></textarea>
					</div>
				</li>
				<li class="list-group-item">
					<button class="btn btn-primary" type="submit" name="submitBlacklist">Modifica blacklist</button>
				</li>
			</ul>
		</div>
		</form>
	</div>
	<div class="tab-pane" id="cancella">
		<!-- Deletion form -->
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Cancellazione prenotazioni</h3>
			</div>
			<ul class="list-group">
				<li class="list-group-item">
					<div class="checkbox">
						<?php
						echo 'Ci sono <b>' . $cogestione->getSubscriptionsNumber() . ' prenotazioni</b> effettuate.';
						?> Se vuoi cancellarle, spunta la casella.
						I dati non potranno essere recuperati.<br />
						<label for="confermaTruncate">
						<input type="checkbox" name="confermaTruncate" id="confermaTruncate" value="Cancella prenotazioni" />
						Cancella tutte le prenotazioni
						</label>
					</div>
				</li>
				<li class="list-group-item" id="delete-single-reservation">
					<div class="form-group">
						Elimina una singola prenotazione.<br />
						<label for="uid_delete">
						UID: <input type="text" name="uid_delete" id="uid_delete" size="20" placeholder="123" />
						</label>
					</div>
				</li>
				<li class="list-group-item">
					<button class="btn btn-danger" type="submit" name="submitDelete">Conferma cancellazione</button>
				</li>
			</ul>
		</div>
		</form>
	</div>
	<div class="tab-pane" id="attivita">

	<!-- Activity form -->
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Modifica tabella attività</h3>
				</div>
				<div class="panel-body">
					<label>Aggiungi <input type="number" min="0" name="newBlocks" value="0" /> nuovi blocchi</label>
					<table id="ActivityTable" class="table table-bordered">
						<tr>
<?php
/* Intestazione con blocchi */
/* Ottiene i nomi delle colonne (blocchi) */
$blocks = $cogestione->blocchi();
foreach($blocks as $id => $b) {
	$id = $b->id();
	$bt = $b->title();
	echo "\n<th class=\"active\">"
		. "<input type=\"hidden\" name=\"block[$id][id]\" value=\"$id\" />\n"
		. '<div class="input-group">'
		. '<span class="checkbox input-group-addon">'
		. "<label><input type=\"checkbox\" id=\"block-delete-$id\" name=\"block[$id][delete]\" />DEL</label>"
		. "</span>"
		. "<input class=\"form-control\" type=\"text\" size=\"35\" name=\"block[$id][title]\" id=\"block-title-$id\" value=\"". htmlspecialchars($bt, ENT_QUOTES, "UTF-8", false) . "\" />"
		. "</div>"
		. "</th>";
}
echo "\n</tr><tr>";
/* Procede colonna per colonna */
foreach($blocks as $i => $b) {
	echo '<td id="block-' . $i . '">';
	$activities = $cogestione->getActivitiesForBlock($b);
	
	/* Stampa tutte le attività che si svolgono contemporaneamente */
	foreach($activities as $act) {
		$title = htmlspecialchars($act->title(), ENT_QUOTES, "UTF-8", false);
		$location = htmlspecialchars($act->location(), ENT_QUOTES, "UTF-8", false);
		$id = $act->id();
		$placeholder = htmlspecialchars('Descrizione per "' . $title . '"');
		echo "\n<div class=\"set-activity\" id=\"activity-$id\">\n"
			. "<input type=\"hidden\" name=\"activity[$id][id]\" value=\"$id\" />\n"
			. "<input type=\"hidden\" name=\"activity[$id][block]\" value=\"$i\" />\n"
			. '<div class="input-group">'
			. '<span class="checkbox input-group-addon">'
			. "<label for=\"activity-delete-$id\">"
			. "<input id=\"activity-delete-$id\" name=\"activity[$id][delete]\" type=\"checkbox\" />"
			. "DEL</label></span>"
			. "<input class=\"form-control activity-set-title\" type=\"text\" id=\"activity-title-$id\" name=\"activity[$id][title]\" value=\"$title\" /><br />\n"
			. "</div>"
			. '<div class="input-group activity-size">'
			. '<span class="input-group-addon">Posti:</span>'
			. "<input class=\"form-control\" type=\"number\" min=\"0\" id=\"activity-max-$id\" name=\"activity[$id][max]\" value=\""
			. intval($act->size()) . "\" />\n"
			. '<span class="checkbox input-group-addon">'
			. "<label for=\"activity-vm-$id\">"
			. "<input id=\"activity-vm-$id\" name=\"activity[$id][vm]\" type=\"checkbox\" "
			. ($act->vm() ? 'checked="checked"' : '')
			. "/>VM18</label>"
			. "</span>"
			. "</div>"
			. '<div class="input-group">'
			. '<span class="input-group-addon">Luogo:</span>'
			. "<input class=\"form-control activity-set-location\" type=\"text\" id=\"activity-location-$id\" name=\"activity[$id][location]\" value=\"$location\" /><br />\n"
			. '</div>'
			. "<textarea class=\"form-control\" rows=\"4\" name=\"activity[$id][description]\" placeholder=\"$placeholder\">" . htmlspecialchars($act->description()) . "</textarea>"
			. "\n</div>\n";
	}
	echo '</td>';
}
echo '</tr><tr>';
foreach($blocks as $i => $b) {
	echo '<td>';
	echo '<label>Aggiungi <input class="form-control" type="number" min="0" name="block[' . intval($i) . '][newRows]" value="0" /> nuove attività</label>';
	echo '</td>';
}
?>
						</tr>
					</table>
					<button class="btn btn-primary" type="submit" name="submitActivities">Modifica attività</button>
				</div><!-- panel-body -->
			</div><!-- panel -->
		</form>
	</div><!-- tab-pane -->
</div><!-- tab-content -->
<?php
	showFooter();
	
	function activeTab($tabid) {
		/* Invisibile element that remembers the active tab on submit */
		echo '<div id="active-tab">' . $tabid . '</div>';
	}
?>