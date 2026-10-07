# Woodland redesign verification

Implemented in the existing Laravel 12 / Filament 4 project. No database migrations, authentication changes, publishing-rule changes or new runtime dependencies were introduced.

## Visual and interaction checks

Chromium browser checks used a 1440 × 1000 desktop viewport and a 390 × 844 mobile touch viewport. Captured and inspected screenshots of the opening, a selected branch, the mobile opening, selected work below the tree, Work, Blogs & Guides and an article.

Verified in a live browser:

- Centered title and navigation below it; all six destinations preserved.
- Loaded original transparent tree artwork and licensed local title font.
- Pointer motion changes the main-tree transform independently of the background.
- Scrolling changes mist and forest transforms; scrolling back restores the corresponding framing. No pinned scrolling or movement of reading content.
- Keyboard focus highlights the corresponding branch and shows its explanation. Enter opens a preview; Escape closes it and restores focus. Project markers retain ordinary destination links.
- Mobile menu opens/closes; touch opens branch previews and the complete index. No pointer parallax is required on touch.
- Reduced-motion emulation removes transforms and continuous animation while preserving previews and links.
- Leaving the scene pauses ambient motion. A browser visibility-change handler check pauses ambient animation when `document.hidden` is true. This handler was exercised through a simulated visibility event; OS-level background-tab throttling was not measured.
- No horizontal overflow on Home, Explore, Work, Blogs, Channels, Now, Contact, project or article pages at the tested widths.
- Navigation and the branch index work with JavaScript disabled.
- Article scenery remains static during scrolling; a solid cream surface supports 17 px desktop / 16 px mobile body text.
- No browser JavaScript errors or failed HTTP requests during verification.

Automated axe checks of the desktop and mobile opening reported no WCAG 2 A/AA or WCAG 2.1 AA violations. These checks supplement keyboard/touch testing and do not constitute a full accessibility certification.

## Application and build checks

- Vite production build passed. Existing JavaScript bundle is about 56 KB before gzip, including the existing Axios dependency; the redesign adds no framework or WebGL runtime.
- 28 Laravel feature tests passed with 179 assertions.
- Filament integration tests cover creating a branch, uploading and publishing a project, linking related articles/videos, changing gallery order and retaining collection redirects.
- Discovery/access tests continue to enforce private, draft, unlisted and public behavior, including protected media.
- New regression tests cover additional branch groups, smaller-branch browsing, stable marker positions after a rename, project connections and the explicit list alternative.
- Pint and Git whitespace checks passed.

## Windows delivery and limitations

The update package copies an explicit, checksummed allowlist of code/artwork files. It contains no `.env`, SQLite database, uploads, dependencies or generated frontend build. The PowerShell updater rejects protected paths and reparse points, backs up replaced code and verifies the existing `.env` and standard SQLite file hashes after copying.

The Docker workflow was previously verified with Linux containers and is unchanged by the redesign. Windows Docker Desktop and the PowerShell updater were not executed on an actual Windows machine here. The guide provides exact commands for the user's existing ZIP installation; no Git checkout is assumed. Preserve a private data backup before applying the update.

The underlying principal-tree painting is fixed; branches and connected content are live HTML/SVG overlays. Small content edits preserve their ordered positions. Reordering intentionally moves labels, and groups of eight prevent crowding. All content is also reachable through conventional collection pages.

No deployment was performed.
