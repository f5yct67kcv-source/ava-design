<?php
// Terminbuchung fuer gespraech.html. Liest freie Fenster aus dem Kalender
// «Gespraeche» in Hostpoint Cloud Office (CalDAV) und traegt Buchungen dort ein.
//
// So steuert Adrian die Termine, ohne die Website anzufassen:
//   - Termin mit dem Titel «frei» im Kalender «Gespraeche»: in diesem Fenster
//     ist buchbar. Feste Zeiten als wiederkehrender Termin.
//   - Alles andere in diesem Kalender blockiert, auch ganztaegig («gesperrt»)
//     und die gebuchten Gespraeche selbst.
// Buchbar ist nur, was ausdruecklich freigegeben ist.
//
// GET                    freie Zeiten als JSON
// POST aktion=buchen     Termin eintragen, Bestaetigung an beide
// POST aktion=absagen    Termin loeschen (Link aus der Bestaetigung)

const KALENDER   = 'https://caldav.hostpoint.ch/caldav/Y2FsOi8vMC8xMDE/';
const EMPFAENGER = 'adrian@formateins.ch';
const ABSENDER   = 'adrian@formateins.ch';
const TELEFON    = '079 686 84 86';
// Fest, nicht aus dem Host-Kopf: sonst liesse sich der Absage-Link in der
// Bestaetigung auf eine fremde Seite umbiegen.
const SEITE      = 'https://www.formateins.ch';
// Zoom-Link fuer Video-Gespraeche, steht in der Bestaetigung. Leer: Adrian
// schickt den Link von Hand.
const VIDEO_LINK = '';

const DAUER_MIN       = 30;  // Laenge eines Gespraechs
const PUFFER_MIN      = 15;  // Abstand zu allem anderen im Kalender
const VORLAUF_TAGE    = 1;   // 1 = fruehestens morgen
const REICHWEITE_TAGE = 28;  // so weit im Voraus buchbar
const ZEITZONE        = 'Europe/Zurich';

const GRENZE_PRO_STUNDE = 5;     // Buchungen pro Absender-IP
const MINDESTDAUER_MS   = 5000;  // Zeitfalle wie in anfrage.php
const ZWISCHENSPEICHER_S = 60;   // freie Zeiten so lange nicht neu abfragen

const ARTEN = ['telefon' => 'Telefon', 'video' => 'Video'];

ini_set('display_errors', '0');
date_default_timezone_set(ZEITZONE);
mb_internal_encoding('UTF-8');
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

function ende(int $code, array $daten = []): void {
  http_response_code($code);
  echo json_encode(['ok' => $code === 200] + $daten, JSON_UNESCAPED_UNICODE);
  exit;
}

// Wie in anfrage.php: eine Zeile ohne Steuerzeichen, gekuerzt.
function zeile(string $schluessel, int $max = 200): string {
  $wert = (string) ($_POST[$schluessel] ?? '');
  $wert = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $wert) ?? '';
  return mb_substr(trim(preg_replace('/\s+/u', ' ', $wert) ?? ''), 0, $max);
}

// Zugangsdaten kommen beim Upload aus den GitHub-Secrets (siehe Workflow).
// Im Repository stehen nur Platzhalter, dann ist die Buchung aus.
$zugang = @include __DIR__ . '/kalender-zugang.php';
$eingerichtet = is_array($zugang)
  && ($zugang['benutzer'] ?? '') !== '' && !str_starts_with($zugang['benutzer'], '__')
  && ($zugang['passwort'] ?? '') !== '' && !str_starts_with($zugang['passwort'], '__');
if (!$eingerichtet) ende(503, ['grund' => 'nicht eingerichtet']);


// ---------- CalDAV ----------

