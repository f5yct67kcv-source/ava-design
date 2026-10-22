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

// Zeitfalle: Kein Mensch beantwortet sieben Fragen und fuellt das Formular in
// weniger Sekunden aus. Der Rechner schickt mit, wie lange die Seite offen war.
const MINDESTDAUER_MS = 8000;

// Antworten fuer die Bestaetigung an den Kunden, siehe bestaetigen() unten.
// Muss zu den data-wert-Angaben in kostenrechner.html passen.
const ANTWORTEN = [
  'umfang'     => ['Umfang', ['eine' => 'Eine Seite', 'firma' => 'Firmenwebsite, 3 bis 5 Seiten', 'gross' => 'Grössere Website, ab 6 Seiten']],
  'bestand'    => ['Ausgangslage', ['nein' => 'Noch keine Website', 'ersetzen' => 'Bestehende ersetzen', 'ueberarbeiten' => 'Bestehende überarbeiten']],
  'texte'      => ['Texte', ['da' => 'Sind vorhanden', 'teil' => 'Teilweise vorhanden', 'ich' => 'Werden neu geschrieben']],
  'bilder'     => ['Bilder', ['da' => 'Sind vorhanden', 'teil' => 'Teilweise vorhanden', 'shooting' => 'Neue Fotos vor Ort', 'video' => 'Video vor Ort']],
  'logo'       => ['Logo und Auftritt', ['da' => 'Beides vorhanden', 'teil' => 'Nur ein Logo', 'neu' => 'Wird neu gestaltet']],
  'funktionen' => ['Funktionen', ['nein' => 'Keine', 'sprache' => 'Zweite Sprache', 'buchung' => 'Online-Buchung', 'beides' => 'Zweite Sprache und Online-Buchung']],
  'zeit'       => ['Zeitrahmen', ['offen' => 'Keine Eile', 'normal' => 'In den nächsten zwei Monaten', 'datum' => 'Bis zu einem festen Datum']],
];

