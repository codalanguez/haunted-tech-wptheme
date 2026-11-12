# MechApe page timeout investigation — 2026-11-12 UTC

Status: recovered and verified on November 11, 2026 (America/Denver). Historical diagnostic findings follow the resolution below.


## Resolution

10Web's PHP-FPM log identified the effective live pool as `wplive`, repeatedly reaching `max_children = 3`. The earlier readable `max_children = 1` file was not the live pool configuration. The web-server log showed three concurrent AI Engine MCP GET listening streams active when PHP restarted. The installed-version source documents that each idle stream can occupy a worker for 180 seconds.

Restarting PHP alone did not restore sustained service. An attempted AI Engine deactivation through 10Web failed; the plugin remains active. The deployed fix in `inc/mcp-worker-guard.php` runs after REST permission checks, returns 405 for the optional standalone GET stream at `/mcp/v1/http`, and limits fallback token-URL streams to five seconds using AI Engine's documented filter. POST requests and session cleanup remain available. After deploying the corrected callback guard and restarting PHP, the page recovered.

The initial pre-dispatch approach was superseded by the final after-permission callback guard. The final deployment validation confirms anonymous access is denied, authenticated GET returns 405, and authenticated POST initialization returns 200 with a protocol result. No credentials are printed. The administrative test identity exists only within the CLI validation process.

### Verification

- Public MechApe request recovered to HTTP 200 in 1.00 seconds, with repeat requests in approximately 0.28–0.42 seconds. Login also returned 200 in 0.33 seconds.
- Live browser checked at desktop width 1920 and mobile viewport 390 × 844. Mobile-size emulation, not a physical phone test.
- All seven updated screenshot files loaded in-page; the Obsidian screenshot also opened successfully at full size (1024 × 800).
- Desktop 2.0.1 / Obsidian 0.1.10 content, $29 purchase links, cloud API charge notice, and purchase FAQ verified. Main content and screenshot controls fit the mobile viewport. Off-canvas navigation and the full-bleed footer link extend outside the content bounds by design.
- PHP lint, worker-guard validation, reader-funnel smoke check, homepage smoke check, and required MechApe release-page smoke check all passed: https://github.com/codalanguez/haunted-tech-wptheme/actions/runs/43167125047
- Existing unrelated `inc/render-callbacks.php` edits were preserved and excluded from commits.

This protects the current theme's small shared PHP pool; changing themes removes the guard. If standalone MCP push notifications are needed later, use a separate service/pool or reassess hosting capacity before removing the protection. No paid hosting upgrade was made.

## Historical investigation
## Verified before repair

- WordPress CLI bootstrap completes in 0.60–0.63 seconds.
- MechApe page is published and contains the new release-updates content.
- All seven Desktop 2.0.1 / Obsidian 0.1.10 screenshot files return HTTP 200. Local public downloads took 0.23–0.60 seconds.
- A public /mechape/ request returned no bytes even after 65 seconds.
- Requests to /mechape/, /wp-json/, and /wp-login.php return no bytes within 12 seconds from the hosting server itself. The same page request times out from the author's computer.
- nginx and PHP 8.3 FPM report running. Four PHP FPM processes were visible; this alone does not establish capacity exhaustion.
- Available log tails did not expose a relevant fatal error or upstream timeout. Production error logs may not be readable by the deployment account.
- No hosting settings, plugin activation settings, permissions, or services were changed by the diagnostic.

## Next hosting investigation

Ask 10Web to inspect the site's nginx upstream and PHP-FPM pool/queue, current slow requests, and production error/slow logs. WordPress CLI and static serving work, but HTTP routes invoking PHP stall. Determine the blocked request or service before choosing a restart or configuration change.

After service recovery, verify /mechape/ returns HTTP 200 promptly; confirm Desktop 2.0.1, Obsidian 0.1.10, the $29 purchase link, separate cloud API charges, and all screenshots on desktop and narrow mobile layouts.

## Evidence

- https://github.com/codalanguez/haunted-tech-wptheme/actions/runs/43164617145
- https://github.com/codalanguez/haunted-tech-wptheme/actions/runs/43164771606

The temporary SSH diagnostic workflow and script were removed after the investigation. Existing unrelated edits to inc/render-callbacks.php are excluded from these commits.

Final follow-up: the readable PHP 8.3 pool configuration reports `pm = ondemand` and `pm.max_children = 1`. This is a capacity concern, but the diagnostic did not establish whether that file controls the live site's pool or prove that worker exhaustion caused the outage. Ask hosting support to verify the effective pool configuration. The host's own /wp-login.php request also timed out after 65 seconds without a first byte. Final diagnostic: https://github.com/codalanguez/haunted-tech-wptheme/actions/runs/43164946966