function dav(string $methode, string $adresse, string $koerper = '', array $koepfe = []): array {
  global $zugang;
  $c = curl_init($adresse);
  $optionen = [
    CURLOPT_CUSTOMREQUEST  => $methode,
    CURLOPT_USERPWD        => $zugang['benutzer'] . ':' . $zugang['passwort'],
    CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 6,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => $koepfe,
  ];
  if ($koerper !== '') $optionen[CURLOPT_POSTFIELDS] = $koerper;
  curl_setopt_array($c, $optionen);
  $antwort = curl_exec($c);
  $code = (int) curl_getinfo($c, CURLINFO_RESPONSE_CODE);
  curl_close($c);
  return [$code, is_string($antwort) ? $antwort : ''];
}

// Alle Termine im Zeitraum. Der Server soll Wiederholungen selbst aufloesen
// (expand). Tut er es nicht, uebernimmt das vorkommen() weiter unten.
// null heisst: Kalender nicht erreichbar.
function termineHolen(DateTimeImmutable $von, DateTimeImmutable $bis, bool $aufloesen = true): ?array {
  global $diagnose;
  $z = fn(DateTimeImmutable $d) => $d->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z');
  $daten = $aufloesen
    ? '<C:calendar-data><C:expand start="' . $z($von) . '" end="' . $z($bis) . '"/></C:calendar-data>'
    : '<C:calendar-data/>';
  $abfrage = '<?xml version="1.0" encoding="utf-8"?>'
    . '<C:calendar-query xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">'
    . '<D:prop>' . $daten . '</D:prop>'
    . '<C:filter><C:comp-filter name="VCALENDAR"><C:comp-filter name="VEVENT">'
    . '<C:time-range start="' . $z($von) . '" end="' . $z($bis) . '"/>'
    . '</C:comp-filter></C:comp-filter></C:filter></C:calendar-query>';
  [$code, $antwort] = dav('REPORT', KALENDER, $abfrage, ['Depth: 1', 'Content-Type: application/xml; charset=utf-8']);
  $diagnose[] = ['aufloesen' => $aufloesen, 'code' => $code, 'laenge' => strlen($antwort)];
  if ($code !== 207) return null;

  $dom = new DOMDocument();
  if (!@$dom->loadXML($antwort, LIBXML_NONET)) return null;
  $termine = [];
  $gefunden = 0;
  foreach ($dom->getElementsByTagNameNS('urn:ietf:params:xml:ns:caldav', 'calendar-data') as $knoten) {
    if (trim($knoten->textContent) === '') continue;
    $gefunden++;
    foreach (icsLesen($knoten->textContent) as $t) $termine[] = $t;
  }
  $diagnose[count($diagnose) - 1] += [
    'antworten' => $dom->getElementsByTagNameNS('DAV:', 'response')->length,
    'daten' => $gefunden,
    'termine' => array_map(fn($t) => [
      'frei' => preg_match('/^\s*frei\b/iu', $t['titel'] ?? '') === 1,
      'beginn' => $t['beginn']->format('c'), 'ende' => $t['ende']->format('c'),
      'rrule' => !empty($t['rrule']), 'wdh' => !empty($t['wiederholungVon']),
    ], $termine),
  ];
  // Manche Server loesen Wiederholungen nicht auf und lassen die Daten dann
  // ganz weg. Dann ohne expand nochmals, vorkommen() uebernimmt das Aufloesen.
  if ($aufloesen && $gefunden === 0 && $dom->getElementsByTagNameNS('DAV:', 'response')->length > 0) {
    return termineHolen($von, $bis, false);
  }
  return $termine;
}


// ---------- iCalendar lesen ----------

