<?php
// Nimmt die Anfrage aus dem Kostenrechner entgegen und schickt sie als Mail.
// Laeuft beim Hoster (Hostpoint), braucht nur PHP und dessen mail()-Funktion,
// keine Bibliothek, keine Datenbank. Anfragen werden nicht abgelegt, nur die
// Bremse merkt sich eine Stunde lang einen Hash der IP und die Uhrzeit.

// EMPFAENGER: wo die Anfragen ankommen sollen.
// ABSENDER:   muss ein Postfach auf der eigenen Domain bei Hostpoint sein,
//             sonst landen die Mails beim Empfaenger im Spam oder gar nicht.
//             Antworten gehen ueber Reply-To an den Kunden, nicht hierher.
const EMPFAENGER = 'adrian@formateins.ch';
const ABSENDER   = 'adrian@formateins.ch';

// Hoechstens so viele Anfragen pro Absender-IP und Stunde.
const GRENZE_PRO_STUNDE = 5;

ini_set('display_errors', '0');
date_default_timezone_set('Europe/Zurich');
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function ende(int $code, string $grund = ''): void {
  http_response_code($code);
  echo json_encode($code === 200 ? ['ok' => true] : ['ok' => false, 'grund' => $grund]);
  exit;
}

// Eine Zeile Text: Zeilenumbrueche raus (sonst lassen sich Kopfzeilen einschleusen),
// Leerraum glaetten, Laenge begrenzen.
function zeile(string $schluessel, int $max = 200): string {
  $wert = (string) ($_POST[$schluessel] ?? '');
  $wert = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $wert) ?? '';
  return mb_substr(trim(preg_replace('/\s+/u', ' ', $wert) ?? ''), 0, $max);
}
function absatz(string $schluessel, int $max = 4000): string {
  $wert = str_replace("\r\n", "\n", (string) ($_POST[$schluessel] ?? ''));
  $wert = preg_replace('/[\x00-\x08\x0B-\x1F\x7F]+/u', '', $wert) ?? '';
  return mb_substr(trim($wert), 0, $max);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') ende(405, 'nur POST');
if (EMPFAENGER === '' || ABSENDER === '') ende(503, 'nicht eingerichtet');

// Nur Anfragen von der eigenen Seite. Fehlt der Origin-Kopf (alte Browser), lassen wir durch.
$herkunft = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($herkunft !== '' && parse_url($herkunft, PHP_URL_HOST) !== parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)) {
  ende(403, 'fremde Herkunft');
}

// Spamfalle: Menschen sehen das Feld nicht. Bots bekommen ein falsches "ok".
if (zeile('_gotcha') !== '') ende(200);

// Bremse gegen Massenversand. Nur ein Hash der IP wird kurz abgelegt, nie die IP selbst.
$ablage = sys_get_temp_dir() . '/anfrage_' . hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . __FILE__);
$jetzt = time();
$zeiten = array_filter(
  array_map('intval', @file($ablage, FILE_IGNORE_NEW_LINES) ?: []),
  fn($t) => $t > $jetzt - 3600
);
if (count($zeiten) >= GRENZE_PRO_STUNDE) ende(429, 'zu viele Anfragen');

$name  = zeile('name');
$mail  = zeile('mail');
if ($name === '') ende(400, 'Name fehlt');
if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) ende(400, 'E-Mail ungueltig');

$betrieb   = zeile('betrieb');
$telefon   = zeile('tel', 40);
$bemerkung = absatz('bemerkung');

$fragen = [
  'umfang'     => 'Umfang',
  'bestand'    => 'Ausgangslage',
  'texte'      => 'Texte',
  'bilder'     => 'Bilder',
  'logo'       => 'Logo und Auftritt',
  'funktionen' => 'Funktionen',
  'zeit'       => 'Zeitrahmen',
];

$text = [];
$text[] = 'Preisrahmen: ' . (zeile('rahmen', 60) ?: '–');
if (($separat = zeile('separat')) !== '') $text[] = 'Separat offerieren: ' . $separat;
$text[] = '';
foreach ($fragen as $schluessel => $titel) {
  if (($wert = zeile($schluessel, 300)) !== '') $text[] = $titel . ': ' . $wert;
}
$text[] = '';
$text[] = 'Name: '    . $name;
$text[] = 'Betrieb: ' . ($betrieb ?: '–');
$text[] = 'E-Mail: '  . $mail;
$text[] = 'Telefon: ' . ($telefon ?: '–');
$text[] = '';
$text[] = 'Bemerkung:';
$text[] = $bemerkung ?: '–';
$text[] = '';
$text[] = '— gesendet über den Kostenrechner, ' . date('d.m.Y H:i');

$betreff = 'Anfrage Website · ' . ($betrieb ?: $name);
$koepfe = [
  'From'                      => 'Kostenrechner <' . ABSENDER . '>',
  'Reply-To'                  => $mail,
  'MIME-Version'              => '1.0',
  'Content-Type'              => 'text/plain; charset=UTF-8',
  'Content-Transfer-Encoding' => '8bit',
];

mb_internal_encoding('UTF-8');
$gesendet = mail(
  EMPFAENGER,
  mb_encode_mimeheader($betreff, 'UTF-8', 'Q'),
  implode("\n", $text),
  $koepfe,
  '-f' . ABSENDER
);
if (!$gesendet) ende(500, 'Versand fehlgeschlagen');

$zeiten[] = $jetzt;
@file_put_contents($ablage, implode("\n", $zeiten), LOCK_EX);
ende(200);
