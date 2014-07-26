cogestione
==========

Questo è il mio software per l'organizzazione della cogestione.
Devo ancora stabilirne le politiche di distribuzione.

L'uso dell'applicativo dovrebbe risultare ovvio a partire dall'interfaccia.
In caso contrario, essa necessiterà di miglioramenti.

Installazione
-------------

1. Copiare la cartella "cogestione" nel proprio spazio web.
2. Importare il file sql/cogestione.sql in un database di MySQL.
3. Copiare config.default.php in config.php
4. Modificare config.php con le impostazioni di MySQL e le credenziali per modificare le attività.
5. Ora potete andare sul sito nel vostro browser e impostare tutto attraverso la pagina "Imposta".

![Schermata di impostazione della cogestione](http://i.imgur.com/5p7TCgc.png)

Blocchi
-------

Innanzitutto occorre impostare i blocchi attraverso la pagina **Imposta**.
Sarà richiesto il login con le credenziali impostate in config.php.

Si aggiungerà un numero opportuno di **blocchi**.
Un **blocco** è un intervallo temporale in cui si svolgono contemporaneamente più attività.
Ad esempio si potranno impostare 4 blocchi:
- Lunedì dalle 9 alle 11
- Lunedì dalle 11 alle 13
- Martedì dalle 8.30 alle 10.30
- Martedì dalle 10.30 alle 12.30

La descrizione di ogni blocco è assolutamente arbitraria.
Per aggiungere dei blocchi, digitare il numero di blocchi che si vogliono aggiungere nella casella "Aggiungi ... blocchi" e poi, in basso, fare clic su "Modifica attività".

Attività
--------

Poi, per ogni blocco bisognerà aggiungere le **attività**.
Non è necessario che ogni blocco abbia il medesimo numero di attività.
Per aggiungere delle attività ad un blocco, digitare il numero di attività da inserire nella casella "Aggiungi ... attività" che si trova in fondo alla colonna del blocco. Poi fare clic su "Modifica attività".

Ciascuna attività ha le seguenti proprietà:
- Titolo
- Descrizione
- Capienza (numero massimo di posti): una capienza di 0 significa "illimitato"
- Flag VM18 "riservata alle quarte e alle quinte"

Tutte queste proprietà sono facilmente modificabili dalla pagina "Imposta".

Merita una nota la procedura di rimozione di attività e blocchi.
Attività e blocchi possono essere cancellati selezionando le spunte "DEL" vicino ai loro nomi, nella pagina di impostazione.
- Cancellando un blocco si cancellano anche tutte le attività in esso contenute (quelle nella stessa colonna).
- Cancellando un'attività si cancellano anche tutte le relative prenotazioni.

Per questo _è sconsigliabile cancellare blocchi e attività una volta che siano già state inserite delle prenotazioni nel sistema_.
Non c'è invece alcun problema a rinominare le attività, cambiarne le descrizioni o la capienza: ogni attività è dotata di un identificatore univoco, memorizzato nel database, che tiene traccia delle relative prenotazioni anche se si cambiano il titolo o le proprietà dell'attività.

- Se si modifica la capienza di un'attività, in nessun caso saranno cancellate le prenotazioni relative.
- Se si imposta il flag VM18, non sarà comunque rimossa alcuna prenotazione, neppure quelle non rispondenti al requisito.

Classi
------

È possibile modificare le classi dalla pagina **Imposta**, specificando l'elenco completo delle classi nell'apposita casella di testo. I nomi delle classi devono essere separati da punto e virgola (";"); la spaziatura verrà ignorata.

Se si tenta di rimuovere una classe con delle prenotazioni associate, il sistema lo impedirà. Bisognerà dunque rimuovere prima tutte le prenotazioni associate alla classe che si vuole eliminare.

Prenotazioni
------------

È possibile cancellare una prenotazione singola cercando il nome dello studente nell'interfaccia degli elenchi e cliccando sulla "X" rossa a fianco.
Oppure è possibile prendere nota dell'UID e inserirlo nel form di cancellazione nella schermata Imposta.
In entrambi i casi occorre aver fatto il login.

È possibile cancellare **tutte** le prenotazioni selezionando l'apposita casella nella schermata **Imposta**.

La cancellazione delle prenotazioni è sempre irreversibile.

Abilitazione
------------

Le prenotazioni si possono attivare o disattivare in modalità automatica o manuale, dalla schermata **Imposta**.

* **Modalità automatica**: si specificano data e ora di inizio e di fine. Il sistema accetterà prenotazioni a partire dall'ora di partenza e cesserà di farlo all'ora di chiusura specificata. Una volta impostato, il sistema non necessita di ulteriore supervisione. Gli orari di inizio e di fine sono visibili nella pagina **Prenota**.
* **Modalità manuale**: il sistema si attiva e si disattiva manualmente dalla schermata Imposta.

![Schermata di prenotazione per gli utenti](http://i.imgur.com/dZD4StH.png)

Elenchi
-------

È possibile visualizzare i nomi degli studenti prenotati dalla pagina **Elenchi**.
Questa interfaccia ha due modi di utilizzo.

Si può selezionare un'attività dalla tabella e verrà visualizzato l'elenco dei partecipanti a quell'attività. La pagina è anche stampabile per avere una comoda lista cartacea di prenotati.

Si può anche cercare un utente per nome, cognome e/o classe. È possibile specificare anche uno solo di questi campi, per esempio per vedere dove sono prenotati tutti gli studenti di una data classe.

Da quest'ultima vista, se si è fatto il login in zona amministrativa, si possono anche cancellare le prenotazioni cliccando sulla "X" rossa accanto alla riga dell'utente prenotato. Non sarà richiesta ulteriore conferma.

Grafico
-------

Il grafico mostra l'andamento del numero cumulativo di iscrizioni in funzione del tempo.
Serve più per curiosità che per altro. È ancora uno strumento da migliorare.

To do
-----

* Blacklist nomi
* Ordinamento liste per nome, cognome o classe
* Impostazione delle credenziali di autenticazione via web, da memorizzare in database (con hash)
* Possibilità di cancellare le prenotazioni dalla ricerca per attività
* Template per rendere più leggibile il codice (?)
* Pulizia codice