// Hoechstens eine Bestaetigung pro Adresse und Tag.
const BESTAETIGUNG_PRO_TAG = 1;

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
// Schweizer Nummer (0 oder +41/0041 plus neun Ziffern) oder auslaendische mit
// Vorwahl. Gleiche Regel wie im Browser (telGueltig in kostenrechner.html und
// gespraech.html).
function telefonGueltig(string $t): bool {
  if (!preg_match('~^[+0-9 ()/.\-]+$~', $t)) return false;
  $n = preg_replace('/^00/', '+', preg_replace('/[^\d+]/', '', $t) ?? '') ?? '';
  return preg_match('/^0[1-9]\d{8}$/', $n) === 1
    || preg_match('/^\+410?[1-9]\d{8}$/', $n) === 1
    || preg_match('/^\+(?!41)[1-9]\d{6,13}$/', $n) === 1;
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

// Spamfallen: Menschen sehen das Feld nicht und brauchen laenger als ein paar
// Sekunden. Bots bekommen ein falsches "ok" und merken nichts.
if (zeile('_gotcha') !== '') ende(200);
if ((int) zeile('dauer', 12) < MINDESTDAUER_MS) ende(200);

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
if (!telefonGueltig($telefon)) ende(400, 'Telefon fehlt');
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

// Erst jetzt, wo die Anfrage sicher bei mir ist, die Bestaetigung an den Kunden.
// Scheitert sie, merkt der Kunde nichts davon: Die Anfrage ist ja angekommen.
try { bestaetigen($name, $mail); } catch (Throwable $e) { error_log('Bestaetigung: ' . $e->getMessage()); }
ende(200);


// ---------- Bestaetigung an den Kunden ----------
// Das Formular verschickt Mails an eine Adresse, die jemand selbst eintippt.
// Damit niemand darueber Werbung an fremde Adressen schickt, steht in der
// Bestaetigung nichts frei Eingegebenes ausser einem geprueften Vornamen:
// keine Bemerkung, kein Betrieb, keine URL. Die Antworten kommen aus der
// Liste ANTWORTEN oben, nicht aus dem mitgeschickten Text.

function bestaetigen(string $name, string $mail): void {
  // Steht im Namen ein Link oder eine Adresse, ist das keine echte Anfrage.
  if (preg_match('~https?:|www\.|@|\.(ch|com|net|org|de|info|io|biz|ru|xyz|top)\b~i', $name)) return;

  // Vorname nur, wenn er wie ein Name aussieht. Sonst ohne Namen, nicht ohne Mail.
  $vorname = mb_substr(preg_split('/\s+/u', $name)[0] ?? '', 0, 30);
  if (!preg_match('/^[\p{L}][\p{L}\'’\-\.]*$/u', $vorname)) $vorname = '';

  $ablage = sys_get_temp_dir() . '/bestaetigung_' . hash('sha256', mb_strtolower($mail) . __FILE__);
  $jetzt = time();
  $zeiten = array_filter(
    array_map('intval', @file($ablage, FILE_IGNORE_NEW_LINES) ?: []),
    fn($t) => $t > $jetzt - 86400
  );
  if (count($zeiten) >= BESTAETIGUNG_PRO_TAG) return;

  $zeilen = [];
  foreach (ANTWORTEN as $schluessel => [$titel, $werte]) {
    $wert = zeile('wert_' . $schluessel, 20);
    if (!isset($werte[$wert])) continue;
    $text = $werte[$wert];
    if ($schluessel === 'zeit' && $wert === 'datum' && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', zeile('datum', 10), $d)) {
      $text = 'Bis ' . $d[3] . '.' . $d[2] . '.' . $d[1];
    }
    $zeilen[] = [$titel, $text];
  }
  $separat = [];
  if (in_array(zeile('wert_bilder', 20), ['shooting', 'video'], true)) $separat[] = zeile('wert_bilder', 20) === 'video' ? 'Video vor Ort' : 'Fotos vor Ort';
  if (zeile('wert_logo', 20) === 'neu') $separat[] = 'Logo und visueller Auftritt';

  // Nur ein Rahmen in der Form, wie ihn der Rechner baut.
  $rahmen = zeile('rahmen', 40);
  if (!preg_match('/^CHF [0-9’\'\s]+ – [0-9’\'\s]+$/u', $rahmen)) $rahmen = '';

  $anrede = $vorname !== '' ? 'Danke, ' . $vorname . '.' : 'Danke.';
  $betreff = 'Deine Anfrage ist angekommen · formateins';

  // Textfassung fuer Mailprogramme ohne HTML.
  $text = [$anrede . ' Deine Anfrage ist da.', '',
    'Ich schaue mir deine Angaben an und rufe dich in der Regel innerhalb von zwei Arbeitstagen kurz an, um zu verstehen, was du dir vorstellst. Danach bekommst du ein Festpreisangebot.', ''];
  if ($rahmen !== '') {
    $text[] = 'Dein Preisrahmen: ' . $rahmen;
    $text[] = 'Unverbindlicher Richtwert. Verbindlich ist das Festpreisangebot.';
    if ($separat) $text[] = 'Separat offeriert: ' . implode(', ', $separat);
    $text[] = '';
  }
  if ($zeilen) {
    $text[] = 'Deine Angaben:';
    foreach ($zeilen as [$t, $w]) $text[] = '- ' . $t . ': ' . $w;
    $text[] = '';
    $text[] = 'Stimmt etwas nicht oder fehlt etwas? Antworte einfach auf diese Mail.';
    $text[] = '';
  }
  array_push($text, 'Bis bald', 'Adrian von Arb', '079 686 84 86 · formateins.ch', '', '--',
    'formateins, eine Marke der pzu consulting gmbh · Hochgasse 7, 4632 Trimbach',
    'Du bekommst diese Mail einmalig, weil du den Kostenrechner ausgefüllt hast. Kein Newsletter.');

  $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
  $serif = "Georgia,'Times New Roman',serif";
  $sans  = 'Helvetica,Arial,sans-serif';
  $etikett = "margin:0;font-family:$sans;font-size:12px;letter-spacing:2px;text-transform:uppercase;font-weight:700";

  $rahmenHtml = '';
  if ($rahmen !== '') {
    $separatHtml = $separat ? '<p style="margin:6px 0 0;font-family:' . $sans . ';font-size:13px;line-height:1.5;color:#6b7a83">Separat offeriert: ' . $h(implode(', ', $separat)) . '</p>' : '';
    $rahmenHtml = <<<HTML
  <tr><td style="padding:28px 36px 0">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f4ef;border-left:3px solid #ff7a3d;border-radius:4px">
      <tr><td style="padding:20px 22px">
        <p style="$etikett;color:#6b7a83">Dein Preisrahmen</p>
        <p style="margin:6px 0 0;font-family:$serif;font-size:30px;color:#0f1a21;font-weight:700">{$h($rahmen)}</p>
        <p style="margin:6px 0 0;font-family:$sans;font-size:13px;line-height:1.5;color:#6b7a83">Unverbindlicher Richtwert. Verbindlich ist das Festpreisangebot.</p>
        $separatHtml
      </td></tr>
    </table>
  </td></tr>
HTML;
  }

  $angabenHtml = '';
  if ($zeilen) {
    $reihen = '';
    foreach ($zeilen as $i => [$t, $w]) {
      $unten = $i === count($zeilen) - 1 ? 'border-bottom:1px solid #e6e2da;' : '';
      $reihen .= '<tr><td style="padding:9px 12px 9px 0;border-top:1px solid #e6e2da;' . $unten . 'color:#6b7a83">' . $h($t) . '</td>'
        . '<td align="right" style="padding:9px 0;border-top:1px solid #e6e2da;' . $unten . 'font-weight:600">' . $h($w) . '</td></tr>';
    }
    $angabenHtml = <<<HTML
  <tr><td style="padding:28px 36px 0">
    <p style="$etikett;color:#6b7a83;margin-bottom:8px">Deine Angaben</p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-family:$sans;font-size:14px;color:#0f1a21">$reihen</table>
    <p style="margin:12px 0 0;font-family:$sans;font-size:13px;line-height:1.5;color:#6b7a83">Stimmt etwas nicht oder fehlt etwas? Antworte einfach auf diese Mail.</p>
  </td></tr>
HTML;
  }

  $html = <<<HTML
<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="color-scheme" content="light"><title>{$h($betreff)}</title></head>
<body style="margin:0;padding:0;background:#ecebe7">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#ecebe7"><tr><td align="center" style="padding:32px 12px">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:6px;overflow:hidden">
  <tr><td style="background:#0f1a21;padding:26px 36px"><span style="font-family:$serif;font-size:24px;font-weight:700;color:#f3efe7">format</span><span style="font-family:$serif;font-size:24px;font-weight:700;color:#ff7a3d">eins</span></td></tr>
  <tr><td style="padding:40px 36px 8px">
    <p style="$etikett;color:#ff7a3d;margin-bottom:6px">Anfrage erhalten</p>
    <h1 style="margin:0;font-family:$serif;font-size:32px;line-height:1.15;color:#0f1a21;font-weight:700">{$h($anrede)}<br>Deine Anfrage ist da.</h1>
    <p style="margin:18px 0 0;font-family:$sans;font-size:16px;line-height:1.6;color:#3b4a53">Ich schaue mir deine Angaben an und rufe dich in der Regel innerhalb von zwei Arbeitstagen kurz an, um zu verstehen, was du dir vorstellst. Danach bekommst du ein Festpreisangebot.</p>
  </td></tr>
$rahmenHtml
$angabenHtml
  <tr><td style="padding:34px 36px 36px">
    <p style="margin:0;font-family:$sans;font-size:16px;line-height:1.6;color:#3b4a53">Bis bald<br><span style="font-family:$serif;font-size:20px;color:#0f1a21;font-weight:700">Adrian von Arb</span></p>
    <p style="margin:10px 0 0;font-family:$sans;font-size:14px;line-height:1.6;color:#6b7a83"><a href="tel:+41796868486" style="color:#0f1a21">079 686 84 86</a> · <a href="https://www.formateins.ch" style="color:#0f1a21">formateins.ch</a></p>
  </td></tr>
  <tr><td style="background:#f6f4ef;padding:18px 36px;font-family:$sans;font-size:12px;line-height:1.5;color:#8a969c">formateins, eine Marke der pzu consulting gmbh · Hochgasse 7, 4632 Trimbach<br>Du bekommst diese Mail einmalig, weil du den Kostenrechner ausgefüllt hast. Kein Newsletter.</td></tr>
</table>
</td></tr></table>
</body></html>
HTML;

  $grenze = 'grenze_' . bin2hex(random_bytes(12));
  $koerper = "--$grenze\r\n"
    . "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
    . chunk_split(base64_encode(implode("\n", $text)))
    . "--$grenze\r\n"
    . "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
    . chunk_split(base64_encode($html))
    . "--$grenze--\r\n";

  $koepfe = [
    'From'           => mb_encode_mimeheader('Adrian von Arb · formateins', 'UTF-8', 'Q') . ' <' . ABSENDER . '>',
    'Reply-To'       => EMPFAENGER,
    'MIME-Version'   => '1.0',
    'Content-Type'   => 'multipart/alternative; boundary="' . $grenze . '"',
    // Keine Abwesenheitsnotizen zurueck, das ist eine automatische Mail.
    'Auto-Submitted' => 'auto-replied',
  ];

  $ok = mail($mail, mb_encode_mimeheader($betreff, 'UTF-8', 'Q'), $koerper, $koepfe, '-f' . ABSENDER);
  if ($ok) {
    $zeiten[] = $jetzt;
    @file_put_contents($ablage, implode("\n", $zeiten), LOCK_EX);
  }
}
