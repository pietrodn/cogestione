<?php
require_once("common.php");

$css = Array('css/login.css');

if(isset($_SESSION['auth']) && !isset($_GET['logout'])) {
	header('Location: ./imposta.php');
	die();
}

$configurator = Configurator::configurator();

if(isset($_POST['login'])) {
	if($configurator->isAuthenticated($_POST['username'], $_POST['password'])) {
		session_regenerate_id(true);
		$_SESSION['auth'] = TRUE;
		$_SESSION['username'] = $_POST['username'];
		header('Location: ./imposta.php');
		//printSuccess('Benvenuto ' . htmlspecialchars($_POST['username']) . ', ti sei autenticato con successo!');
		die();
	} else {
		destroyLogin();
		showHeader('ca-nstab-login', "Login", $css);
		printError('Autenticazione fallita!');
	}
} else if (isset($_GET['logout'])) {
	destroyLogin();
	showHeader('ca-nstab-login', "Login", $css);
	printSuccess('Logout avvenuto con successo.');
} else {
	showHeader('ca-nstab-login', "Login", $css);
}
?>

<!-- Authentication form -->
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-signin">
	<input class="form-control" type="text" name="username" id="username" size="20" placeholder="Utente" />
	<input class="form-control" type="password" name="password" id="password" size="20" placeholder="Password" />
	
	<!-- Login button -->
	<button class="btn btn-primary btn-block btn-lg" type="submit" name="login">Login</button>
</form>

<?php
	showFooter();
?>