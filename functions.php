<?php

function printError($message) {
	echo '<div class="panel panel-danger">
			  <div class="panel-heading">
				<h3 class="panel-title">Errore</h3>
			  </div>
			  <div class="panel-body">
				' . htmlentities($message) . '
			  </div>
		</div>';
}

function printSuccess($message) {
	echo '<div class="panel panel-success">
			  <div class="panel-heading">
				<h3 class="panel-title">Operazione riuscita</h3>
			  </div>
			  <div class="panel-body">
				' . htmlentities($message) . '
			  </div>
		</div>';
}

?>