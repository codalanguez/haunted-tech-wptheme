# Haunted Tech UI consistency

The shared contract lives in `assets/ui-consistency.css`, loaded after main, serial-funnel, and MechApe styles.

- Dialogs: square gold border, double outer frame, red ambient shadow, gold corner brackets. Decorative brackets must use `pointer-events: none`.
- Close controls: 48px square, gold at rest, red on hover, visible keyboard focus.
- Text actions: square borders and at least 44px height. Preserve filled primary actions, selected tabs, disabled states, and distinct component typography.
- Fields: square dark surfaces, readable bone text, gold focus outline.
- Small indicators, playback controls, and the illustrated CRT monitor retain intentional circular/curved shapes.
- Respect reduced-motion preferences. Do not add animation merely to make components match.

Reviewed surfaces: About; gallery lightbox; portrait lightbox; book and web-novel dialogs; all three commission dialogs; MechApe; gallery tabs and filters; service, retailer, download, inquiry, newsletter, and form actions; WordPress button/search blocks; transmission cards; Lab icon borders.

Validation used the saved public homepage markup in Electron at 1440px and 390px. Checked frame radius, corner pointer behavior, close-control size and viewport visibility. Dynamic book/web-novel content was not loaded during the style check. Existing theme deployment smoke checks cover the reader funnel and homepage story carousel.

For new components, use theme variables (`--gold`, `--red`, `--void`, `--bone`, `--border`) and existing button classes. Avoid broad resets of every border or every button; native controls and state indicators need their semantics preserved.

## Mobile audit — October 29, 2026

Checked 320×568, 390×844, 844×390, 1024×768, and 1280×900 in Electron with software rendering. Fixed clipped section headings, header actions/menu overflow, shrinking gallery controls, zero-height About biography and commission scroll areas, and gallery metadata clipped in landscape. Compact navigation now runs through 1280px with matching CSS and JavaScript breakpoints.

Reviewed rendered content from all 12 published WordPress Pages (10 non-empty page bodies; Home and Blog are template-driven), plus saved homepage markup and shared templates. Verified navigation expansion, commission form bottoms, About footer reachability, populated gallery detail, MechApe opening/closing and Full Tour, and viewport-visible close controls. Populated book/web-novel REST responses and every individual blog/chapter were not fetched: public page requests were rate-limited (HTTP 429). Their shared shell and responsive template rules were inspected. No form was submitted. These are responsive-browser checks, not a physical iOS/Android device test.

## Populated book/web-novel follow-up — October 29, 2026

Retrieved successful live `/haunted-tech/v1/book-modal/` and `/webnovel-modal/` responses for Drained, Letters Between Sex and Violence: Volume 3, Victoria, and Letters Between Sex and Violence. Served those unmodified responses to the actual theme popup-loading JavaScript in an isolated local browser, testing all four at 320×568, 390×844, 844×390, and 1024×768. Expanded chapter accordions and scrolled to the final related-story panels.

Fixed clipped terminal paths and crowded story recommendations inside the nested CRT panel: reduced internal mobile gutters, used shrinkable grid columns, wrapped names/paths, and constrained tooltips. All 16 loaded cases have matching body/scroll widths and no actionable elements outside the popup's content width. The intentionally oversized decorative `book-hero::before` background is clipped by design. This closes the prior gap for representative populated book/web-novel dialogs; it does not claim physical-device coverage or every title in the catalog.
