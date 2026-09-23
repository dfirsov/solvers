<?php
/* Drop-in receiver for a host that runs PHP, as an alternative to serve.js.
 * Appends one JSON object per line to logs/events.jsonl, alongside this file.
 *
 * If you use this, point the pages at it:
 *     sed -i 's|var LOG_URL = "log";|var LOG_URL = "log.php";|' *.html
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

if ($out !== '') { file_put_contents($dir . '/events.jsonl', $out, FILE_APPEND | LOCK_EX); }
http_response_code(204);
