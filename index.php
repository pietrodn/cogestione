<?php
	require_once("common.php");
	
	$css = Array('css/prenota.css');
	$js = Array('js/prenota.js');
	showHeader('ca-nstab-prenota', 'Prenotazioni cogestione 2014', $css, $js);
	
	$configurator = Configurator::configurator();
	$cogestione = new Cogestione();
?>

<?php

/* Ottiene i nomi delle colonne (blocchi) */
$blocks = $cogestione->blocchi();
   
if(inputValid($cogestione)) {
	$name = $_POST['name'];
	$surname = $_POST['surname'];
	$class_id = intval($_POST['class']);
	
	/* La classe senza la sezione */
	$class_info = $cogestione->getClassInfo($class_id);
	$classN = $class_info['class_year'];
	$class_name = $class_info['class_year'] . $class_info['class_section'];
	
	// Riepilogo e controllo affollamento
	$inserts = Array();
	$pieno = $vm = FALSE;
	$correctBlocks = TRUE;
	
	// Riepilogo
	$riepilogo = '';
	$riepilogo .= '<p>Le tue prenotazioni:</p>
		<table class="table table-bordered">
		<tr><th>Nome</th><th>Cognome</th><th>Classe</th>';
	foreach($blocks as $b) {
		$b = htmlspecialchars($b);
		$riepilogo .= "\n<th>$b</th>";
	}
	$riepilogo .= "\n</tr><tr>\n";
	
	$riepilogo .= "<td>" . htmlspecialchars($name)
		. "</td>\n<td>" . htmlspecialchars($surname)
		. "</td>\n<td>" . htmlspecialchars($class_name) . "</td>\n";
	
	/* Ripete per ogni blocco */
	foreach($blocks as $i => $b) {
		$selectedActivity = intval($_POST['block_' . $i]);
		$activityRow = $cogestione->getActivityInfo($selectedActivity);
		
		/* Verifico se l'attività è coerente con il blocco */
		if($activityRow['activity_time'] != $i) {
			$correctBlocks = FALSE;
		}
		
		/* Verifico l'affollamento. Se max=0 il vincolo non vale. */
		if($activityRow['activity_size'] != 0 && $activityRow['prenotati'] >= $activityRow['activity_size']) {
			$pieno = TRUE;
		}
		
		/* Solo le quarte e le quinte possono accedere alle attività "VM18" */
		if(!$cogestione->activityOkForClass($activityRow['activity_id'], $class_id)) {
			$vm = TRUE;
		}
		
		$inserts[$i] = $selectedActivity;
		$riepilogo .= "\n<td><div class=\"activity\">" . htmlspecialchars($activityRow['activity_title']) . ($pieno ? ' <b>[Pieno!]</b>':'') . '</div></td>';
	}
	$riepilogo .= '</tr></table>';
	if(!$configurator->isEnabled()) {
		printError('Le prenotazioni sono chiuse!');
	} else if($pieno) {
		printError('Alcune delle attività selezionate sono troppo affollate. Rifai!');
	} else if ($vm) {
		printError('Alcune delle attività selezionate sono riservate a quarte e quinte. Rifai!');
	} else if ($cogestione->isSubscribed($name, $surname, $class)) {
		printError('Ti sei già iscritto!');
	} else if(!$correctBlocks) {
		printError('Alcune delle attività scelte non sono coerenti con i blocchi.');
	} else {
		/* Controlli passati. L'utente può iscriversi. */
		echo $riepilogo;
		$cogestione->inserisciPrenotazione($name, $surname, $class_id, $inserts);	  
		printSuccess("I dati sono stati registrati con successo.");
	}
	
} else {
	printTimeBox();
?>
<div id="desc">
<p>Questo è il sistema di prenotazione per la <b>cogestione</b> che si svolgerà al Liceo Scientifico "Albert Einstein" di Milano il giorno 25 gennaio 2014. Ecco come prenotarti per le attività:
<ol>
<li>Leggi la <b><a href="http://www.liceoeinsteinmilano.it/cms/public/circolari1213/280prenotazione%20cogestione.22.02.13.pdf">circolare</a></b> con le indicazioni operative.</li>
<li>Inserisci <b>nome, cognome e classe</b>, ognuno nel rispettivo campo.</li>
<li><b>Leggi</b> le descrizioni delle attività e le aule in cui si svolgeranno passando il cursore del mouse sui titoli delle stesse.
<li><b>Scegli con attenzione</b> le attività a cui vuoi prenotarti.
<ul>
<li>Per ogni colonna dovrai scegliere <b>una e una sola attività</b> (il numero <span class="text-danger">[n]</span> indica i posti rimasti).</li>
<li>Le attività segnate con (<b>Q</b>) sono riservate alle classi <b>quarte e quinte</b>. Per selezionarle dovrai prima scegliere la classe.</li>
<li>Se prevedi di essere <b>assente</b> per uno o più giorni, scegli l'apposita opzione: ricordati che dovrai comunque giustificare l'assenza secondo le modalità previste dal Regolamento di Istituto.</li>
<li>Se non puoi scegliere un'attività, vuol dire che ci sono <span class="text-danger">[0]</span> posti rimasti oppure è riservata alle classi quarte e quinte.</li>
</ul>
</li>
<li><b>Ricontrolla: una volta che avrai confermato la prenotazione, non potrai cambiare idea!</b><br />Ogni richiesta in questo senso sarà respinta.</li>
<li>Fai clic sul pulsante "Conferma".</li>
</ol>
<p>In caso di difficoltà o di problemi con il sistema, contattare <a href="mailto:cogestione@cogestione2014.netsons.org">l'assistenza</a>.</p>
</div>
<?php
	if(isset($_POST['submit'])) {
		printError('Non hai compilato correttamente tutti i campi. Riprova.');
	}
	
	printForm($cogestione);
}

