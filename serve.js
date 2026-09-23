#!/usr/bin/env node
/* Serves this directory and collects the solvers' event log.
 *
 *     node serve.js [port]        default 8080, or $PORT
 *
 * Pages post a JSON array of events to /log; each event is appended as one
 * line to logs/events.jsonl. Same origin as the pages, so there is no CORS
 * and nothing third-party involved.
 *
 * The receiver deliberately does not record IP addresses. Note that a
 * reverse proxy in front of this (nginx, Apache) will record them in its own
 * access log unless you turn that off.
 */
"use strict";

var http = require("http");
var fs = require("fs");
var path = require("path");

var ROOT = __dirname;
var LOGDIR = path.join(ROOT, "logs");
var LOGFILE = path.join(LOGDIR, "events.jsonl");
var PORT = Number(process.argv[2] || process.env.PORT || 8080);

var MAX_BODY = 64 * 1024;   /* bytes per post */
var MAX_EVENTS = 40;        /* events per post */
var MAX_STR = 400;          /* characters per string field */
var MAX_KEYS = 24;          /* fields per event */

var TYPES = {
  ".html": "text/html; charset=utf-8",
  ".js":   "text/javascript; charset=utf-8",
  ".css":  "text/css; charset=utf-8",
  ".json": "application/json; charset=utf-8",
  ".jsonl":"application/x-ndjson; charset=utf-8",
  ".md":   "text/plain; charset=utf-8",
  ".svg":  "image/svg+xml",
  ".png":  "image/png",
  ".ico":  "image/x-icon",
  ".txt":  "text/plain; charset=utf-8"
};

/* Keep only what a page is supposed to send, and keep it small. A log that
   anyone can post to should not be able to grow unbounded fields. */
function clean(ev){
  if (!ev || typeof ev !== "object" || Array.isArray(ev)) return null;
  var out = {}, keys = Object.keys(ev).slice(0, MAX_KEYS);
  keys.forEach(function(k){
    var v = ev[k];
    if (typeof v === "string") out[k] = v.length > MAX_STR ? v.slice(0, MAX_STR) : v;
    else if (typeof v === "number" && isFinite(v)) out[k] = v;
    else if (typeof v === "boolean" || v === null) out[k] = v;
  });
  return Object.keys(out).length ? out : null;
}

function append(events, done){
  var now = new Date().toISOString();
  var lines = "";
  events.slice(0, MAX_EVENTS).forEach(function(raw){
    var ev = clean(raw);
    if (!ev) return;
    ev.r = now;                       /* when the host received it */
    lines += JSON.stringify(ev) + "\n";
  });
  if (!lines) return done();
  fs.mkdir(LOGDIR, {recursive: true}, function(err){
    if (err) return done(err);
    fs.appendFile(LOGFILE, lines, done);
  });
}

function collect(req, cb){
  var body = "", over = false;
  req.on("data", function(c){
    if (over) return;
    body += c;
    if (body.length > MAX_BODY){ over = true; cb(new Error("too large")); req.destroy(); }
  });
  req.on("end", function(){ if (!over) cb(null, body); });
  req.on("error", function(e){ if (!over){ over = true; cb(e); } });
}

function serveFile(res, file){
  fs.readFile(file, function(err, data){
    if (err){
      res.writeHead(404, {"Content-Type": "text/plain; charset=utf-8"});
      res.end("Not found\n");
      return;
    }
    res.writeHead(200, {
      "Content-Type": TYPES[path.extname(file).toLowerCase()] || "application/octet-stream",
      "Cache-Control": "no-cache"
    });
    res.end(data);
  });
}

http.createServer(function(req, res){
  var url = req.url.split("?")[0];

  if (req.method === "POST" && (url === "/log" || url === "/log/")){
    collect(req, function(err, body){
      if (err){ res.writeHead(413).end(); return; }
      var events;
      try { events = JSON.parse(body); }
      catch (e){ res.writeHead(400).end(); return; }
      if (!Array.isArray(events)){ res.writeHead(400).end(); return; }
      append(events, function(e){
        res.writeHead(e ? 500 : 204).end();
        if (e) console.error("log write failed:", e.message);
      });
    });
    return;
  }

  if (req.method !== "GET" && req.method !== "HEAD"){
    res.writeHead(405).end();
    return;
  }

  /* static files, confined to this directory */
  var rel = decodeURIComponent(url);
  if (rel === "/" ) rel = "/index.html";
  var file = path.join(ROOT, rel);
  if (path.relative(ROOT, file).split(path.sep)[0] === ".."){
    res.writeHead(403).end();
    return;
  }
  serveFile(res, file);
}).listen(PORT, function(){
  console.log("solvers on http://localhost:" + PORT);
  console.log("log         " + LOGFILE);
});
