<?php	
	require_once("config.php");
	require("nav.php");
	
	$css = Array('includes/StiliCogestione.css');
	$js = Array(
		'http://code.jquery.com/jquery-1.10.2.min.js',
		'includes/prenotazioni.js');
	showHeader('Prenotazioni cogestione 2014', $css, $js);

	echo '<div id="timeBox">';


/* Ore di inizio e di fine */
$dtz = new DateTimeZone('Europe/Rome');
$beginTime = new DateTime($cogeStart, $dtz);
$endTime = new DateTime($cogeEnd, $dtz);

$now = new DateTime(null, $dtz);

if($now < $beginTime OR $now > $endTime) {
	$enabled = 0;
} else {
	$enabled = 1;
}

/* Manual override */
if($manuallyEnableForm) {
	$enabled = $manualSwitch;
}

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
?>
</div>

<?php
// MAIN
$db = initDB();

/* Ottiene i nomi delle colonne (blocchi) e l'elenco delle classi */
$blocks = blocchi($db);
$classi = classi($db);
$validated = FALSE;

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
        
if($validated) {
    $name = $db->real_escape_string($_GET['name']);
    $surname = $db->real_escape_string($_GET['surname']);
    $class = $db->real_escape_string($_GET['class']);
    $arrSplit = str_split($class, 1);
    
    /* La classe senza la sezione */
    $classN = $arrSplit[0];
    
    // Riepilogo e controllo affollamento
    $inserts = Array();
    $pieno = $vm = FALSE;
    
    // Riepilogo
    $riepilogo = '';
    $riepilogo .= "<p>Le tue prenotazioni:</p>\n";
    $riepilogo .= '<table id="ActivityTable">';
    $riepilogo .= '<tr>';
    foreach($blocks as $b) {
        $riepilogo .= "\n<th>$b</th>";
    }
    $riepilogo .= "\n</tr><tr>";
    
    /* Ripete per ogni singola prenotazione */
    foreach($blocks as $i => $b) {
        $pref = $db->real_escape_string($_GET['block_' . $i]);
        $activityRow = getActivityInfo($pref, $db);
        
        /* Verifico l'affollamento. Se max=0 il vincolo non vale. */
        if($activityRow['max'] != 0 && $activityRow['prenotati'] >= $activityRow['max']) {
            $pieno = TRUE;
        }
        
        /* Solo le quarte e le quinte possono accedere alle attività "VM18" */
        if($activityRow['vm'] == 1 && $classN != 4 && $classN != 5) {
            $vm = TRUE;
        }
        
        $inserts[$i] = $pref;
        $riepilogo .= "\n<td><div class=\"activity\">" . $activityRow['title'] . ($pieno ? ' <b>[Pieno!]</b>':'') . '</div></td>';
    }
    $riepilogo .= '</tr></table>';
    if(!$enabled) {
        echo '<div class="error">Le prenotazioni sono chiuse!</div>';
    } else if($pieno) {
        echo '<div class="error">Alcune delle attività selezionate sono troppo affollate. Rifai!</div>';
    } else if ($vm) {
        echo '<div class="error">Alcune delle attività selezionate sono riservate a quarte e quinte. Rifai!</div>';
    } else if (isSubscribed($name, $surname, $class, $db)) {
        echo '<div class="error">Ti sei già iscritto!</div>';
    } else {
        echo $riepilogo;
        
        // Inserimento dati
        foreach($inserts as $k => $in) {
            $res = $db->query("INSERT INTO prenotazioni (name, surname, class, time, activity) VALUES ('$name', '$surname', '$class', $k, $in);");
            if(!$res) die("Errore nell'inserimento della prenotazione!");
        }
        
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
        echo '<p class="error">Non hai compilato tutti i campi. Riprova.</p>';
    } echo  '<form action="'. $_SERVER['PHP_SELF'] . '" method="get" autocomplete="off">
            <table id="fieldTable">
            <tr><td><label for="name">Nome: </label></td>
            <td><input class="iField" type="text" name="name" id="name" required placeholder="Mario" /></td></tr>
            <tr><td><label for="surname">Cognome: </label></td>
            <td><input class="iField" type="text" name="surname" id="surname" placeholder="Rossi" required /></td></tr>
            <tr><td><label for="class">Classe: </label></td>
            <td><select class="iField" name="class" id="class" onchange="getClassAndToggle(this)" required>
            <option value="" disabled selected>Seleziona la classe</option>';
    
    // Selettore classe       
    foreach($classi as $cl) {
        echo "\n<option value=\"$cl\">$cl</option>";
    }        
            
    echo "\n</select></td></tr>";
    echo '<tr><td colspan="2"><input id="submit" type="submit" name="submit" value="Conferma" ' . ($enabled ? '' : 'disabled') . ' /></td></tr>'; 
    echo "</table>\n";
    
    /* Stampa la griglia */
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
                            WHERE attivita.time=' . $i . '
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

$db->close();
echo '</form>';
showFooter('ca-nstab-prenota');
?>