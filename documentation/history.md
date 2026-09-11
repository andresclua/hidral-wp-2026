# History of the project

Please register all relevant modifications to files, JS and state if these modifications took place in a staging, production or local environment, to which branches the files were commited and who did those changes.

---

## 2026-09-08 — Bug found: ACF / Yoast "too early" notices (NOT YET FIXED)

- **Environment:** local
- **Branch:** `re-do-2027`
- **Found by:** andres@terrahq.com

**Symptom:** on every page load, PHP notices appear:
```
Function _load_textdomain_just_in_time was called incorrectly. Translation loading
for the wordpress-seo domain was triggered too early...
Function acf_get_value was called incorrectly... called before ACF has been initialized.
```

**Root cause:** `functions.php` runs `$app = new Core(); $app->init();` at the top
level, so it executes while the theme's `functions.php` is being loaded — **before**
WordPress fires the `init` action. `Core::init()` synchronously constructs
`System_Warning`, and `functions/framework/classes/System_Warning.php:73-74` calls
`get_field('terra_system_warning_interval', 'option')` directly in the constructor.
That's an ACF call before ACF itself is ready, which cascades into ACF (and then
Yoast) loading their translations too early.

**File:** `functions/framework/classes/System_Warning.php` (Terra Framework code,
but tracked in this repo — see note below).

**Proposed fix (not applied yet, pending team decision):** move the `get_field()`
call — and the constructor block that starts `Mail_To` / `Google_Search_Console` /
`Terra_URL_Health_Check` (lines ~80-94), which also runs unhooked and depends on
`$this->interval` — into an `add_action('init', ...)` callback instead of running
them straight from `__construct()`.

**Note on repo:** `functions/framework/` is documented (`CLAUDE.md`) as a separate
Terra Framework repo, cloned independently and gitignored (`/functions/framework`
in `.gitignore`). In practice, in *this* repo the framework files were already
committed before that ignore rule was added, so `git ls-files` still tracks them
(e.g. `System_Warning.php`) and any fix here would be pushed as part of
`hidral-wp-2026`, not a separate framework repo/remote.

