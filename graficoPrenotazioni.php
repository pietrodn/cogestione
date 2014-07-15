<?php
	require_once("functions.php");
	require("nav.php");
	
	$css = Array('css/StiliCogestione.css');
	showHeader('Grafico prenotazioni cogestione', $css);
	
    // Config
    $dtz = new DateTimeZone('Europe/Rome');
    date_default_timezone_set('Europe/Rome');
    $beginDateTime = new DateTime(START_TIME, $dtz);
	$endDateTime = new DateTime(END_TIME, $dtz);
	
    $beginTime = $beginDateTime->format('YmdHis');	// Inizio delle prenotazioni	
    $endTime = $endDateTime->format('YmdHis');          // Fine delle prenotazioni
    $xSize = 710;			// Dimensioni del grafico
    $ySize = 420;
    $xTicks = floor($xSize/100);	// N. di tacche sull'asse X
    
    // MAIN
    $chxlArr = $chxpArr = Array();
    
    $bDT = new DateTime($beginTime, $dtz);
    $beginTS = $dTS = date_timestamp_get($bDT);
    
    $eDT = new DateTime($endTime, $dtz);
    $curDT = new DateTime(null, $dtz);
    
    $endDT = min($curDT, $eDT);
    $endTS = $endDT->getTimestamp(); // Non va comunque oltre l'ora di fine
    
    $db = initDB();
    
    // Intervalli di 1h
    $res = $db->query("SELECT count(pren_id) as c,
    	UNIX_TIMESTAMP(CONCAT(DATE(pren_timestamp), ' ', HOUR(pren_timestamp), ':00:00')) + 3600 - $beginTS AS t
    	FROM prenotazioni WHERE pren_timestamp >= $beginTime
    	GROUP BY CONCAT(DATE(pren_timestamp), HOUR(pren_timestamp)) ORDER BY pren_timestamp;");
    
    $pCount = $nData = 0;
    
    // Data
    $chdX = '';
    $chdY = '';
    while($row = $res->fetch_assoc()) {
    	$pCount += $row['c'];
    	$chdX .= $row['t'] . ',';
    	$chdY .= $pCount . ',';
    	$nData++;
    }
    
    $pMax = ceil($pCount/100)*100;	// Massima Y arrotondata al centinaio successivo
    
    // X axis labels
    for($i=0; $i<=$xTicks; $i++) {
    	$chxlArr[] = date('d/m H:i', $dTS);
    	$chxpArr[] = ($dTS-$beginTS);
    	$dTS += round(($endTS-$beginTS)/$xTicks);
    }
    
    // Create some random text-encoded data for a line chart.
    $preurl = 'https://chart.googleapis.com/chart?chid=' . md5(uniqid(rand(), true));
    
    $chxl = join($chxlArr, '|');
    $chxp = join($chxpArr, ',');
    $chdX = substr($chdX, 0, -1);
    $chdY = substr($chdY, 0, -1);
    $chd = 't:0,' . $chdX . '|0,' . $chdY;
    $xStep = 100/$xTicks;
    $yStep = 50*100/$pMax;

    // Add data, chart type, chart size, and scale to params.
    $chart = array(
	'cht' => 'lxy', 						// XY graph
	//'chtt' => 'Andamento delle prenotazioni', 			// Title
	'chs' => $xSize . 'x' . $ySize,					// Size
	'chxt' => 'x,y',						// Shown axis
	'chds' => "0," . strval($endTS-$beginTS) . ",0,$pMax,50",	// Data scaling
	'chxr' => "0,0," . strval($endTS-$beginTS) . "|1,0,$pMax",	// Axis scaling
	'chxp' => '0,' . $chxp,						// Axis label positions
	'chxl' => '0:|' . $chxl,					// Custom axis labels
	'chg' => "$xStep,$yStep",					// Grid steps
	'chd' => $chd);							// Data
    $url = $preurl . '&' . http_build_query($chart);
    echo '<img id="graph" src="' . htmlentities($url) . '" alt="Grafico delle prenotazioni" />';
    ?>
<p>Il grafico mostra il numero di prenotazioni effettuate in funzione del tempo.</p>
<p>Powered by <a href="http://code.google.com/apis/chart/">Google Chart Tools</a>.</p>
<?php
	if($pCount > 100) {
		echo '<a href="http://www.youtube.com/watch?v=SiMHTK15Pik">' .
			'<img src="http://4.bp.blogspot.com/-zNb8GGTK-HA/TqfpGrp57FI/AAAAAAAAA1k/4xS1oobD-gI/s1600/over9000.jpg"' .
			'style="width:300px;" alt="Over 9000!" /></a>' .
			'<br /><p style="font-size:200%;">It\'s over ' . floor($pCount/100) . ' HUNDRED!</p>';
	}

showFooter('ca-nstab-grafico');
$db->close();
?>