function icsLesen(string $ics): array {
  // Fortsetzungszeilen beginnen mit Leerzeichen oder Tab.
  $ics = preg_replace("/\r?\n[ \t]/", '', $ics) ?? '';
  $termine = [];
  $aktuell = null;
  foreach (preg_split("/\r?\n/", $ics) as $zeile) {
    if ($zeile === 'BEGIN:VEVENT') { $aktuell = ['exdate' => []]; continue; }
    if ($zeile === 'END:VEVENT') { if ($aktuell) $termine[] = $aktuell; $aktuell = null; continue; }
    if ($aktuell === null || !str_contains($zeile, ':')) continue;
    [$kopf, $wert] = explode(':', $zeile, 2);
    $teile = explode(';', $kopf);
    $name = strtoupper(array_shift($teile));
    $param = [];
    foreach ($teile as $p) if (str_contains($p, '=')) { [$k, $v] = explode('=', $p, 2); $param[strtoupper($k)] = trim($v, '"'); }

    switch ($name) {
      case 'SUMMARY':  $aktuell['titel'] = icsText($wert); break;
      case 'UID':      $aktuell['uid'] = $wert; break;
      case 'STATUS':   $aktuell['status'] = strtoupper($wert); break;
      case 'RRULE':    $aktuell['rrule'] = $wert; break;
      case 'DURATION': $aktuell['dauer'] = $wert; break;
      case 'DTSTART':  $aktuell['beginn'] = icsZeit($wert, $param); $aktuell['ganztag'] = !str_contains($wert, 'T'); break;
      case 'DTEND':    $aktuell['ende'] = icsZeit($wert, $param); break;
      case 'RECURRENCE-ID': $aktuell['wiederholungVon'] = icsZeit($wert, $param); break;
      case 'EXDATE':
        foreach (explode(',', $wert) as $w) if ($d = icsZeit($w, $param)) $aktuell['exdate'][] = $d->getTimestamp();
        break;
    }
  }
  $termine = array_values(array_filter($termine, fn($t) => !empty($t['beginn'])));
  foreach ($termine as &$t) {
    if (empty($t['ende'])) {
      try {
        $t['ende'] = !empty($t['dauer']) ? $t['beginn']->add(new DateInterval(ltrim($t['dauer'], '+')))
          : $t['beginn']->modify($t['ganztag'] ? '+1 day' : '+0 minutes');
      } catch (Exception $e) { $t['ende'] = $t['beginn']; }
    }
  }
  unset($t);
  return $termine;
}

function icsText(string $wert): string {
  return strtr($wert, ['\\n' => "\n", '\\N' => "\n", '\\,' => ',', '\\;' => ';', '\\\\' => '\\']);
}

function icsZeit(string $wert, array $param): ?DateTimeImmutable {
  $wert = trim($wert);
  try {
    if (preg_match('/^\d{8}$/', $wert)) {
      return DateTimeImmutable::createFromFormat('!Ymd', $wert, new DateTimeZone(ZEITZONE)) ?: null;
    }
    if (!preg_match('/^(\d{8}T\d{6})(Z?)$/', $wert, $m)) return null;
    $zone = $m[2] === 'Z' ? 'UTC' : ($param['TZID'] ?? ZEITZONE);
    try { $tz = new DateTimeZone($zone); } catch (Exception $e) { $tz = new DateTimeZone(ZEITZONE); }
    $d = DateTimeImmutable::createFromFormat('Ymd\THis', $m[1], $tz);
    return $d ? $d->setTimezone(new DateTimeZone(ZEITZONE)) : null;
  } catch (Exception $e) {
    return null;
  }
}

