<?php
/* Drop-in receiver for a host that runs PHP, as an alternative to serve.js.
 * Upload it next to the pages; they post here with no configuration needed.
 * Appends one JSON object per line to logs/.htevents.jsonl, alongside this file.
 * The .ht prefix is deliberate: Apache denies that name pattern from its main
 * config, so the log stays unreadable over the web even where .htaccess is
 * ignored because AllowOverride is off.
 *
 * Does not record IP addresses. Your web server's access log will, unless
 * you configure it otherwise.
 */
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$raw = file_get_contents('php://input', false, null, 0, 65536);
$events = json_decode($raw, true);
if (!is_array($events)) { http_response_code(400); exit; }

$dir = __DIR__ . '/logs';
if (!is_dir($dir) && !mkdir($dir, 0750, true)) { http_response_code(500); exit; }

/* The log sits under the document root, so keep the web server from handing it
   back: it holds what other people typed. This covers Apache; on nginx add
   `location ^~ /logs/ { deny all; }` yourself. */
$guard = $dir . '/.htaccess';
if (!file_exists($guard)) {
    @file_put_contents($guard,
        "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
}

$now = gmdate('c');
$out = '';
foreach (array_slice($events, 0, 40) as $ev) {
    if (!is_array($ev)) continue;
    $clean = [];
    foreach (array_slice($ev, 0, 24, true) as $k => $v) {
        if (is_string($v))      $clean[$k] = mb_substr($v, 0, 400);
        elseif (is_int($v) || is_float($v) || is_bool($v) || $v === null) $clean[$k] = $v;
    }
    if (!$clean) continue;
    $clean['r'] = $now;
    $out .= json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
}

if ($out !== '') { file_put_contents($dir . '/.htevents.jsonl', $out, FILE_APPEND | LOCK_EX); }
http_response_code(204);
