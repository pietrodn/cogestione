<?php
require_once("config.php");

function showHeader($selectedId='', $title, $cssFiles=Array(), $jsFiles=Array())
{
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="keywords" content="cogestione liceo einstein pietrodn" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo htmlentities($title) ?></title>
		<link rel="icon" href="images/favicon.ico" />
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<script src="js/jquery-2.1.1.min.js"></script>
		<script src="js/bootstrap.min.js"></script>


<?php
	foreach($cssFiles as $f) {
		echo '<link rel="stylesheet" href="' . htmlentities($f, ENT_QUOTES) . '" />' . "\n";
	}
	
	foreach($jsFiles as $f) {
		echo '<script src="' . htmlentities($f, ENT_QUOTES) . '"></script>' . "\n";
	}
?>
</head>
<body>
	<!-- Fixed navbar -->
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href=".">Cogestione</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
<?php
	$tablinks = array(
		array('id' => 'ca-nstab-prenota', 'title' => 'Prenota', 'url' => '.'),
		array('id' => 'ca-nstab-elenchi', 'title' => 'Elenchi', 'url' => 'elenchiCogestione.php'),
		array('id' => 'ca-nstab-grafico', 'title' => 'Grafico', 'url' => 'graficoPrenotazioni.php'),
		array('id' => 'ca-nstab-imposta', 'title' => 'Imposta', 'url' => 'impostaCogestione.php')
		);
		
		foreach($tablinks as $i) {
			echo "<li " . ($selectedId == $i['id'] ? 'class="active"' : '') . '><a href="' . $i['url'] . '">' . $i['title'] . '</a></li>';
        }
?>
        </ul>
        <ul class="nav navbar-nav navbar-right">
    <?php
    if($_SESSION['auth']) {
    ?>
        
        <li class="navbar-text">Signed in as <?php echo htmlentities($_SESSION['username']); ?></li>
        <li><a href="login.php?logout=1">Logout</a></li>
        
    <?php
    } else {
    ?>
    <li><a <?php echo ($selectedId == 'ca-nstab-login' ? 'class="active"' : '') ?> href="login.php">Login</a></li>
    <?php
    }
    ?>
    </ul>
        

        </div><!--/.nav-collapse -->
      </div>
    </div>
    <div class="jumbotron">
		<div class="container">
			<div class="media">
				<!--<a class="pull-left" href="#">
					<img id="wikitech-logo" class="media-object" src="images/Wiki.png" alt="Einsteinwiki">
				</a>-->
				<div class="media-body">
					<h1><?php echo htmlentities($title) ?>
					</h1>
				</div>
			</div>
			<!-- start content -->
<?php
}

function showFooter()
{

?>
</div>
    
	</body>
</html>
<?php
}
?>