# Coda MCP Worker Protection

A single-file WordPress must-use plugin, version 1.0.0. It protects the site's
small PHP-FPM pool from idle AI Engine MCP listening streams independently of
the active theme. There are no settings, database migrations, or activation hooks.

## Behavior

- After WordPress route permission checks, standalone `GET /mcp/v1/http`
  returns HTTP 405 with `Cache-Control: no-store`.
- POST requests, DELETE cleanup, OPTIONS, other REST routes, and previous
  dispatch results are preserved.
- AI Engine's `mwai_mcp_stream_max_time` filter caps fallback token-URL streams
  at five seconds. This limits occupancy; it is not a concurrency limit.
- AI Engine is not required to load this plugin. Without it, the filter is idle.

## Installation and updates

Copy `coda-mcp-worker-guard.php` directly into `WPMU_PLUGIN_DIR` (normally
`wp-content/mu-plugins/`). WordPress loads it automatically; it appears under
**Plugins → Must-Use**. No activation click is needed, and changing/deleting the
Haunted Tech theme does not remove the installed copy.

The repository's deployment installs this file atomically before syncing the
theme, using the CLI-only `scripts/install-mcp-worker-guard.php`. It reads the
configured WordPress MU directory and replaces only its own named file, never
deleting or synchronizing other must-use plugins. Future updates are deployed
from this repository; WordPress does not auto-update this site-specific plugin.

To intentionally disable the protection, remove only this file from the MU
directory and remove its installation step from deployment first, or deployment
will restore it. Plan adequate PHP capacity before enabling long-lived streams.

## Validation

`php tests/mcp-worker-guard.php` loads the plugin alone, without a theme, and
checks route/method isolation, response preservation, headers, and timeout cap.
The deployment's WordPress integration check verifies it loaded before theme
setup, anonymous access is denied, authenticated GET returns 405, and MCP POST
initialization succeeds. Public page smoke checks follow.