// Falls der Server Wiederholungen nicht aufloest: taeglich und woechentlich
// (mit BYDAY, INTERVAL, UNTIL, COUNT, EXDATE). Mehr braucht ein Freigabe-
// kalender nicht. Monatliche und jaehrliche Regeln zaehlen nur einmal.
function vorkommen(array $t, DateTimeImmutable $bis): array {
  $laenge = $t['ende']->getTimestamp() - $t['beginn']->getTimestamp();
  if (empty($t['rrule'])) return [[$t['beginn'], $t['ende']]];

  $regel = [];
  foreach (explode(';', $t['rrule']) as $teil) if (str_contains($teil, '=')) { [$k, $v] = explode('=', $teil, 2); $regel[strtoupper($k)] = $v; }
  $art = $regel['FREQ'] ?? '';
  if (!in_array($art, ['DAILY', 'WEEKLY'], true)) return [[$t['beginn'], $t['ende']]];

  $schritt = max(1, (int) ($regel['INTERVAL'] ?? 1));
  $anzahl  = isset($regel['COUNT']) ? (int) $regel['COUNT'] : PHP_INT_MAX;
  $bisRegel = isset($regel['UNTIL']) ? icsZeit($regel['UNTIL'], []) : null;
  $grenze = $bisRegel && $bisRegel < $bis ? $bisRegel : $bis;
  $tage = ['MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6, 'SU' => 7];
  $wochentage = [];
  if ($art === 'WEEKLY') {
    foreach (explode(',', $regel['BYDAY'] ?? '') as $b) if (isset($tage[substr($b, -2)])) $wochentage[] = $tage[substr($b, -2)];
    if (!$wochentage) $wochentage = [(int) $t['beginn']->format('N')];
    sort($wochentage);
  }

  $liste = [];
  $gezaehlt = 0;
  $start = $t['beginn'];
  for ($runde = 0; $runde < 800 && $gezaehlt < $anzahl; $runde++) {
    if ($art === 'DAILY') {
      $kandidaten = [$start->modify('+' . ($runde * $schritt) . ' days')];
    } else {
      // Montag der jeweiligen Woche, dann die gewuenschten Tage darin.
      $montag = $start->modify('-' . ((int) $start->format('N') - 1) . ' days')->modify('+' . ($runde * $schritt) . ' weeks');
      $kandidaten = array_map(fn($n) => $montag->modify('+' . ($n - 1) . ' days'), $wochentage);
    }
    foreach ($kandidaten as $k) {
      if ($k < $t['beginn']) continue;
      if ($k > $grenze || $gezaehlt >= $anzahl) break 2;
      $gezaehlt++;
      if (in_array($k->getTimestamp(), $t['exdate'], true)) continue;
      $liste[] = [$k, $k->modify('+' . $laenge . ' seconds')];
    }
  }
  return $liste;
}


// ---------- Freie Zeiten ----------

function fruehestens(): DateTimeImmutable {
  return (new DateTimeImmutable('today'))->modify('+' . VORLAUF_TAGE . ' days');
}
function spaetestens(): DateTimeImmutable {
  return (new DateTimeImmutable('today'))->modify('+' . (REICHWEITE_TAGE + 1) . ' days');
}

// ['2026-10-27' => ['14:00', '14:45'], ...] oder null, wenn der Kalender schweigt.
function freieZeiten(): ?array {
  $von = fruehestens();
  $bis = spaetestens();
  $termine = termineHolen($von->modify('-1 day'), $bis);
  if ($termine === null) return null;

  // Ausnahmen einzelner Wiederholungen ersetzen das urspruengliche Vorkommen.
  $ersetzt = [];
  foreach ($termine as $t) if (!empty($t['wiederholungVon'])) $ersetzt[($t['uid'] ?? '') . '@' . $t['wiederholungVon']->getTimestamp()] = true;

  $fenster = [];
  $belegt = [];
  foreach ($termine as $t) {
    if (($t['status'] ?? '') === 'CANCELLED') continue;
    $frei = preg_match('/^\s*frei\b/iu', $t['titel'] ?? '') === 1;
    foreach (vorkommen($t, $bis) as [$b, $e]) {
      if (empty($t['wiederholungVon']) && isset($ersetzt[($t['uid'] ?? '') . '@' . $b->getTimestamp()])) continue;
      if ($frei) $fenster[] = [$b, $e]; else $belegt[] = [$b->getTimestamp(), $e->getTimestamp()];
    }
  }

  $dauer = DAUER_MIN * 60;
  $puffer = PUFFER_MIN * 60;
  $zeiten = [];
  foreach ($fenster as [$b, $e]) {
    for ($s = $b->getTimestamp(); $s + $dauer <= $e->getTimestamp(); $s += $dauer + $puffer) {
      if ($s < $von->getTimestamp() || $s >= $bis->getTimestamp()) continue;
      foreach ($belegt as [$bb, $be]) {
        if ($bb < $s + $dauer + $puffer && $be > $s - $puffer) continue 2;
      }
      $zeiten[$s] = true;
    }
  }
  ksort($zeiten);
  $tage = [];
  foreach (array_keys($zeiten) as $s) {
    $d = (new DateTimeImmutable('@' . $s))->setTimezone(new DateTimeZone(ZEITZONE));
    $tage[$d->format('Y-m-d')][] = $d->format('H:i');
  }
  return $tage;
}


// ---------- Ausgabe in Worten ----------

const WOCHENTAGE = [1 => 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];
const MONATE = [1 => 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];

function inWorten(DateTimeImmutable $d): string {
  return WOCHENTAGE[(int) $d->format('N')] . ', ' . $d->format('j') . '. ' . MONATE[(int) $d->format('n')] . ', ' . $d->format('H:i') . ' Uhr';
}

function icsEscape(string $s): string {
  return strtr($s, ['\\' => '\\\\', ';' => '\;', ',' => '\,', "\r\n" => '\n', "\n" => '\n']);
}

// Lange Zeilen falten, wie RFC 5545 es verlangt (75 Bytes).
function icsZeilen(array $zeilen): string {
  $aus = '';
  foreach ($zeilen as $z) {
    $teil = '';
    foreach (mb_str_split($z) as $zeichen) {
      if (strlen($teil . $zeichen) > 74) { $aus .= $teil . "\r\n"; $teil = ' '; }
      $teil .= $zeichen;
    }
    $aus .= $teil . "\r\n";
  }
  return $aus;
}

function icsTermin(string $uid, DateTimeImmutable $beginn, string $titel, string $beschreibung, string $methode = ''): string {
  $utc = fn(DateTimeImmutable $d) => $d->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z');
  $zeilen = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//formateins//Termine//DE'];
  if ($methode !== '') $zeilen[] = 'METHOD:' . $methode;
  array_push($zeilen,
    'BEGIN:VEVENT',
    'UID:' . $uid,
    'DTSTAMP:' . $utc(new DateTimeImmutable()),
    'DTSTART:' . $utc($beginn),
    'DTEND:' . $utc($beginn->modify('+' . DAUER_MIN . ' minutes')),
    'SUMMARY:' . icsEscape($titel),
    'DESCRIPTION:' . icsEscape($beschreibung),
    'END:VEVENT',
    'END:VCALENDAR'
  );
  return icsZeilen($zeilen);
}

function schluessel(string $uid): string {
  global $zugang;
  return hash_hmac('sha256', $uid, $zugang['passwort'] . __FILE__);
}

function zwischenspeicher(): string {
  return sys_get_temp_dir() . '/termine_' . hash('sha256', __FILE__);
}


// ---------- Mails ----------

function mailSenden(string $an, string $betreff, string $text, string $html = '', string $ics = '', string $antwortAn = ''): bool {
  $koepfe = [
    'From'         => mb_encode_mimeheader('Adrian von Arb · formateins', 'UTF-8', 'Q') . ' <' . ABSENDER . '>',
    'Reply-To'     => $antwortAn !== '' ? $antwortAn : EMPFAENGER,
    'MIME-Version' => '1.0',
  ];
  if ($html === '' && $ics === '') {
    $koepfe['Content-Type'] = 'text/plain; charset=UTF-8';
    $koepfe['Content-Transfer-Encoding'] = '8bit';
    $koerper = $text;
  } else {
    $aussen = 'aussen_' . bin2hex(random_bytes(10));
    $innen  = 'innen_' . bin2hex(random_bytes(10));
    $koepfe['Content-Type'] = 'multipart/mixed; boundary="' . $aussen . '"';
    $teil = fn(string $typ, string $inhalt, string $extra = '') => "Content-Type: $typ\r\nContent-Transfer-Encoding: base64\r\n$extra\r\n" . chunk_split(base64_encode($inhalt));
    $koerper = "--$aussen\r\nContent-Type: multipart/alternative; boundary=\"$innen\"\r\n\r\n"
      . "--$innen\r\n" . $teil('text/plain; charset=UTF-8', $text)
      . ($html !== '' ? "--$innen\r\n" . $teil('text/html; charset=UTF-8', $html) : '')
      . "--$innen--\r\n"
      . ($ics !== '' ? "--$aussen\r\n" . $teil('text/calendar; charset=UTF-8; method=PUBLISH; name="termin.ics"', $ics, "Content-Disposition: attachment; filename=\"termin.ics\"\r\n") : '')
      . "--$aussen--\r\n";
  }
  return mail($an, mb_encode_mimeheader($betreff, 'UTF-8', 'Q'), $koerper, $koepfe, '-f' . ABSENDER);
}

// Bestaetigung an den Kunden, gleiche Gestalt wie die aus anfrage.php.
function bestaetigungHtml(string $anrede, string $wann, string $wie, string $absagen): string {
  $h = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
  $serif = "Georgia,'Times New Roman',serif";
  $sans  = 'Helvetica,Arial,sans-serif';
  $etikett = "margin:0;font-family:$sans;font-size:12px;letter-spacing:2px;text-transform:uppercase;font-weight:700";
  return <<<HTML
<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="color-scheme" content="light"><title>Dein Gespräch ist gebucht</title></head>
<body style="margin:0;padding:0;background:#ecebe7">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#ecebe7"><tr><td align="center" style="padding:32px 12px">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:6px;overflow:hidden">
  <tr><td style="background:#0f1a21;padding:26px 36px"><span style="font-family:$serif;font-size:24px;font-weight:700;color:#f3efe7">format</span><span style="font-family:$serif;font-size:24px;font-weight:700;color:#ff7a3d">eins</span></td></tr>
  <tr><td style="padding:40px 36px 8px">
    <p style="$etikett;color:#ff7a3d;margin-bottom:6px">Gespräch gebucht</p>
    <h1 style="margin:0;font-family:$serif;font-size:32px;line-height:1.15;color:#0f1a21;font-weight:700">{$h($anrede)}<br>Wir sprechen uns.</h1>
  </td></tr>
  <tr><td style="padding:28px 36px 0">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f4ef;border-left:3px solid #ff7a3d;border-radius:4px">
      <tr><td style="padding:20px 22px">
        <p style="$etikett;color:#6b7a83">Dein Termin</p>
        <p style="margin:6px 0 0;font-family:$serif;font-size:24px;line-height:1.25;color:#0f1a21;font-weight:700">{$h($wann)}</p>
        <p style="margin:8px 0 0;font-family:$sans;font-size:15px;line-height:1.5;color:#3b4a53">{$h($wie)}</p>
      </td></tr>
    </table>
    <p style="margin:14px 0 0;font-family:$sans;font-size:13px;line-height:1.5;color:#6b7a83">Der Termin hängt als Kalendereintrag an dieser Mail. Passt es doch nicht? <a href="{$h($absagen)}" style="color:#0f1a21">Hier absagen</a> und einfach neu buchen.</p>
  </td></tr>
  <tr><td style="padding:34px 36px 36px">
    <p style="margin:0;font-family:$sans;font-size:16px;line-height:1.6;color:#3b4a53">Bis bald<br><span style="font-family:$serif;font-size:20px;color:#0f1a21;font-weight:700">Adrian von Arb</span></p>
    <p style="margin:10px 0 0;font-family:$sans;font-size:14px;line-height:1.6;color:#6b7a83"><a href="tel:+41796868486" style="color:#0f1a21">079 686 84 86</a> · <a href="https://www.formateins.ch" style="color:#0f1a21">formateins.ch</a></p>
  </td></tr>
  <tr><td style="background:#f6f4ef;padding:18px 36px;font-family:$sans;font-size:12px;line-height:1.5;color:#8a969c">formateins, eine Marke der pzu consulting gmbh · Hochgasse 7, 4632 Trimbach<br>Du bekommst diese Mail, weil du auf formateins.ch ein Gespräch gebucht hast.</td></tr>
</table>
</td></tr></table>
</body></html>
HTML;
}


// ---------- Anfragen ----------

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $ablage = zwischenspeicher();
  $alt = @json_decode((string) @file_get_contents($ablage), true);
  if (is_array($alt) && ($alt['zeit'] ?? 0) > time() - ZWISCHENSPEICHER_S) ende(200, ['dauer' => DAUER_MIN, 'tage' => $alt['tage']]);
  $diagnose = [];
  $tage = freieZeiten();
  if (isset($_GET['diagnose'])) ende(200, ['diagnose' => $diagnose, 'tage' => $tage]);  // VORUEBERGEHEND
  if ($tage === null) ende(503, ['grund' => 'Kalender nicht erreichbar']);
  @file_put_contents($ablage, json_encode(['zeit' => time(), 'tage' => $tage]), LOCK_EX);
  ende(200, ['dauer' => DAUER_MIN, 'tage' => $tage]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') ende(405, ['grund' => 'nur GET und POST']);

// Nur Anfragen von der eigenen Seite, wie in anfrage.php.
$herkunft = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($herkunft !== '' && parse_url($herkunft, PHP_URL_HOST) !== parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)) {
  ende(403, ['grund' => 'fremde Herkunft']);
}

$aktion = zeile('aktion', 20);

if ($aktion === 'absagen') {
  $uid = zeile('uid', 60);
  if (!preg_match('/^formateins-[0-9a-f]{20}$/', $uid) || !hash_equals(schluessel($uid), zeile('t', 80))) ende(403, ['grund' => 'Link ungueltig']);
  [$code, $ics] = dav('GET', KALENDER . $uid . '.ics');
  if ($code === 404) ende(200, ['schon' => true]);
  if ($code !== 200) ende(503, ['grund' => 'Kalender nicht erreichbar']);
  $termin = icsLesen($ics)[0] ?? null;
  [$code] = dav('DELETE', KALENDER . $uid . '.ics');
  if (!in_array($code, [200, 204, 404], true)) ende(503, ['grund' => 'Kalender nicht erreichbar']);
  @unlink(zwischenspeicher());
  $wann = $termin ? inWorten($termin['beginn']) : '';
  mailSenden(EMPFAENGER, 'Abgesagt: ' . ($termin['titel'] ?? 'Gespräch'),
    "Dieses Gespräch wurde über den Link in der Bestätigung abgesagt:\n\n" . ($termin['titel'] ?? '') . "\n" . $wann . "\n\nDer Termin ist aus dem Kalender «Gespräche» entfernt, die Zeit ist wieder frei.");
  ende(200, ['wann' => $wann]);
}

if ($aktion !== 'buchen') ende(400, ['grund' => 'unbekannte Aktion']);

// Spamfallen wie in anfrage.php: Bots bekommen ein falsches "ok".
if (zeile('_gotcha') !== '' || (int) zeile('dauer', 12) < MINDESTDAUER_MS) ende(200, ['wann' => '']);

$ablage = sys_get_temp_dir() . '/termine_ip_' . hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . __FILE__);
$jetzt = time();
$zeiten = array_filter(array_map('intval', @file($ablage, FILE_IGNORE_NEW_LINES) ?: []), fn($t) => $t > $jetzt - 3600);
if (count($zeiten) >= GRENZE_PRO_STUNDE) ende(429, ['grund' => 'zu viele Buchungen']);

