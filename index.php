<?php	
	require_once("config.php");
	require_once("functions.php");
	require("nav.php");
	
	$css = Array('css/StiliCogestione.css');
	$js = Array(
		'http://code.jquery.com/jquery-1.10.2.min.js',
		'js/prenotazioni.js');
	showHeader('Prenotazioni cogestione 2014', $css, $js);
	
	$db = initDB();
	printTimeBox($db);
?>

<?php
// MAIN

/* Ottiene i nomi delle colonne (blocchi) */
$blocks = blocchi($db);
   
if(inputValid($db)) {
    $name = $_GET['name'];
    $surname = $_GET['surname'];
    $class = $_GET['class'];
    $arrSplit = str_split($class, 1);
    
    /* La classe senza la sezione */
    $classN = intval($arrSplit[0]);
    
    // Riepilogo e controllo affollamento
    $inserts = Array();
    $pieno = $vm = FALSE;
    $correctBlocks = TRUE;
    
    // Riepilogo
    $riepilogo = '';
    $riepilogo .= "<p>Le tue prenotazioni:</p>\n";
    $riepilogo .= '<table id="ActivityTable">';
    $riepilogo .= '<tr><th>Nome</th><th>Cognome</th><th>Classe</th>';
    foreach($blocks as $b) {
    	$b = htmlentities($b);
        $riepilogo .= "\n<th>$b</th>";
    }
    $riepilogo .= "\n</tr><tr>\n";
    
    $riepilogo .= "<td>" . htmlspecialchars($name) . "</td>\n<td>" . htmlspecialchars($surname)
    	. "</td>\n<td>" . htmlspecialchars($class) . "</td>\n";
    
    /* Ripete per ogni singola prenotazione */
    foreach($blocks as $i => $b) {
        $pref = intval($_GET['block_' . $i]);
        $activityRow = getActivityInfo($pref, $db);
        
        /* Verifico se l'attività è coerente con il blocco */
        if($activityRow['time'] != $i) {
        	$correctBlocks = FALSE;
        }
        
        /* Verifico l'affollamento. Se max=0 il vincolo non vale. */
        if($activityRow['max'] != 0 && $activityRow['prenotati'] >= $activityRow['max']) {
            $pieno = TRUE;
        }
        
        /* Solo le quarte e le quinte possono accedere alle attività "VM18" */
        if($activityRow['vm'] == 1 && $classN != 4 && $classN != 5) {
            $vm = TRUE;
        }
        
        $inserts[$i] = $pref;
        $riepilogo .= "\n<td><div class=\"activity\">" . htmlentities($activityRow['title']) . ($pieno ? ' <b>[Pieno!]</b>':'') . '</div></td>';
    }
    $riepilogo .= '</tr></table>';
    if(!isEnabled()) {
        printError('Le prenotazioni sono chiuse!');
    } else if($pieno) {
        printError('Alcune delle attività selezionate sono troppo affollate. Rifai!');
    } else if ($vm) {
        printError('Alcune delle attività selezionate sono riservate a quarte e quinte. Rifai!');
    } else if (isSubscribed($name, $surname, $class, $db)) {
        printError('Ti sei già iscritto!');
    } else if(!$correctBlocks) {
    	printError('Alcune delle attività scelte non sono coerenti con i blocchi.');
    } else {
    	/* Controlli passati. L'utente può iscriversi. */
        echo $riepilogo;
    	inserisciPrenotazione($name, $surname, $class, $inserts, $db);    
        echo "<p>I dati sono stati registrati con successo.</p>";
    }
    
} else {
?>
<div id="desc">
<p>Questo è il sistema di prenotazione per la <b>cogestione</b> che si svolgerà al Liceo Scientifico "Albert Einstein" di Milano il giorno 25 gennaio 2014. Ecco come prenotarti per le attività:
<ol>
<li>Leggi la <b><a href="http://www.liceoeinsteinmilano.it/cms/public/circolari1213/280prenotazione%20cogestione.22.02.13.pdf">circolare</a></b> con le indicazioni operative.</li>
<li>Inserisci <b>nome, cognome e classe</b>, ognuno nel rispettivo campo.</li>
<li><b>Leggi</b> le descrizioni delle attività e le aule in cui si svolgeranno passando il cursore del mouse sui titoli delle stesse.
<li><b>Scegli con attenzione</b> le attività a cui vuoi prenotarti.
<ul>
<li>Per ogni colonna dovrai scegliere <b>una e una sola attività</b> (il numero <span class="posti">[n]</span> indica i posti rimasti).</li>
<li>Le attività segnate con (<b>Q</b>) sono riservate alle classi <b>quarte e quinte</b>. Per selezionarle dovrai prima scegliere la classe.</li>
<li>Se prevedi di essere <b>assente</b> per uno o più giorni, scegli l'apposita opzione: ricordati che dovrai comunque giustificare l'assenza secondo le modalità previste dal Regolamento di Istituto.</li>
<li>Se non puoi scegliere un'attività, vuol dire che ci sono <span class="posti">[0]</span> posti rimasti oppure è riservata alle classi quarte e quinte.</li>
</ul>
</li>
<li><b>Ricontrolla: una volta che avrai confermato la prenotazione, non potrai cambiare idea!</b><br />Ogni richiesta in questo senso sarà respinta.</li>
<li>Fai clic sul pulsante "Conferma".</li>
</ol>
<p>In caso di difficoltà o di problemi con il sistema, contattare <a href="mailto:cogestione@cogestione2014.netsons.org">l'assistenza</a>.</p>
</div>
<?php
    if(isset($_GET['submit'])) {
        printError('Non hai compilato correttamente tutti i campi. Riprova.');
    }
    
    printForm($db);
}

