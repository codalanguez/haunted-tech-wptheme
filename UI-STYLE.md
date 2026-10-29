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
