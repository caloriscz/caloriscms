# Public theme conventions

The public frontend uses the shared Latte layout in `app/FrontModule/templates/@layout.latte` and the small site-specific stylesheet `www/css/caloris-site.css`. Keep generic CMS and Bootstrap styling in the existing shared assets; put only caloris.cz-specific presentation rules in `caloris-site.css`.

The layout assigns semantic variants from the page slug:

- the homepage has an empty slug and receives `caloris-layout--profile` plus `caloris-page--profile`;
- `privacy-policy` and slugs ending in `-privacy-policy` receive `caloris-layout--policy` plus `caloris-page--policy`;
- all other pages keep the normal CMS navigation, page container, and footer.

Profile and policy variants are standalone pages. Their navigation and footer are omitted by the Latte layout rather than hidden with CSS. This keeps the HTML and accessibility tree aligned with what users see and avoids relying on the `:has()` selector or an empty `page-` class.

The older `.caloris-profile` and `.caloris-privacy` content classes remain supported for existing CMS-authored markup. New policy pages only need the documented slug convention; their content does not need to duplicate layout CSS or inline styles.

Tracy remains disabled for browser requests and enabled for CLI diagnostics. elFinder debug output must remain disabled outside an explicitly local-only development configuration. Do not add `Debugger::barDump()` calls to public rendering paths.