$name    = zeile('name', 120);
$mail    = zeile('mail', 200);
$telefon = zeile('tel', 40);
$art     = zeile('art', 20);
if ($name === '') ende(400, ['grund' => 'Name fehlt']);
if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) ende(400, ['grund' => 'E-Mail ungueltig']);
if (!preg_match('~^[+0-9 ()/.\-]+$~', $telefon) || strlen(preg_replace('/\D/', '', $telefon)) < 8) ende(400, ['grund' => 'Telefon fehlt']);
if (!isset(ARTEN[$art])) ende(400, ['grund' => 'Art fehlt']);
$beginn = DateTimeImmutable::createFromFormat('!Y-m-d H:i', zeile('datum', 10) . ' ' . zeile('zeit', 5), new DateTimeZone(ZEITZONE));
if (!$beginn) ende(400, ['grund' => 'Zeit fehlt']);

// Pruefen und Eintragen unter Sperre, damit zwei gleichzeitige Buchungen
// nicht dieselbe Zeit bekommen.
$sperre = fopen(sys_get_temp_dir() . '/termine_sperre_' . hash('sha256', __FILE__), 'c');
flock($sperre, LOCK_EX);

$frei = freieZeiten();
$uid = 'formateins-' . bin2hex(random_bytes(10));
$titel = 'Gespräch: ' . $name . ' (' . ARTEN[$art] . ')';
$beschreibung = "Telefon: $telefon\nE-Mail: $mail\nArt: " . ARTEN[$art] . "\n\nGebucht über formateins.ch/gespraech.html";
$eingetragen = false;

