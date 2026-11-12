# MechApe page timeout investigation — 2026-11-12 UTC

Status: unresolved hosting/request-path issue; live desktop/mobile acceptance is not complete.

## Verified

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
