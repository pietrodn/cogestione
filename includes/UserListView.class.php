<?php

require_once('Cogestione.class.php');
require_once('ListView.class.php');

class UserListView extends ListView {
	
	private $cogestione;
	private $authenticated = FALSE;
	
	function __construct($list) {
		parent::__construct($list);
		$this->cogestione = new Cogestione();
	}
	
	public function setAuthenticated($auth) {
		$this->authenticated = $auth;
	}
	
	public function render() {
		if(count($this->list)) {
			$blocks = $this->cogestione->blocchi();
			$riepilogo = '';
			$riepilogo .= '<div class="panel panel-success noprint">
				<div class="panel-heading">
					<h3 class="panel-title">Prenotazioni trovate</h3>
				</div>
				<table class="table">';
			$riepilogo .= '<tr class="active">' . ($this->authenticated ? '<th></th>' : '') . '<th>UID</th><th>Nome</th><th>Cognome</th><th>Classe</th>';
			foreach($blocks as $b) {
				$blockTitle = htmlspecialchars($b->title());
				$riepilogo .= "\n<th>$blockTitle</th>";
			}
			$riepilogo .= "\n</tr>";
			foreach($this->list as $u) {
				$riepilogo .= "\n<tr>";
				if($this->authenticated) {
					$riepilogo .= "\n<td>"
						. '<a class="btn btn-danger btn-xs" href="' . $_SERVER['PHP_SELF'] . '?deleteUser=' . intval($u->id()) .'">X</a>'
						. '</td>';
				}
				$riepilogo .= "\n<td>" . htmlspecialchars($u->id()) . '</td>';
				$riepilogo .= "\n<td>" . htmlspecialchars($u->name()) . '</td>';
				$riepilogo .= "\n<td>" . htmlspecialchars($u->surname()) . '</td>';
				$riepilogo .= "\n<td><a href=\"?cercastud=1&class=" . $u->classe()->id() . "\">" . htmlspecialchars($u->classe()->name()) . '</a></td>';
	
				$prenotazione = $this->cogestione->getReservationsForUser($u);
	
				foreach($blocks as $i => $b) {
					$riepilogo .= "\n<td><a href=\"?activity=" . $prenotazione[$i]->id() . "\">" . htmlspecialchars($prenotazione[$i]->title()) . '</a></td>';
				}
			}
			$riepilogo .= '</tr></table></div>';
			echo $riepilogo;
		} else {
			printSuccess('Nessuno studente trovato!');
		}
	}

}

?>