# Propositional Logic Solvers

Five interactive tools for a propositional logic course. Each takes a formula and
works through a method step by step, checking every move rather than handing over
an answer.

| file            | tool                 | method                                   |
|-----------------|----------------------|------------------------------------------|
| `tableaux.html` | Semantic Tableaux    | signed T/F tableaux, α and β rules        |
| `bdd.html`      | BDD Builder          | Shannon expansion, unordered diagrams     |
| `robdd.html`    | ROBDD Constructor    | C1/C2/C3 reduction under a fixed order    |
| `arith.html`    | Arithmetic Encoding  | Boolean polynomials, ring laws + `x·x = x`|
| `equiv.html`    | Equivalence Checker  | truth tables, counterexamples             |

Every page is a complete HTML document — no build step and no dependencies beyond
Google Fonts — so the directory can be uploaded as-is, or opened straight from
disk.

## Deploying

The five tools and the index are plain files: upload them anywhere and they work,
including opened straight from disk over `file://`. **The logging is the part that
needs something from the host**, because a static file server has nothing to
receive a POST.

| host                                       | tools | logging                          |
|--------------------------------------------|-------|----------------------------------|
| static — GitHub Pages, S3, plain web space  | works | silently does nothing             |
| Apache or nginx **with PHP**                | works | works, nothing to configure       |
| anywhere you can run Node                   | works | `node serve.js`                   |

A failed post is swallowed, so on a static host the tools behave exactly as they
do otherwise — you simply get no log.

### With Node

```sh
node serve.js            # http://localhost:8080
node serve.js 9000       # another port, or set $PORT
```

No packages needed. It serves this directory, accepts the log at `/log.php` (and
at `/log`), and writes `logs/htevents.jsonl`, which it also serves back. It
listens on all interfaces, so
put it behind nginx if the machine is public.

### With PHP

Upload `log.php` along with the pages. That is the whole procedure — the pages
already post to `log.php`, relative to wherever they sit, so a subdirectory works
without adjustment.

`logs/` must be writable **by the web server user**, which is often not the user
that uploaded the files. If nothing appears, that is almost always why.

If the log URL returns 403, read the response body — Apache names the rule that
fired. The one to expect here is:

```
Server unable to read htaccess file, denying access to be safe
```

That does **not** mean an `.htaccess` exists. It means Apache could not look for
one, because it cannot read the directory — so it refuses rather than risk
ignoring a rule. The cause is the directory's mode: PHP creates `logs/` as the
account user, and on a host where Apache serves static files as a different user,
anything tighter than `0755` shuts it out. One command fixes it:

```sh
chmod 755 solvers/logs
```

`log.php` now creates the directory `0755`, but an existing one keeps whatever
mode it was made with.

### The log is readable over the web, on purpose — for now

`logs/htevents.jsonl` is left fetchable so bring-up can be checked from a browser:

```
https://your.host/path/logs/htevents.jsonl
```

That means **anyone who can reach the site can read what everyone typed**, so it
is a setting for getting things working, not for running a cohort. Either of these
closes it again, and either alone is enough:

- Rename the file to `.htevents.jsonl` in `log.php` and `serve.js`. Apache denies
  anything starting with `.ht` from its *main* config, by name, before it looks
  for the file, so this holds whether or not `AllowOverride` is on:

  ```
  GET logs/.htnonexistent    403     denied by name, though nothing is there
  GET logs/nonexistent.jsonl 404     ordinary file handling
  ```

- Restore the `.htaccess` that `log.php` used to write into `logs/`. The block is
  still in the file, commented, with the lines to put back. This one needs
  `AllowOverride` to be on, which you can check: a `403` on a file that exists
  nowhere under `logs/` means it is being honoured.

An `.htaccess` already sitting in `logs/` on a server keeps denying the directory
until it is deleted by hand — a newer `log.php` will not remove it.

On nginx neither applies; use `location ^~ /logs/ { deny all; }`.

### Do not upload a local `logs/`

It is gitignored, so a git deploy will not carry it, but an `rsync` or a drag of
the whole folder will — and then someone else's events are sitting on your server
under a name you did not choose.

If you left an old `events.jsonl` or `.htevents.jsonl` up there from an earlier
round, delete it — nothing writes to those names any more.

## Logging

Pages post events to `/log` on the host that served them — same origin, so no CORS
and nothing third-party. Opened from a `file://` URL, or with the receiver down,
the post fails quietly and the tools carry on: **logging is never allowed to break
a tool.**

### Turning it off

```sh
sed -i 's|var LOG_URL = "log.php";|var LOG_URL = "";|' *.html
```

An empty `LOG_URL` disables logging entirely — no requests, no queue.

### What a line looks like

One JSON object per line, one line per formula submitted. `t` is when the browser
sent it, `r` when the host received it, `p` the page.

```json
{"t":"…","p":"arith","f":"p -> (q -> p)","r":"…"}
{"t":"…","p":"tableaux","f":"q -> (p -> q)","sign":"F","r":"…"}
{"t":"…","p":"bdd","f":"p &","err":"parse","r":"…"}
{"t":"…","p":"equiv","fa":"~(p | q)","fb":"~p & ~q","r":"…"}
```

That is the whole schema. Rule steps, mistakes, verdicts and page views are **not**
recorded — only what someone typed and submitted.

Three things deliberately do not produce a line: the formula a page loads with,
because nobody submitted it; **Restart**, because it re-runs whatever is already in
the box; and reordering variables in the ROBDD tool, for the same reason. A formula
that fails to parse *is* logged, with `err`, since it is still something someone
tried. Switching T/F in the tableaux tool logs again, because the sign is part of
what is being analysed and it travels in the line.

### Reading it

```sh
# which formulas get tried, commonest first
jq -r 'select(.f) | .f' logs/htevents.jsonl | sort | uniq -c | sort -rn | head -20

# per tool
jq -r '"\(.p)\t\(.f // (.fa + "  vs  " + .fb))"' logs/htevents.jsonl | sort | uniq -c | sort -rn

# what failed to parse
jq -r 'select(.err == "parse") | .f' logs/htevents.jsonl | sort | uniq -c | sort -rn

# submissions per day
jq -r '.t[0:10]' logs/htevents.jsonl | sort | uniq -c
```

### Privacy

The log records the formulas people submit, and nothing else — no buttons, no
progress, no page views. There is no name, no account, no cookie and no session
id, so two lines from the same person are not linkable. Neither `serve.js` nor
`log.php` records IP addresses.

Two things to be aware of before running this for a real cohort:

- A reverse proxy in front of the receiver (nginx, Apache) writes IPs to its own
  access log unless you turn that off. Timestamp plus IP is enough to make the
  whole record personal data.
- A formula box is a free-text field, and free-text fields collect whatever people
  type into them.

So: say on the page or in the course notes that the tools log what is entered, and
decide how long the log is kept. If none of that is wanted, the `sed` above
switches logging off and the pages go back to making no network calls at all.
