<?php
// Router fuer den lokalen PHP-Server, verhaelt sich wie die Regeln in
// htaccess-hostpoint: /gespraech liefert gespraech.html, die alte Form mit
// .html leitet um. Aufruf im README unter "Lokal ansehen".
$pfad = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$wurzel = dirname(__DIR__);
$suche = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '';

if (preg_match('~^(.*/)index\.html$~', $pfad, $m) || preg_match('~^(.+)\.html$~', $pfad, $m)) {
  header('Location: ' . $m[1] . $suche, true, 301);
  return true;
}
if ($pfad !== '/' && !str_contains($pfad, '..') && is_file($wurzel . $pfad . '.html')) {
  header('Content-Type: text/html; charset=utf-8');
  readfile($wurzel . $pfad . '.html');
  return true;
}
return false;
