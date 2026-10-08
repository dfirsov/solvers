# Logic Solvers

Interactive tools for a logic course. Each takes a formula and works through a
method step by step, checking every move rather than handing over an answer.

Propositional, with the first predicate tool alongside; modal is planned, which
is why the set is not named after propositional logic.

| file             | tool                 | method                                     |
|------------------|----------------------|--------------------------------------------|
| `tableaux.html`  | Semantic Tableaux    | signed T/F tableaux, α and β rules          |
| `natded.html`    | Natural Deduction    | Gentzen trees, intro and elim rules         |
| `sequent.html`   | Sequent Calculus     | cut-free LK, countermodels from stuck leaves|
| `bdd.html`       | BDD Builder          | Shannon expansion, unordered diagrams       |
| `robdd.html`     | ROBDD Constructor    | C1/C2/C3 reduction under a fixed order      |
| `arith.html`     | Arithmetic Encoding  | Boolean polynomials, ring laws + `x·x = x`  |
| `equiv.html`     | Equivalence Checker  | truth tables, counterexamples               |
| `predicate.html` | Predicate Deduction  | ∀I ∀E ∃I ∃E, function symbols, Dilemma      |
| `predseq.html`   | Predicate Sequents   | cut-free LK with ∀L ∀R ∃L ∃R                |
| `clausal.html`   | Clausal Form         | prenex form, CNF, skolemisation             |
| `kripke.html`    | Kripke Models        | worlds, arrows, valuations, frame conditions, K E D C |
| `modaltab.html`  | Modal Tableaux       | labelled tableaux, K through S5             |
| `corresp.html`   | Frame Correspondence | axiom ↔ frame condition, checked both ways  |

### What is different about the predicate page

**Nothing is decided by the case of a name.** `p(x)` and `P(x)` are both
predicates; `f(a)` and `F(a)` are both terms. What decides is where the name
stands: at a formula position it is a predicate, inside a term's brackets it is
a function symbol, and a bare name is a propositional letter or a constant by
the same test. So `∀x p(x, f(x))` reads the way it looks, and each name keeps
one job and one arity throughout — mixing them is reported, not guessed at.

**A quantifier reaches over one unit, and the dot widens it.** `∃x p(x) ∨ ∃x q(x)`
is two separate scopes, as it is on paper. Church's dot takes the scope to the
end instead, so `∀x. p(x) ⊃ q(x)` is `∀x (p(x) ⊃ q(x))` while `∀x p(x) ⊃ q(x)`
is `(∀x p(x)) ⊃ q(x)`. Brackets always decide, and the tree shows you the
reading immediately. Quantifiers are written `forall`/`exists` or `∀`/`∃`;
`all` and `some` work too.

**Terms are terms, not just names.** `∀E` and `∃I` take any term you can build
from the function symbols in play, so `∀x p(x) ⊢ p(f(f(a)))` is one step. A
brand-new *constant* is accepted as well — a domain is never empty, so naming a
fresh thing is sound, and `⊢ ∃x (p(x) ⊃ ∀y p(y))` cannot be started without it,
there being no term in that sequent at all. A brand-new *function symbol* is
refused, because at that point it is a typo far more often than an intention.

Two things the propositional pages do, this one cannot:

- **It will not tell you a sequent is provable before you prove it.** First-order
  validity is undecidable. What it does instead is look for a countermodel in a
  domain of at most three — interpreting every function symbol as well as every
  predicate — which settles the question the *other* way: if one turns up, stop,
  because no proof exists.
- **A parameter is not a variable you chose.** `∀I` and `∃E` each mint a fresh
  name — `a`, then `a2`, and so on — and that freshness is the whole eigenvariable
  condition. Everything already in scope where a parameter is born was written
  down before it existed, so nothing in scope can mention it. `Done` re-checks
  each `∀I` and `∃E` against the finished tree rather than trusting the argument.

#### Frame correspondence

Harjutus 7 opens by asking what frame condition an axiom corresponds to, and
`corresp.html` is for checking an answer. You give the axiom and the condition —
the condition as a first-order statement about the one relation a frame has,
`forall w. forall u. forall v. R(w,u) & R(w,v) -> u = v`, which is the language
lecture 12 writes the correspondence table in — and every frame up to three or
four worlds is tested twice over: is the axiom valid on it, and does the
condition hold of it.

