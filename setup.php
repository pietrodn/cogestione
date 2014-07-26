<?php
require_once("common.php");

showHeader('ca-nstab-setup', 'Setup cogestione');

$showForm = TRUE;

if(is_readable(CONFIG_FILE)) {
	printSuccess('Il programma è configurato. Vai alla <a href=".">pagina iniziale</a>.');
	$showForm = FALSE;
} else {
	if (file_exists(CONFIG_FILE)) {
		printError('Il file di configurazione esiste, ma non è leggibile.');
	} else if (!is_writable('.')) {
		printError('Non posso creare il file di configurazione perché la directory corrente non è scrivibile.');
	} else {
		if(isset($_POST['submit'])) {
			
			echo "<p>Provo a connettermi al database...";
			$db = new mysqli($_POST['host'], $_POST['username'], $_POST['password'], $_POST['dbname']);
			if ($db->connect_error) {
				printError('Errore nella connessione al database: ' . $db->connect_error);
			} else {
				echo " <b>OK!</b></p>\n";
				
				echo "<p>Provo a creare il database...</p>";
				$sql_setup = file_get_contents('sql/cogestione.sql');
				$db->multi_query($sql_setup);
				$db->close();
				
				echo "<p>Provo a creare il file di configurazione...";
				$template = file_get_contents('config.template.php');
				$template = str_replace('DB_HOST', addslashes($_POST['host']), $template);
				$template = str_replace('DB_USER', addslashes($_POST['username']), $template);
				$template = str_replace('DB_PASSWORD', addslashes($_POST['password']), $template);
				$template = str_replace('DB_NAME', addslashes($_POST['dbname']), $template);
				
				$old = umask();
				umask(0037);
				$res = file_put_contents(CONFIG_FILE, $template);
				umask($old);
				
				if($res === FALSE) {
					echo " <b>FAIL!</b></p>\n";
					printError('Errore nella creazione del file di configurazione!');
				} else {
					$showForm = FALSE;
					echo " <b>OK!</b></p>\n";
					printSuccess('Il file di configurazione è stato creato correttamente. Vai alla <a href=".">pagina iniziale</a>.');
				}
			}
		}
	}
	
	if($showForm) {
		/* Mostra il form */
		showConfigForm();
	}
}
	
showFooter();

function showConfigForm() {
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">Configura</h3>
		</div>
		<div class="panel-body">
			<fieldset>
				<div class="form-group">
					<label for="host">DB host: </label>
					<input class="form-control" type="text" name="host" id="host" size="20" placeholder="MySQL server host" value="<?php
					if(isset($_POST['host'])) {
						echo htmlspecialchars($_POST['host']);
					} else {
						echo 'localhost';
					}
					?>" />
				</div>
				<div class="form-group">
					<label for="username">DB user: </label>
					<input class="form-control" type="text" name="username" id="username" size="20" placeholder="MySQL username" value="<?php
					if(isset($_POST['username'])) {
						echo htmlspecialchars($_POST['username']);
					}
					?>" />
				</div>
				<div class="form-group">
					<label for="password">DB password: </label>
					<input class="form-control" type="password" name="password" id="password" size="20" placeholder="password" value="" />
				</div>
				<div class="form-group">
					<label for="dbname">DB name: </label>
					<input class="form-control" type="text" name="dbname" id="dbname" size="20" placeholder="MySQL DB name" value="<?php
					if(isset($_POST['dbname'])) {
						echo htmlspecialchars($_POST['dbname']);
					} else {
						echo 'cogestione';
					}
					?>" />
				</div>
				<!-- Submit button -->
				<div class="form-group">
					<button class="btn btn-primary" type="submit" name="submit">Configura</button>
				</div>
			</fieldset>
		</div>
	</div>
</form>
<?php
}
?>