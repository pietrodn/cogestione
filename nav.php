<?php
function showHeader($selectedId='', $title, $cssFiles=Array(), $jsFiles=Array())
{
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="keywords" content="cogestione liceo einstein pietrodn" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title><?php echo htmlspecialchars($title) ?></title>
		<link rel="icon" href="images/favicon.ico" />
		<link href="css/bootstrap.min.css" rel="stylesheet" />
		<link href="css/common.css" rel="stylesheet" />
		<script src="js/jquery-2.1.1.min.js"></script>
		<script src="js/bootstrap.min.js"></script>


<?php
	foreach($cssFiles as $f) {
		echo '<link rel="stylesheet" href="' . htmlspecialchars($f) . '" />' . "\n";
	}
	
	foreach($jsFiles as $f) {
		echo '<script src="' . htmlspecialchars($f) . '"></script>' . "\n";
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
          <a class="navbar-brand" href=".">Cogestione 2014</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
<?php
	$tablinks = array(
		array('id' => 'ca-nstab-prenota', 'title' => 'Prenota', 'url' => '.'),
		array('id' => 'ca-nstab-elenchi', 'title' => 'Elenchi', 'url' => 'elenchi.php'),
		array('id' => 'ca-nstab-grafico', 'title' => 'Grafico', 'url' => 'grafico.php'),
		array('id' => 'ca-nstab-imposta', 'title' => 'Imposta', 'url' => 'imposta.php')
		);
		
		foreach($tablinks as $i) {
			echo "<li " . ($selectedId == $i['id'] ? 'class="active"' : '') . '><a href="' . $i['url'] . '">' . $i['title'] . '</a></li>';
        }
?>
        </ul>
        <ul class="nav navbar-nav navbar-right">
    <?php
    if(isset($_SESSION['auth'])) {
    ?>
        
        <li class="navbar-text">Signed in as <?php echo htmlspecialchars($_SESSION['username']); ?></li>
        <li><a href="login.php?logout=1">Logout</a></li>
        
    <?php
    } else {
    ?>
    <li <?php echo ($selectedId == 'ca-nstab-login' ? 'class="active"' : '') ?>><a href="login.php">Login</a></li>
    <?php
    }
    ?>
    </ul>
        

        </div><!--/.nav-collapse -->
      </div>
    </div>
	<div class="container">
		<div class="page-header">
			<h1><?php echo htmlspecialchars($title) ?></h1>
		</div>
		<!-- start content -->
<?php
}

function showFooter()
{

?>

	</div>

	<div class="footer">
		<div class="container">
			<p class="text-muted">
				Made by <a href="//github.com/pietrodn">Pietro De Nicolao</a>.
				Licensed under the <a href="//www.gnu.org/copyleft/gpl.html">GNU GPLv3 license</a>.
				<a href="//github.com/pietrodn/cogestione">Source code</a>.
			</p>
		</div>
	</div>
</body>
</html>
<?php
}
?>