showFooter();

/* end main */

function inputValid($cogestione) {
	$validated = FALSE;
	
	$blocks = $cogestione->blocchi();
	$classi = $cogestione->classi();

	if(isset($_POST['class'])) {
		$validated = TRUE;
	
		/* Verifico la completezza dei dati */
		if(empty($_POST['name']) || empty($_POST['surname']) || empty($_POST['class']))
			$validated = FALSE;
	
		/* L'utente deve aver prenotato tutti i blocchi */
		foreach($blocks as $i => $b)
		{
			if(!isset($_POST['block_' . $i]) || !is_numeric($_POST['block_' . $i]))
				$validated = FALSE;
		}
	
		/* La classe deve essere in elenco */
		if(!array_key_exists($_POST['class'], $classi))
			$validated = FALSE;
	}
	
	return $validated;
}

function printForm($cogestione) {

	$configurator = Configurator::configurator();
	/* Stampa il form */
	
	echo  '<form action="'. $_SERVER['PHP_SELF'] . '" method="post" autocomplete="off">
			<fieldset class="form-inline">
			<div class="form-group">
			<label class="sr-only" for="name">Nome: </label>
			<input class="form-control" type="text" name="name" id="name" required placeholder="Nome" />
			</div>
			<div class="form-group">
			<label class="sr-only" for="surname">Cognome: </label>
			<input class="form-control" type="text" name="surname" id="surname" placeholder="Cognome" required />
			</div>
			<div class="form-group">
			<label class="sr-only" for="class">Classe: </label>';	   
	
	printClassSelector($cogestione);
	
	echo "\n</div></fieldset>\n";
	
	printActivityTable($cogestione);
	
	echo '<button class="btn btn-primary ' . ($configurator->isEnabled() ? '' : 'disabled') . '" type="submit" name="submit">Conferma</button>' . "\n";
	
	echo '</form>';
}

function printClassSelector($cogestione) {
	$classi = $cogestione->classi();
	
	echo '<select class="form-control" name="class" id="classSelector" required>
			<option value="" disabled selected>Seleziona la classe</option>';
	
	// Selettore classe		  
	foreach($classi as $cl_id => $cl_val) {
		if(isset($_POST['class']) && $cl_id == $_POST['class'])
			$selected = 'selected';
		else
			$selected = '';
		echo "\n<option value=\"$cl_id\" $selected>" . htmlspecialchars($cl_val['class_year'] . $cl_val['class_section']) . "</option>";
	}		 
			
	echo "\n</select>";
}