$db->close();
showFooter('ca-nstab-prenota');

/* end main */

function inputValid($db) {
	$validated = FALSE;
	
	$blocks = blocchi($db);
	$classi = classi($db);

	if(isset($_GET['class'])) {
		$validated = TRUE;
	
		/* Verifico la completezza dei dati */
		if(empty($_GET['name']) || empty($_GET['surname']) || empty($_GET['class']))
			$validated = FALSE;
	
		/* L'utente deve aver prenotato tutti i blocchi */
		foreach($blocks as $i => $b)
		{
			if(!isset($_GET['block_' . $i]) || !is_numeric($_GET['block_' . $i]))
				$validated = FALSE;
		}
	
		/* La classe deve essere in elenco */
		if(!in_array($_GET['class'], $classi))
			$validated = FALSE;
	}
	
	return $validated;
}

function printForm($db) {
	/* Stampa il form */
	
	echo  '<form action="'. $_SERVER['PHP_SELF'] . '" method="get" autocomplete="off">';
	echo '<table id="fieldTable">
            <tr><td><label for="name">Nome: </label></td>
            <td><input class="iField" type="text" name="name" id="name" required placeholder="Mario" /></td></tr>
            <tr><td><label for="surname">Cognome: </label></td>
            <td><input class="iField" type="text" name="surname" id="surname" placeholder="Rossi" required /></td></tr>
            <tr><td><label for="class">Classe: </label></td>
            <td>';       
    
    printClassSelector($db);
    
    echo "\n</td></tr>";
    echo '<tr><td colspan="2"><input id="submit" type="submit" name="submit" value="Conferma" ' . (isEnabled() ? '' : 'disabled') . ' /></td></tr>'; 
    echo "</table>\n";
    
    printActivityTable($db);
    
    echo '</form>';
}

function printClassSelector($db) {
	$classi = classi($db);
	
	echo '<select class="iField" name="class" id="class" onchange="getClassAndToggle(this)" required>
            <option value="" disabled selected>Seleziona la classe</option>';
    
    // Selettore classe       
    foreach($classi as $cl) {
    	if(isset($_GET['class']) && $cl == $_GET['class'])
    		$selected = 'selected';
    	else
    		$selected = '';
        echo "\n<option value=\"$cl\" $selected>$cl</option>";
    }        
            
    echo "\n</select>";
}