Correspondence is exactly the claim that those two answers never differ, so a
frame where they do is the answer to the exercise, and that is what the page
shows — with a picture, and a link that opens it in the model lab. It also says
*which way* the answer is wrong, which is the useful part: a frame validating
the axiom without satisfying the condition means the condition is too **strong**,
one satisfying it without validating means too **weak**.

Leave the condition empty and it just shows the frames the axiom is and is not
valid on, which is how to find the condition rather than check it.

This is evidence, not a proof — it says only that no frame that small tells the
two apart. The argument is still yours to make, and the frames shown are the
ones to make it about.

#### Modal tableaux

`modaltab.html` is the propositional tableaux page with worlds. A line is
either a signed formula *at a world*, `w0: T □p`, or an accessibility atom,
`w0 R w1`. Writing the relation down rather than hiding it inside the shape of
the prefixes is the whole point: an open branch then **is** a Kripke structure,
and the page hands it to `kripke.html` through the link in the verdict.

The modal rules come in two kinds, which is the thing to learn:

- `□T` and `◇F` say *every successor*, so they fire once per arrow and can fire
  again when a new arrow turns up. They are never finished.
- `□F` and `◇T` say *some successor*, so they make one new world and are done.

The logic is chosen by switching frame conditions on — the row of `D T 4 B 5`
toggles — and each has a button that adds the arrows it demands. A branch
cannot be called open until the conditions are met, because a countermodel has
to be a frame of the logic. `K4` and `B` are what harjutus 6 asks for; the
usual names (`K`, `D`, `T`, `S4`, `B`, `S5`) appear automatically when the
switches match one.

Every structure read off a branch is checked before it is offered: it has to
satisfy the frame conditions and actually refute the formula. A tool that hands
you a countermodel should have looked at it first.

**A known limit.** With **transitivity** or **euclideanness** on, the search
need not terminate: a `◇` demands a fresh world, the closure draws an arrow
that reaches it, and that wakes the `◇` again. Proofs still close — it is the
*countermodels* that can run away — and K, D, T, KB and B are unaffected.
Making S4 and S5 terminate needs a loop check (blocking) that this page does
not do. Instead it stops at forty worlds and explains what it is looking at:
the formulas repeating at the last few worlds are the countermodel.

#### Kripke models

`kripke.html` is a laboratory rather than a solver: you build the structure and
it tells you what is true where. **Drag a world to move it**; drag from the dot
on its right edge onto another world to add or remove an arrow, onto itself for
a loop; click a letter to flip it. Worlds start on a circle and stay where they
are put, which is what makes a four-cycle readable instead of a tangle —
`tidy` puts them back. Where they sit is only a picture, so it is not part of
the structure's text form. Every
world then carries a T or an F for the formula, and the panel shows the whole
subformula chain at the selected world — including, for each `□` and `◇`, which
successors made it come out that way. That last line is the "justify" the
exercises keep asking for.

Alongside it the five frame conditions of lecture 10 — serial, reflexive,
transitive, symmetric, euclidean — each lit when it holds and naming a
witnessing triple when it does not, plus which of K, D, T, B, S4, S5 the frame
belongs to.

Two questions it will answer outright, both by exhausting the valuations of the
frame you have drawn, which is small enough to be instant at these sizes:

- **find a valuation** making the formula true at the selected world, and load it;
- **valid on this frame?** — true in every world under every valuation. When the
  answer is no it also says whether the formula is nonetheless valid at the
  *selected* world, because that is what an exercise about one world is asking.

The structure has a text form (`W=3; R=0>1,0>2,2>0; p=1,2`) which can be pasted
in, so a structure given in an exercise takes seconds to set up.

##### More than one agent

Lecture 13's epistemic logic needs one accessibility relation per agent, so the
page carries a list of them. Arrows belong to whichever agent is picked in the
strip, and are drawn in that agent's colour with its name on them; the frame
conditions are then reported per agent, since knowledge wants each relation to
be an equivalence. The text form names them: `W=3; Ra=0>1; Rb=0>2; p=1`, with a
bare `R=` still meaning the first agent.

The operators are the lecture's, and each is just a different set of arrows to
look along:

| | reads along | written |
|---|---|---|
| `K_a A` | *a*'s own arrows | agent *a* knows A |
| `E A` | the **union** of them | everyone knows A |
| `D A` | the **intersection** | distributed knowledge |
| `C A` | any **chain** of one arrow or more | common knowledge |

