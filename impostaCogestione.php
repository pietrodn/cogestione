<?php
require_once("common.php");
$css = Array('css/StiliCogestione.css');
$js = Array('http://code.jquery.com/jquery-1.10.2.min.js');

showHeader("Impostazioni cogestione", $css, $js);

$configurator = Configurator::configurator();
$cogestione = new Cogestione();
$validated = FALSE;

if(isset($_POST['confermaTutto'])) {
	if($configurator->isAuthenticated($_POST['username'], $_POST['password'])) {
		$activities = $bl = $deleteAct = $deleteBlocks = Array();
		
		// Escaping dati attività
		foreach($_POST['activity'] as $act) {
			if(!empty($act['id'])) {
				$id = intval($act['id']);
				$activities[$id]['block'] = intval($act['block']);
				$activities[$id]['max'] = intval($act['max']);
				$activities[$id]['title'] = htmlspecialchars_decode($act['title'], ENT_QUOTES);
				$activities[$id]['vm'] = intval(!empty($act['vm']));
				$activities[$id]['description'] = htmlspecialchars_decode($act['description'], ENT_QUOTES);
				if(!empty($act['delete']))
					$deleteAct[] = $id;
			}
		}
		
		// Escaping dati blocchi
		foreach($_POST['block'] as $b) {
			if(!empty($b['id'])) {
				$id = intval($b['id']);
				$bl[$id]['title'] = htmlspecialchars_decode($b['title'], ENT_QUOTES);
				$bl[$id]['newRows'] = intval($b['newRows']);
				if(!empty($b['delete']))
					$deleteBlocks[] = $id;
			}
		}
		
		// Cancella le attività da cancellare.
		$cogestione->deleteActivities($deleteAct);
		
		// Modifica dati attività.
		foreach($activities as $id => $in) {
			if(in_array($id, $deleteAct))
				continue;
			$cogestione->replaceActivity($id, $in['block'], $in['max'], $in['title'], $in['vm'], $in['description']);
		}
		
		// Cancella i blocchi da cancellare
		$cogestione->deleteBlocks($deleteBlocks);
		
		// Modifica dati blocchi
		foreach($bl as $k => $b) {
			if(in_array($k, $deleteBlocks))
				continue;
			$cogestione->replaceBlock(intval($k), $b['title']);
			
			// Nuove righe attività
			if($b['newRows']>0) {
				$cogestione->addNewActivities($b['newRows'], $k);
			}
		}
		
		// Nuovi blocchi
		$newBlocks = intval($_POST['newBlocks']);
		$cogestione->addNewBlocks($newBlocks);
		
		// Cleanup (order *is* important)
		$cogestione->cleanOrphanActivities();
		$cogestione->cleanOrphanPrenotations();
			
		printError('I dati sono stati registrati con successo.');
		
		if(isset($_POST['confermaTruncate'])) {
			$cogestione->clearReservations();
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
	echo 'Ci sono <b>' . $cogestione->getSubscriptionsNumber() . ' prenotazioni</b> effettuate.';
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
$blocks = $cogestione->blocchi();
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
	$activities = $cogestione->getActivitiesForBlock($i);
	
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

?>