function printActivityTable($db) {
    /* Stampa la griglia */
    $blocks = blocchi($db);
    echo '<table id="ActivityTable">';
    /* Intestazione con blocchi */
    echo '<tr>';
    foreach($blocks as $b) {
        echo "\n<th>$b</th>";
    }
    echo "\n</tr><tr>";
    /* Procede colonna per colonna */
    foreach($blocks as $i => $b) {
        echo '<td>';
        $res = $db->query('SELECT attivita.*, COUNT(prenotazioni.id) AS prenotati
                            FROM attivita
                            LEFT JOIN prenotazioni ON attivita.id=prenotazioni.activity
                            WHERE attivita.time=' . intval($i) . '
                            GROUP BY attivita.id
                            ORDER BY attivita.id;');
        
        /* Stampa tutte le attività che si svolgono contemporaneamente */
        while($row = $res->fetch_assoc()) {
            $full = ($row['max']!=0 && $row['prenotati']>=$row['max']);
            
            echo "\n<div class=\"activity"
            	. ($full ? ' disabled' : '')
            	. "\">\n<input type=\"radio\" name=\"block_$i\" value=\""
                . intval($row['id']) . '" id="activity_' . intval($row['id']) . '"'
                . ($full ? ' disabled ' : '')
                . ' class="' . ($row['vm'] ? 'vm' : '') . ($full ? ' full' : '') . '" ' 
                . ' required />' . "\n" . '<label for="activity_' . intval($row['id']) . '">' . "\n"
                . ($row['max']!=0?'<span class="posti">['
                . ($row['max']-$row['prenotati']) . "]</span>\n":'')
                . htmlspecialchars($row['title']) . "</label>"
                . ($row['description'] ? "<div id=\"activity_desc_" . intval($row['id']) . "\" class=\"activity_description\">" . $row['description'] . "</div>" : '')
                . "</div>\n";
        }
        echo "</td>\n";
    }
    echo '</tr></table>';
}

function printTimeBox($db) {
	/* Prints info on opening and closing times */
	
	echo '<div id="timeBox">';
	$enabled = isEnabled();
	
	/* Ore di inizio e di fine */
	$dtz = new DateTimeZone('Europe/Rome');
	$beginTime = new DateTime(START_TIME, $dtz);
	$endTime = new DateTime(END_TIME, $dtz);

	$now = new DateTime(null, $dtz);
	
	if(!$enabled) {
		echo '<p class="error"><b>Avviso</b>: le prenotazioni sono ora chiuse.</p>';
	}
	echo '<p>Le prenotazioni saranno aperte <br />da <b>'
		. $beginTime->format('r')
		. '</b><br />a <b>'
		. $endTime->format('r')
		. '</b></p>';

	if($now >= $beginTime AND $now <= $endTime AND $enabled) {
		$diffTime = date_diff($endTime, $now); 
		echo '<p>Prenotazioni chiuse tra <b>'
		   . $diffTime->format('%d giorni, %h ore, %i minuti, %s secondi')
		   . '</b>.</p>';
	} else if($now <= $beginTime AND !$enabled) {
		$diffTime = date_diff($beginTime, $now); 
		echo '<p>Prenotazioni aperte tra <b>'
		   . $diffTime->format('%d giorni, %h ore, %i minuti, %s secondi')
		   . '</b>.</p>';
	}
	
	echo '</div>';
}

function inserisciPrenotazione($name, $surname, $class, $prenotazione, $db) {
	/* $prenotazione array associativo "id blocco" => "id attività" */
	
	// Escaping
	$name = $db->real_escape_string($name);
	$surname = $db->real_escape_string($surname);
	$class = $db->real_escape_string($class);
	// Inserimento dati
	foreach($prenotazione as $blocco_id => $attivita_id) {
		$blocco_id = intval($blocco_id);
		$attivita_id = intval($attivita_id);
		
		$res = $db->query("INSERT INTO prenotazioni (name, surname, class, time, activity) VALUES ('$name', '$surname', '$class', $blocco_id, $attivita_id);");
		if(!$res) die("Errore nell'inserimento della prenotazione!");
	}
}
?>