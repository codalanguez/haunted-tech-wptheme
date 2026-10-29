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
