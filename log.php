<?php
/* Drop-in receiver for a host that runs PHP, as an alternative to serve.js.
 * Upload it next to the pages; they post here with no configuration needed.
 * Appends one JSON object per line to logs/htevents.jsonl, alongside this file.
 *
 * NOTE: the log is deliberately left readable over the web for now, so it can
 * be fetched from a browser during bring-up. To close that later, rename the
 * file back to .htevents.jsonl (Apache denies the .ht prefix by name, from its
 * main config) and/or restore the .htaccess written below.
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
/* 0755, not 0750: on a host where PHP and Apache's static file serving run as
   different users, 0750 locks the web server out of the directory entirely —
   it then cannot even read an .htaccess in there and returns 403 for
   everything, which is exactly what happened on the first deployment. */
if (!is_dir($dir) && !mkdir($dir, 0755, true)) { http_response_code(500); exit; }

/* Deliberately not writing an .htaccess guard here yet — see the note above.
   To close the log off again, restore:
       $guard = $dir . '/.htaccess';
       if (!file_exists($guard)) { @file_put_contents($guard, "Require all denied\n"); }
*/

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

if ($out !== '') { file_put_contents($dir . '/htevents.jsonl', $out, FILE_APPEND | LOCK_EX); }
http_response_code(204);
