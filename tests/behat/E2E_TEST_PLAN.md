# WirisQuizzes — Behat E2E Test Plan

> Cross-cutting plan for the Behat end-to-end (E2E) suite of the WirisQuizzes
> plugin family (`qtype_wq` + the six WIRIS question types). It is stored in the
> `qtype_wq` plugin because that plugin is the shared base every WIRIS question
> type depends on.

- Status: living document.
- Environment: [wiris-moodle-docker](https://github.com/wiris/wiris-moodle-docker).
- Targets: Moodle 4.5 → latest (currently 5.2), all PHP versions supported by each branch.

---

## 1. Scope & philosophy

E2E (Behat) tests should validate **workflows and UI integration across components**
in a real browser. They must **not** re-test logic that is cheaper and more
reliable to cover with PHPUnit.

| Belongs in E2E (Behat)                                              | Belongs in unit / integration (PHPUnit)                          |
| ------------------------------------------------------------------ | ---------------------------------------------------------------- |
| Question renders, can be attempted, reviewed in the browser        | Grade arithmetic / fraction maths for a given response           |
| Backup → restore → re-attempt **workflow** succeeds                 | Backup/restore **data integrity** at the DB/structure level      |
| Teacher preview / duplicate / regrade **UI actions** work          | WIRIS Quizzes XML (de)serialisation, `question.php` grading       |
| Editor (TinyMCE + WIRIS) loads and the edit form saves             | `questiontype.php` save/load round-trips                         |
| Localized UI strings appear in chosen language                     | `lang` string existence / `get_string` resolution                |
| Install/status page (`info.php`) is reachable and reports state     | Plugin version metadata, capability definitions                  |

Guiding rules:

1. **Create questions with data generators** (`the following "questions" exist`)
   wherever possible. It is robust, fast and version-stable.
2. **Reserve UI question creation** for explicitly testing the edit form / save
   path. WIRIS answer inputs are MathType-overlaid (`class="wirisprocessed"`,
   *not reachable by keyboard*), so never type into them via the UI — fill only
   `Question name` / `Question text` and save.
3. Prefer **named selectors and accessibility labels** over brittle XPath.
4. Reuse shared steps from `qtype_wq/tests/behat/behat_wq_base.php`.

---

## 2. JavaScript blocker — root cause, workaround, revert plan

### Symptom
Any scenario that creates/edits a WIRIS question through the UI aborts with:

```
javascript error: Error: Mismatched anonymous define() module
https://requirejs.org/docs/errors.html#mismatch
```

thrown while Moodle sets the TinyMCE *Question text* field via
`require(['editor_tiny/editor'], …)`.

### Root cause
The WIRIS Quizzes client library `quizzes.js` is loaded with a plain `<script>`
tag through `quizzes/service.php`
(`wq/renderer.php` and `wq/questiontype.php::display_question_editing_page`). The
bundle is a **UMD module**: on a page that already exposes RequireJS (every
Moodle page) it detects `define.amd` and registers an **anonymous `define()`**.
Because the script is not loaded *through* RequireJS, that anonymous define is
orphaned in RequireJS's private `globalDefQueue`. The next genuine
`require([...])` runs `intakeDefines()` → `takeGlobalQueue()`, finds the
anonymous entry (`args[0] === null`) and calls
`onError(makeError('mismatch', …))` (`lib/requirejs/require.js:1255`). The
default `req.onError` re-throws, producing an uncaught JS error. Moodle Behat
fails on any JS error, so the scenario stops **before** the functionality under
test is reached.

### Classification
**Not a Moodle bug.** A latent **WIRIS product-integration defect**: shipping a
UMD bundle that pollutes the global AMD loader. It is effectively harmless for
end users (the editor also falls back to the `window` global, and users rarely
trigger a `require()` at that exact instant), which is why it never surfaced in
production — but it is genuinely fragile. For our E2E suite it is a
**harmless-but-blocking** error: unrelated to WIRIS question behaviour, yet it
aborts every scenario that sets a TinyMCE field after the WIRIS editor loads.

### Workaround (implemented)
`qtype_wq/tests/behat/behat_wq_base.php`:

- `const WIRIS_AMD_GUARD_JS` — installs a guard that overrides
  `requirejs.onError`/`require.onError` to **swallow only**
  `err.requireType === 'mismatch'` and **re-throw everything else**, and
  defensively drains any queued anonymous `define()`. Global JS error detection
  stays fully enabled.
- `@When I work around the Wiris Quizzes editor AMD conflict` — standalone step
  for multi-step features.
- `@When I add a "TYPE" Wiris question filling the form with:` — mirrors core
  `behat_core_question::i_add_a_question_filling_the_form_with()` but installs
  the guard between *open edit form* and *fill fields* (core does both in one
  step, leaving no other insertion point).

**Why it is safe:** it only neutralises one specific, well-understood RequireJS
error class produced by the WIRIS UMD bundle; any other JS error still fails the
test. It is scoped to WIRIS scenarios (used by WIRIS steps only).

**Verified:** probe creating an Essay-WIRIS question through the UI →
`1 scenario (1 passed) / 7 steps (7 passed)` on Moodle 5.2.1+.

### Revert plan
When the product fixes the bundle (e.g. temporarily unset `define.amd` around
the `quizzes.js` script load, or load it as a proper AMD module), delete
`WIRIS_AMD_GUARD_JS`, the two steps above, and switch features back to the core
`I add a "..." question filling the form with:` step.

---

## 3. Tagging strategy

### Remove
All ticket-style tags matching `@wqmdl-*` (e.g. `@wqmdl-274`) and the ad-hoc
`@studentwiris`.

### Layered tags

| Layer            | Tags                                                                                             | Notes |
| ---------------- | ------------------------------------------------------------------------------------------------ | ----- |
| Component (req.) | `@qtype_wq`, `@qtype_shortanswerwiris`, `@qtype_essaywiris`, `@qtype_matchwiris`, `@qtype_multichoicewiris`, `@qtype_multianswerwiris`, `@qtype_truefalsewiris` | Moodle convention: a feature carries the component tag of the plugin it lives in, plus any core area it drives (`@mod_quiz`, `@core_question`, `@core_backup`, `@core_question_bank`). |
| Suite umbrella   | `@wq`                                                                                             | On **every** WirisQuizzes feature. Enables suite-wide runs and gates shared hooks/steps. |
| User role        | `@teacher`, `@student`, `@admin`                                                                  | Primary actor of the scenario. |
| Test scope       | `@smoke`, `@regression`                                                                           | `@smoke` = fast, must-pass; `@regression` = broader. |
| Functional area  | `@attempt`, `@preview`, `@review`, `@duplicate`, `@backuprestore`, `@grading`, `@manualgrading`, `@regrade`, `@questionbank`, `@versioning`, `@import`, `@install`, `@inputoptions` | One or more per feature. `@inputoptions` = per-question-type coverage of each answer-input option (see §4a). |

Conventional ordering (filtering is order-independent; this is for readability):
`@<component...> @wq @javascript @<role> @<scope> @<area...>`.

Example:
```gherkin
@qtype_shortanswerwiris @wq @javascript @student @attempt @regression
Feature: Student answers a quiz with a Short answer (WIRIS) question
```

### Useful run selections
```bash
# Fast gate
--tags '@wq&&@smoke'
# Everything WIRIS
--tags '@wq'
# Backup/restore only
--tags '@wq&&@backuprestore'
# One question type
--tags '@qtype_matchwiris'
# Per-input-option coverage (all types)
--tags '@inputoptions'
```

---

## 4a. Per-input-option coverage (`@inputoptions`)

Each WIRIS question type has a dedicated `*_input_types.feature` that exercises
**every answer-input option** the type offers, preferring full attempt+grade where
the control is fillable and falling back to render+complete where it is a
MathType/canvas overlay. Status: **all six types green** (verified individually;
15/16 green in a combined `@inputoptions` run — the 1 miss is the documented
intermittent overlay alert in short-answer, which passes in isolation).

| Type | Feature | Input options covered | Fillable + graded | Render-only (overlay) |
| ---- | ------- | --------------------- | ----------------- | --------------------- |
| SA | `shortanswerwiris/.../shortanswer_input_types.feature` | text field, inline equation, graphical, compound | text field (typed → 1.00) | equation, graphical, compound (MathType/canvas) |
| CL | `multianswerwiris/.../cloze_input_types.feature` | numerical, dropdown (MC), radio (MC_V), checkbox (MR), short-answer | numerical, dropdown, radio (graded) | wrapped SHORTANSWER (MathType), checkbox (render) |
| MC | `multichoicewiris/.../multichoice_input_types.feature` | single (radios), multiple (checkboxes) | both (selected → graded) | — |
| ES | `essaywiris/.../essay_input_types.feature` | editor, plain, monospaced, attachments | editor/plain/monospaced (typed, manual grade) | attachments (upload area render) |
| TF | `truefalsewiris/.../truefalse_input_types.feature` | True / False radios | both (True/False/wrong → 1.00/1.00/0.00) | — |
| MT | `matchwiris/.../match_input_types.feature` | per-row matching dropdowns | all-correct (1.00) | selections accepted + attempt completes (wrong/partial mark = PHPUnit; WIRIS-service non-deterministic) |

Key mechanics (see context.md for full notes): custom **choices** need a helper
`get_..._form_data_*` template (inline `answers[N]`/`subanswers[N]` columns are
ignored); **scalar** options (`responseformat`, `attachments`, `correctanswer`,
`single`) work as inline generator columns; multichoice choices are clicked with
`"qtype_multichoice > Answer"` (aria-labelledby, no `<label for>`); deterministic
WIRIS grading needs a fixed `wirisquestion` (and `wirisoverrideanswer=''` for TF).

---

## 4. Coverage analysis

Legend — **E**xisting, **G**ap, **→** recommended E2E action.
Question types: TF=truefalse, MC=multichoice, SA=shortanswer, ES=essay,
MT=match, CL=cloze/multianswer.

| Area | Existing coverage | Missing coverage | Recommended E2E | Better as unit/integration |
| ---- | ----------------- | ---------------- | --------------- | -------------------------- |
| Student attempt | One scenario per type (data-gen), mostly no grade/feedback asserts | grade & feedback asserts; SA/CL grade; review screen | Harden per-type attempt + a mixed "all types" attempt; assert mark/feedback | Grade arithmetic, partial fractions |
| Quiz preview (teacher) | `quiz_preview` for TF/MC/ES/MT | SA + CL missing; no feedback/navigation/grade checks | Preview all 6 types; check render + navigation + feedback | — |
| Question-bank preview | `question_preview` for TF/MC/ES/MT | SA + CL missing | Preview all 6 from bank | Renderer output details |
| Duplicate quiz | `quiz_duplicate` (bank intact) | settings preserved; independent attempts; duplicated quiz attemptable | Duplicate → verify copy + independence + attemptable | — |
| Backup / restore | `create_backup` (backup only, **broken**, `@qtype_wq` removed) | restore; attempts/grades/settings preserved; restored quiz editable + attemptable | Full backup→restore→re-attempt for all 6 types | Restore DB structure integrity |
| Delete | `delete_quiz`, `remove_question` (bank intact) | — | Keep; retag | — |
| Custom grading | none | max marks, partial marks display | Quiz with custom max marks; assert displayed grade | Fraction/partial maths |
| Regrade | none | regrade workflow per type | Trigger regrade; assert results table consistent | Recalculated grade values |
| Manual grading | none | essay manual grade workflow | Grade ES attempt manually; assert final grade | — |
| Versioning | none | create v2, edit, use in quiz, old attempts intact | Create new version via UI (guarded); reuse; old attempt unchanged | Version metadata |
| Import | none | GIFT/Moodle-XML import preserves WIRIS config | Import Moodle-XML with WIRIS payload; verify question usable | XML payload fidelity |
| Localization | none | en/es/ca UI + WIRIS strings | **Excluded from E2E** (see §8) — not feasible offline and low E2E value | `get_string` resolution + lang-file presence |
| Install / status | none | `question/type/wq/info.php` reachable & reports status | Admin opens `info.php`; assert status info | Version/capability metadata |
| Create/edit via UI | none (was JS-blocked) | edit form loads, WIRIS studio button, save | Teacher creates ES via UI (guarded) + edits; save | Form element wiring |
| Input options (per type) | none | each answer-input option per question type exercised | Per-type `*_input_types.feature` (`@inputoptions`) — fill+grade where fillable, render where overlay (see §4a) | Math-content answering of MathType/canvas overlays (PHPUnit) |
| Comprehensive regression | none | end-to-end across all roles | "All Question Types Quiz" core scenario | — |

---

## 5. Test plan (feature files)

New/updated feature files. `wq` = generic area (`qtype_wq`); per-type = that
plugin's repo.

| # | File | Feature | Role | Tags | Prio |
| - | ---- | ------- | ---- | ---- | ---- |
| 1 | `qtype_wq/tests/behat/install_smoke.feature` | Plugin install/status via `info.php` | admin | `@qtype_wq @wq @javascript @admin @smoke @install` | P0 |
| 2 | `qtype_wq/tests/behat/all_question_types_quiz.feature` | "All Question Types Quiz" comprehensive regression | admin+teacher+student | `@qtype_wq @mod_quiz @wq @javascript @regression @backuprestore @regrade @duplicate` | P0 |
| 3 | `qtype_wq/tests/behat/backup_restore.feature` | Backup → restore → re-attempt, all 6 types (replaces broken `create_backup`) | teacher | `@qtype_wq @core_backup @mod_quiz @wq @javascript @teacher @backuprestore @regression` | P0 |
| 4 | `qtype_wq/tests/behat/quiz_preview.feature` (update) | Teacher preview, all 6 types + feedback/navigation | teacher | `@qtype_wq @mod_quiz @wq @javascript @teacher @preview @regression` | P1 |
| 5 | `qtype_wq/tests/behat/quiz_duplicate.feature` (update) | Duplicate + independence + attemptable | teacher | `@qtype_wq @mod_quiz @wq @javascript @teacher @duplicate @regression` | P1 |
| 6 | `qtype_wq/tests/behat/grading_custom_marks.feature` | Custom max marks + partial-mark display | teacher+student | `@qtype_wq @mod_quiz @wq @javascript @grading @regression` | P1 |
| 7 | `qtype_wq/tests/behat/regrade.feature` | Trigger regrade, results consistent | teacher | `@qtype_wq @mod_quiz @wq @javascript @teacher @regrade @regression` | P1 |
| 8 | `qtype_wq/tests/behat/manual_grading.feature` | Manual grade an Essay-WIRIS attempt | teacher | `@qtype_wq @qtype_essaywiris @mod_quiz @wq @javascript @teacher @manualgrading @regression` | P1 |
| 9 | `qtype_wq/tests/behat/create_edit_question_ui.feature` | Create + edit WIRIS question via UI (guarded) | teacher | `@qtype_wq @core_question @wq @javascript @teacher @questionbank @smoke` | P1 |
| 10 | `qtype_wq/tests/behat/question_versioning.feature` | New version, reuse, old attempts intact | teacher | `@qtype_wq @core_question @wq @javascript @teacher @versioning @regression` | P2 |
| 11 | `qtype_wq/tests/behat/import_questions.feature` | Import Moodle-XML preserving WIRIS config | teacher | `@qtype_wq @core_question @wq @javascript @teacher @import @regression` | P2 |
| 12 | `qtype_<type>/tests/behat/student.feature` (update ×6) | Harden per-type attempt: assert mark/feedback/review | student | `@qtype_<type> @wq @javascript @student @attempt @regression` | P1 |
| 13 | `qtype_wq/tests/behat/student_review.feature` | Review screen, feedback visibility, attempt history | student | `@qtype_wq @mod_quiz @wq @javascript @student @review @regression` | P2 |
| 14 | `qtype_<type>/tests/behat/<type>_input_types.feature` (×6) | Per-type coverage of each answer-input option (see §4a) | student | `@qtype_<type> @wq @javascript @student @attempt @inputoptions @regression` | P2 |

> Localization (`es`/`ca`) was considered for an E2E feature but is **excluded** —
> see §8 for the rationale (offline harness has only the `en` pack; Moodle loads
> strings only for installed languages; the value is better covered by string-file
> checks than by browser E2E).

Shared assets:
- `behat_wq_base.php` — AMD workaround + reusable steps (created).
- Reusable “all six WIRIS questions in a bank” background → keep as a copy-paste
  block (Behat has no shared Background include) but standardise the wording so
  it is greppable and identical across files.

---

## 6. Implementation plan (priority)

### P0 — Critical coverage & blockers
- [x] JS workaround in `behat_wq_base.php` (verified).
- [ ] Tagging cleanup: remove `@wqmdl-*` / `@studentwiris`, apply strategy to all
      existing features.
- [ ] `install_smoke.feature` (`info.php`).
- [ ] `backup_restore.feature` (replaces broken `create_backup.feature`).
- [ ] `all_question_types_quiz.feature` (comprehensive regression).

### P1 — Important regression coverage
- [ ] Harden the six per-type `student.feature` files (assert grade/feedback/review).
- [ ] Update `quiz_preview.feature` to all six types.
- [ ] Update `quiz_duplicate.feature` (independence + attemptable).
- [ ] `grading_custom_marks.feature`.
- [ ] `regrade.feature`.
- [ ] `manual_grading.feature` (Essay).
- [ ] `create_edit_question_ui.feature` (exercises the JS workaround in-suite).

### P2 — Additional valuable coverage
- [ ] `question_versioning.feature`.
- [x] `import_questions.feature`.
- [x] `student_review.feature`.
- [x] Per-input-option features for all six types (`*_input_types.feature`, `@inputoptions`) — see §4a.
- ~~`localization.feature`~~ — **excluded** (see §8).

---

## 7. CI / GitHub Actions

Every plugin repo already has `.github/workflows/ci.yml` (moodle-plugin-ci).
Rather than add a separate `e2e-nightly.yml`, the **existing `ci.yml` of each
plugin was adapted** to also run on a nightly schedule and on manual dispatch, so
the full Behat matrix (not just PR pushes) is exercised every day.

What was changed in each `ci.yml`:
- Added triggers `workflow_dispatch:` and `schedule: cron "0 6 * * *"` (daily
  06:00 UTC) on top of the existing `push`/`pull_request`.
- Added **`MOODLE_502_STABLE`** to the matrix (so it spans Moodle 4.5 → 5.2 →
  `main`) with PHP exclusions matching each branch's support window.
- `qtype_wq` is the **aggregator**: its workflow already installs all six sibling
  WIRIS qtypes, so its Behat step runs the whole suite via `--tags @wq`. Each
  qtype repo keeps running its own `--tags @qtype_<type>`.

PHP-per-Moodle support (keep in sync with
[Moodle dev docs](https://moodledev.io/general/development/policies/php)):

| Moodle branch     | Supported PHP |
| ----------------- | ------------- |
| `MOODLE_405_STABLE` (4.5 LTS) | 8.1, 8.2, 8.3 |
| `MOODLE_500_STABLE` (5.0)     | 8.2, 8.3, 8.4 |
| `MOODLE_501_STABLE` (5.1)     | 8.2, 8.3, 8.4 |
| `MOODLE_502_STABLE` (5.2)     | 8.2, 8.3, 8.4 |

The matrices `exclude:` the unsupported PHP/branch pairs and keep `main` pinned to
the latest PHP via `include:`. Behat faildumps are uploaded as artifacts on
failure (pre-existing step).

Open follow-ups for CI:
- `local_wirisquizzes/ci.yml` is legacy (Moodle 3.9–4.0 on `ubuntu-18.04`); it
  received the nightly trigger for parity but its matrix needs a separate
  modernisation pass.
- Provide a test `configuration.ini` (or service mock) if any scenario needs the
  WIRIS cloud service; current scenarios avoid it.

---

## 8. Assumptions, limitations & remaining gaps

- **WIRIS answer inputs are not E2E-fillable** (MathType overlay). Editing the
  *mathematical* content of a WIRIS answer/variable through Behat is out of scope;
  it is covered by data generators + PHPUnit. Only `Question name`/`Question text`
  are set via the UI.
- **Grade correctness** (exact marks for a maths response) is intentionally left
  to PHPUnit; E2E asserts that *a* grade/feedback is shown and the workflow
  completes, not the arithmetic.
- **WIRIS cloud service** is not exercised; scenarios use locally renderable
  content so they run without `configuration.ini`.
- **Import**: only Moodle-XML is covered (it carries the `<wirisquestion>`
  payload). GIFT does not round-trip WIRIS data and is excluded.
- **Cloze (multianswer)** UI creation remains data-generator only; its embedded
  syntax plus MathType make UI authoring brittle.
- **Localization (`es`/`ca`) is excluded from the E2E suite.** Three reasons:
  (1) *Not feasible in the harness* — the Behat site ships only the `en` core
  pack, the container has no outbound network to `download.moodle.org`, and there
  is no `install_language_pack` CLI, so the `language packs` generator cannot run
  and the `language customisations` generator only edits already-installed
  languages. Moodle loads strings exclusively for installed languages, so a WIRIS
  plugin's bundled `es`/`ca` strings still resolve to English; a stub `lang`
  folder dropped into the dataroot is wiped by `behat_util::reset_dataroot()`
  (its `datarootskiponreset` keeps only `behat`/marker files). (2) *Low E2E
  value* — what localization needs is that the right `get_string()` key resolves
  to the right translation, which is a string-resolution concern best verified by
  a cheap lang-file/`get_string` check, not a browser workflow. (3) The same
  rendering/editor/attempt workflows are already exercised in English by the rest
  of the suite; re-running them per language would duplicate coverage for the cost
  of language-pack provisioning. *Recommended follow-up (outside E2E):* a tiny
  PHPUnit/CI check that each WIRIS plugin's `lang/{es,ca}` files exist and expose
  the same string keys as `lang/en`.
