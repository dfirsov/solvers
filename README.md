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

## Running

Open `index.html`, or serve the directory any way you like.

To collect the event log as well:

```sh
node serve.js            # http://localhost:8080
node serve.js 9000       # another port, or set $PORT
```

`serve.js` needs no packages. It serves this directory and accepts the log; events
land in `logs/events.jsonl`, which is gitignored.

## Logging

Pages post events to `/log` on the host that served them — same origin, so no CORS
and nothing third-party. Opened from a `file://` URL, or with the receiver down,
the post fails quietly and the tools carry on: **logging is never allowed to break
a tool.**

### Turning it off

```sh
sed -i 's|var LOG_URL = "log";|var LOG_URL = "";|' *.html
```

An empty `LOG_URL` disables logging entirely — no requests, no queue.

### On a PHP host instead

Use `log.php` in place of `serve.js` and point the pages at it:

```sh
sed -i 's|var LOG_URL = "log";|var LOG_URL = "log.php";|' *.html
```

### What a line looks like

One JSON object per line. `t` is when the browser made the event, `r` when the host
received it, `s` a random per-tab id, `p` the page, `e` the kind of event.

```json
{"t":"…","s":"k3f9a2","p":"arith","e":"open","r":"…"}
{"t":"…","s":"k3f9a2","p":"arith","e":"start","f":"p -> (q -> p)","vars":2,"r":"…"}
{"t":"…","s":"k3f9a2","p":"arith","e":"step","a":"SQ","ok":false,"why":"nowhere","n":28,"r":"…"}
{"t":"…","s":"k3f9a2","p":"arith","e":"step","a":"EXP","ok":true,"at":1,"terms":3,"r":"…"}
{"t":"…","s":"k3f9a2","p":"arith","e":"done","ok":true,"rules":5,"verdict":"tautology","r":"…"}
```

| event   | when                          | fields                                             |
|---------|-------------------------------|----------------------------------------------------|
| `open`  | page loaded                   | —                                                    |
| `start` | a formula was submitted       | `f` (or `fa`/`fb` on equiv), `vars`, `err`           |
| `step`  | a rule or move was attempted  | `a` action, `ok`, `why` when refused, `n` if repeated|
| `done`  | the student pressed Done      | `ok`, plus the verdict and shape of the result       |

The default formula a page loads with is **not** logged as a `start` — only what
someone actually submits. A run of identical refusals collapses into one row with a
repeat count `n`, so jabbing a dimmed button twenty times is one line, not twenty.

### Reading it

```sh
# which formulas get tried
jq -r 'select(.e=="start" and .f) | .f' logs/events.jsonl | sort | uniq -c | sort -rn | head -20

# the commonest mistakes — the most useful view
jq -r 'select(.e=="step" and .ok==false) | "\(.p)\t\(.a)\t\(.why // "-")"' logs/events.jsonl \
  | sort | uniq -c | sort -rn | head -20

# how often a session reaches Done, per tool
jq -r 'select(.e=="done") | "\(.p)\t\(.ok)"' logs/events.jsonl | sort | uniq -c

# distinct sessions and page views
jq -r .s logs/events.jsonl | sort -u | wc -l
jq -r 'select(.e=="open") | .p' logs/events.jsonl | sort | uniq -c
```

### Privacy

The log records what was typed into the formula boxes and which buttons were
pressed. There is no name, no account and no cookie; `s` is a random id held in
`sessionStorage`, so it lasts one tab and is not linkable across visits. Neither
`serve.js` nor `log.php` records IP addresses.

Two things to be aware of before running this for a real cohort:

- A reverse proxy in front of the receiver (nginx, Apache) writes IPs to its own
  access log unless you turn that off. Timestamp plus IP is enough to make the
  whole record personal data.
- A formula box is a free-text field, and free-text fields collect whatever people
  type into them.

So: say on the page or in the course notes that the tools log what is entered, and
decide how long `events.jsonl` is kept. If none of that is wanted, the `sed` above
switches logging off and the pages go back to making no network calls at all.