`□` and `◇` still mean the first agent's relation, so everything single-agent
reads as before. The panel names which of these a modality is looking along —
"every *b*-successor: w0 ✓, w1 ✗" — which is the part of an epistemic argument
that is easy to get wrong on paper.

What it deliberately does not do is decide validity in a *logic* — that is
quantifying over all frames of a class, and belongs to a tableaux tool rather
than to a model editor.

#### Predicate sequents

`predseq.html` is `sequent.html` with the four quantifier rules, in the form the
lecture gives them:

```
  Γ → A[y/x], ∆              Γ, (∀x A,) A[t/x] → ∆
  ───────────── ∀R           ───────────────────── ∀L
  Γ → ∀x A, ∆                     Γ, ∀x A → ∆

  Γ → A[t/x], (∃x A,) ∆           Γ, A[y/x] → ∆
  ───────────────────── ∃R        ───────────── ∃L
  Γ → ∃x A, ∆                     Γ, ∃x A → ∆
```

`∀R` and `∃L` mint a parameter named after the variable it stands for — `x`
becomes `x'`, as the handout writes `x⁰` — and it is fresh to the whole proof,
which is the side condition. `∀L` and `∃R` ask you for a term instead, and
offer the one that would close the branch first, having found it by matching.

**The brackets in `(∀x A, )` are the interesting part.** Keeping the quantified
formula is optional in the rule and the tool always keeps it, because that is
what lets you instantiate the same quantifier twice — `⊢ ∃x (p(x) ⊃ ∀x p(x))`
cannot be proved otherwise. The price is that proofs carry one more formula
than the handout bothers to write, and that bottom-up search no longer has to
terminate.

That last point is the whole difference from the propositional page. There,
every rule strictly simplifies the sequent, so the search always ends and a
leaf that runs out of rules *is* a countermodel — failing to find a proof is a
decision procedure. Here `∀L` and `∃R` put in terms that need not come from the
goal, so a leaf of bare atoms means only that this route failed; a different
term further down might not have. The page says so rather than pretending, and
offers a countermodel search over domains of up to three to settle what it can.

#### Clausal form

`clausal.html` is a rewriting tool rather than a proving one: you pick a
subformula, pick a law, and the step is added to a chain of `⇔`s laid out the
way the course writes it. The route is the one from the lectures — strip the
implications, push the negations down, pull the quantifiers to the front
(renaming where one would be captured), put the matrix into conjunctive normal
form, then skolemise. A strip of five milestones says which of those are done.

The step that matters is the last one. **Skolemisation is not an equivalence**,
and the chain says so: that line is marked `equisat.` rather than `⇔`, because
the result is satisfiable exactly when the original is and no more than that.
`Herbrandise` is there too, the mirror image, which keeps validity instead.

Three things to know about reading the output. A nullary Skolem symbol is a
constant and is named like one (`c`, `d`), while the rest are functions of the
∀-bound variables before them (`f`, `g`); either way the name dodges every
symbol already in the formula, predicates included. The variables left loose at
the end are **universally quantified** — that implicit ∀ is part of what clausal
form means, and it is what the equisatisfiability check puts back before
comparing.

And **the order you pull quantifiers in matters**. Where both halves of a
conjunction carry one, whichever is left inside a ∀ will skolemise to a
function of it rather than to a constant — the clauses stay correct but say
less than they could. Clicking the quantifier itself, rather than the ∧ below
it, is how you say which one comes out first; the tool points this out the
moment the choice arises. Courses that skolemise in place instead of prenexing
first (UCC's handout26, say) never meet the question, and get the tighter
answer by construction.

`Done` lists the clauses and states the relationship to the original formula,
having checked every interpretation over a domain of up to three.

#### Dilemma

Alongside `RAA` there is `Dilemma`: pick any formula *A*, prove the goal once
with *A* assumed and once with *¬A* assumed, and the goal follows outright. It
is excluded middle in rule form, so a proof using it is classical and the
closing note says so.

It is not a convenience. `⊢ ∃x p(x) ∨ ∀x ¬p(x)` splits open the moment you take
*A* to be `∃x p(x)`, and `⊢ ∃x ¬p(x) ∨ ∀x p(x)` wants it twice over; with `RAA`
alone both are a slog.

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
