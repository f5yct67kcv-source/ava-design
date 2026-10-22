<?php
// Zugang zum Kalender «Gespraeche» fuer termine.php. Die Platzhalter ersetzt
// der Upload-Workflow aus den GitHub-Secrets KALENDER_BENUTZER und
// KALENDER_PASSWORT. Im Repository bleibt die Datei leer, dann ist die
// Buchung aus und gespraech.html zeigt Telefon und Mail.
return [
  'benutzer' => '__KALENDER_BENUTZER__',
  'passwort' => '__KALENDER_PASSWORT__',
];