if ($frei !== null) {
  if (!in_array($beginn->format('H:i'), $frei[$beginn->format('Y-m-d')] ?? [], true)) {
    flock($sperre, LOCK_UN);
    ende(409, ['grund' => 'Zeit nicht mehr frei']);
  }
  [$code] = dav('PUT', KALENDER . $uid . '.ics', icsTermin($uid, $beginn, $titel, $beschreibung),
    ['Content-Type: text/calendar; charset=utf-8', 'If-None-Match: *']);
  $eingetragen = in_array($code, [200, 201, 204], true);
}
@unlink(zwischenspeicher());
flock($sperre, LOCK_UN);

$zeiten[] = $jetzt;
@file_put_contents($ablage, implode("\n", $zeiten), LOCK_EX);

$wann = inWorten($beginn);

// Kalender nicht erreichbar: nichts geht verloren, Adrian bestaetigt von Hand.
if (!$eingetragen) {
  mailSenden(EMPFAENGER, 'Terminwunsch, NICHT eingetragen: ' . $name,
    "Der Kalender war nicht erreichbar, der Termin steht noch nicht drin.\nBitte eintragen und von Hand bestaetigen.\n\n"
    . "Wunsch: $wann\nArt: " . ARTEN[$art] . "\nName: $name\nTelefon: $telefon\nE-Mail: $mail", '', '', $mail);
  ende(200, ['wann' => $wann, 'vorbehalt' => true]);
}

