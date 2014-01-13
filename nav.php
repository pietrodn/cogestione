<?php
require_once("config.php");

function showHeader($title, $cssFiles=Array(), $jsFiles=Array())
{
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo htmlentities($title) ?></title>
    <link rel="icon" href="includes/favicon.ico" />
    <link rel="stylesheet" href="skins/vector/screen.css" media="screen" />
    <link rel="stylesheet" href="skins/common/shared.css" media="screen" />
    <link rel="stylesheet" href="skins/common/commonPrint.css" media="print" />
<?php
	foreach($cssFiles as $f) {
		echo '<link rel="stylesheet" href="' . htmlentities($f, ENT_QUOTES) . '" />' . "\n";
	}
	
	foreach($jsFiles as $f) {
		echo '<script src="' . htmlentities($f, ENT_QUOTES) . '"></script>' . "\n";
	}
?>
</head>
<body class="mediawiki ltr ns-0 ns-subject skin-vector">
    <div id="mw-page-base" class="noprint"></div>
    <div id="mw-head-base" class="noprint"></div>
	<div id="content">
    	<a id="top"></a>
    	<h1 id="firstHeading" class="firstHeading"><?php echo htmlentities($title) ?></h1>
    	<div id="bodyContent">
    	   <h3 id="siteSub">Da Einsteinwiki.</h3>
<?php
}

function showFooter($selectedId='')
{
	$tablinks = array(
		array('id' => 'ca-nstab-prenota', 'title' => 'Prenota', 'url' => 'cogestione.php'),
		array('id' => 'ca-nstab-elenchi', 'title' => 'Elenchi', 'url' => 'elenchiCogestione.php'),
		array('id' => 'ca-nstab-grafico', 'title' => 'Grafico', 'url' => 'graficoPrenotazioni.php'),
		array('id' => 'ca-nstab-imposta', 'title' => 'Imposta', 'url' => 'impostaCogestione.php')
		);

?>
<div class="visualClear"></div>
</div>
</div>
<div id="mw-head" class="noprint">
<div id="left-navigation">
<div id="p-namespaces" class="vectorTabs">
	<h5>Namespace</h5>
	<ul>
<?php
	foreach($tablinks as $i) {
		echo '<li id="' . $i['id'] . '" ' . ($i['id']==$selectedId ? 'class="selected"' : '') . '>'
			. '<span><a href="' . $i['url'] . '">' . $i['title'] . '</a></span></li>';
	}
?>
    </ul>
</div>
</div>
</div>
<div id="mw-panel" class="noprint">
    <!-- logo -->
	<div id="p-logo"><a style="background-image: url(includes/Wiki.png);" href="/wiki/Pagina_principale"  title="Visita la pagina principale"></a></div>
<!-- /logo -->

<!-- Cogestione -->
<div class="portal" id='p-cogestione'>
	<h5>Cogestione</h5>
	<div class="body">
    	<ul>
    		<li id="n-Cogestione-Einstein">
    		  <a href="http://it.cogestioneinstein.wikia.com/wiki/Cogestione_Einstein_Wiki">Cos'è la cogestione</a></li>
    		<!--<li id="n-Cogestione-2011">
    		  <a href="http://it.cogestioneinstein.wikia.com/wiki/Organizzazione_2012">Attività e orario</a></li>-->
    		<li id="n-circolare">
    		  <a href="http://www.liceoeinsteinmilano.it/cms/public/circolari1213/280prenotazione%20cogestione.22.02.13.pdf">Indicazioni operative</a></li>
    		<li id="n-prenotazioni">
    		  <a href="http://cogestione2014.netsons.org/">Prenotazioni cogestione</a></li>
    	</ul>
    </div>
</div>
<!-- /Cogestione -->
</div>

<div id="footer">
    <ul id="footer-places">
        <li id="footer-places-about"><a href="https://pietrodn.wordpress.com" title="Denilog">Creato da Pietro De Nicolao</a></li>
    </ul>
    <ul id="footer-icons" class="noprint">
        <li><a href="http://validator.w3.org/check?uri=referer"><img
        src="http://www.w3.org/Icons/valid-xhtml10"
        alt="Valid XHTML 1.0 Strict" height="31" width="88" style="border:0;" /></a></li>
        <li><a href="http://jigsaw.w3.org/css-validator/check/referer">
        <img style="border:0;width:88px;height:31px"
        src="http://jigsaw.w3.org/css-validator/images/vcss"
        alt="CSS Valido!" />
        </a></li>
    </ul>
    <div style="clear:both"></div>
</div>
</body>
</html>
<?php
}
?>