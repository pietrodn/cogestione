<?php
require_once("common.php");

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
		showHeader('ca-nstab-login', "Login");
		printError('Autenticazione fallita!');
	}
} else if (isset($_GET['logout'])) {
	destroyLogin();
	showHeader('ca-nstab-login', "Login");
	printSuccess('Logout avvenuto con successo.');
} else {
	showHeader('ca-nstab-login', "Login");
}

 
?>

<!-- Authentication form -->
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">Autenticazione</h3>
		</div>
		<div class="panel-body">
			<fieldset class="form-inline">
				<div class="form-group">
					<label class="sr-only" for="username">Username: </label>
					<input class="form-control" type="text" name="username" id="username" size="20" placeholder="utente" />
				</div>
				<div class="form-group">
					<label class="sr-only" for="password">Password: </label>
					<input class="form-control" type="password" name="password" id="password" size="20" placeholder="password" />
				</div>
				<!-- Login button -->
				<div class="form-group">
					<button class="btn btn-primary" type="submit" name="login">Login</button>
				</div>
			</fieldset>
		</div>
	</div>
</form>
<?php
	showFooter();
?>