function printActivityTable($cogestione) {
	/* Stampa la griglia */
	$blocks = $cogestione->blocchi();
	echo '<div class="panel panel-default" id="activity-table">
  <!-- Default panel contents -->
  <div class="panel-heading">
  <h3 class="panel-title">Seleziona le attività</h3>
  </div>

  <!-- Table -->
  <table class="table table-bordered">';
	/* Intestazione con blocchi */
	echo '<tr>';
	foreach($blocks as $b) {
		echo "\n<th class=\"active\">$b</th>";
	}
	echo "\n</tr><tr>";
	/* Procede colonna per colonna */
	foreach($blocks as $i => $b) {
		echo '<td>';
		$activities = $cogestione->getActivitiesForBlock($i);
		
		/* Stampa tutte le attività che si svolgono contemporaneamente */
		foreach($activities as $row) {
			
			$full = ($row['activity_size']!=0 && $row['prenotati']>=$row['activity_size']);
			
			echo "\n" . '<div class="radio">' . "\n";
			printf('<label for="activity_%d" class="popover_activity" data-toggle="popover" title="%s">' . "\n",
				intval($row['activity_id']), 
				htmlspecialchars($row['activity_title']));
			printf('<div class="description-wrapper">%s</div>' . "\n", $row['activity_description']);
			printf('<input type="radio" name="block_%d" value="%d" id="activity_%d" %s class="%s %s" required />' . "\n",
				$i,
				intval($row['activity_id']),
				intval($row['activity_id']),
				($full ? 'disabled' : ''),
				($row['activity_vm'] ? 'vm' : ''),
				($full ? 'full' : '')
				);
				
			if($row['activity_size']!=0) {
				printf('<span class="text-danger">[%d]</span>' . "\n", ($row['activity_size']-$row['prenotati']));
			}
			echo htmlspecialchars($row['activity_title']) . "\n</label>\n</div>\n";
		}
		echo "</td>\n";
	}
	echo "</tr>\n</table>\n</div>";
}

function printTimeBox() {
	/* Prints info on opening and closing times */
	
	$configurator = Configurator::configurator();
	$enabled = $configurator->isEnabled();
	
	/* Ore di inizio e di fine */
	$dtz = new DateTimeZone('Europe/Rome');
	$beginTime = new DateTime($configurator->getStartTime(), $dtz);
	$endTime = new DateTime($configurator->getEndTime(), $dtz);

	$now = new DateTime(null, $dtz);
	
	echo '<div class="panel panel-default">
			<div class="panel-heading">
			<h3 class="panel-title">Prenotazioni '
			. ($enabled ? 'aperte' : 'chiuse')
			. '</h3>
			</div>';
	
	
	
	echo '<ul class="list-group">';
	if($enabled) {
		echo '<li class="list-group-item text-success">Le prenotazioni sono ora <b>aperte</b>.</li>';
	} else {
		echo '<li class="list-group-item text-danger">Le prenotazioni sono ora <b>chiuse</b>.</li>';
	}
	if(!$configurator->getManualMode()) {
		echo '<li class="list-group-item">Apertura: <b>'
			. $beginTime->format('r')
			. '</b></li>'
			. '<li class="list-group-item">Chiusura: <b>'
			. $endTime->format('r')
			. '</b></li>';
	
		if($now >= $beginTime AND $now <= $endTime AND $enabled) {
			$diffTime = date_diff($endTime, $now);
			echo '<li class="list-group-item text-warning">Prenotazioni chiuse tra <b>'
			   . $diffTime->format('%d giorni, %h ore, %i minuti, %s secondi')
			   . '</b>.</li>';
		} else if($now <= $beginTime AND !$enabled) {
			$diffTime = date_diff($beginTime, $now); 
			echo '<li class="list-group-item text-info">Prenotazioni aperte tra <b>'
			   . $diffTime->format('%d giorni, %h ore, %i minuti, %s secondi')
			   . '</b>.</li>';
		}
	}
	
	echo '</ul></div>';
}

?>