mailSenden(EMPFAENGER, 'Neues Gespräch: ' . $name . ', ' . $beginn->format('d.m. H:i'),
  "$wann\nArt: " . ARTEN[$art] . "\n\nName: $name\nTelefon: $telefon\nE-Mail: $mail\n\nSteht im Kalender «Gespräche».", '', '', $mail);

// Bestaetigung an den Kunden. Vorname nur, wenn er wie einer aussieht (siehe anfrage.php).
$vorname = mb_substr(preg_split('/\s+/u', $name)[0] ?? '', 0, 30);
if (!preg_match('/^[\p{L}][\p{L}\'’\-\.]*$/u', $vorname)) $vorname = '';
$anrede = $vorname !== '' ? 'Danke, ' . $vorname . '.' : 'Danke.';
$wie = $art === 'telefon'
  ? 'Ich rufe dich unter ' . $telefon . ' an.'
  : (VIDEO_LINK !== '' ? 'Per Zoom: ' . VIDEO_LINK : 'Per Video. Den Link schicke ich dir vorher per Mail.');
$absagen = SEITE . '/gespraech.html?absagen=' . $uid . '&t=' . schluessel($uid);
$text = "$anrede Wir sprechen uns.\n\nDein Termin: $wann\n$wie\n\nDer Termin hängt als Kalendereintrag an dieser Mail.\nPasst es doch nicht? Hier absagen und einfach neu buchen:\n$absagen\n\nBis bald\nAdrian von Arb\n" . TELEFON . " · formateins.ch";
$icsKunde = icsTermin($uid, $beginn, 'Gespräch mit Adrian von Arb, formateins', $wie . "\n\nAbsagen: " . $absagen, 'PUBLISH');
try {
  mailSenden($mail, 'Dein Gespräch am ' . $beginn->format('j.n.') . ' um ' . $beginn->format('H:i') . ' · formateins',
    $text, bestaetigungHtml($anrede, $wann, $wie, $absagen), $icsKunde);
} catch (Throwable $e) {
  error_log('Terminbestaetigung: ' . $e->getMessage());
}

ende(200, ['wann' => $wann]);
