# Admin HTML write policy

Task `51270fae`, implemented locally on 2026-09-24, corrected on 2026-10-21
after the October 20 verification (Helpdesk templates, maintenance message length,
menu labels), on 2026-10-23 after the October 22 verification (menu label
length, Helpdesk active-content rules) and the owner's decisions of the same day
(links allowed, Helpdesk subject and template editing kept as they are), and on
2026-10-24 after the October 24 verification and the owner's decisions of that
day (pictures from a file, YouTube videos and img `alt`/`width`/`height` allowed;
oversized HTML rejected; Latte comments and tags no longer hide Helpdesk active
content), and on 2026-11-05 after the second October 24 verification (Helpdesk
rules read the template with Latte's tokenizer and see through tags that may
drop text; characters a column can't store no longer fail; empty default `alt`;
no definition cache), and on 2026-11-06 after the November 6 verification and the
owner's direction of that day (the Helpdesk guard stays best effort, with narrower
claims; `{syntax` refused in the subject; previews of stored HTML decode references).
Awaiting verification and release `c8e1d96d`; no production data was rewritten.

## Policy and writer inventory

`App\Security\HtmlContent` centralizes the page editor/API/inline-snippet
HTMLPurifier configuration: allowed attributes are `img.src`, `img.alt`,
`img.width`, `img.height`, `a.href`, `iframe.src`, `iframe.width`,
`iframe.height`, `iframe.frameborder`, `*.style` and `*.class`; URLs must be
relative or use `http`, `https`, `mailto` or `tel`, and an img `src` may also be
a `data:` picture (see below); the existing class allowlist is retained; `font`
is forbidden and empty elements are removed. Scripts, event attributes, other
URL schemes (`javascript:`, `vbscript:`, `ftp:`, `data:` anywhere but an img
`src`) and iframes other than YouTube embeds are removed; a link with such a
scheme keeps its text and loses its `href`. Other link attributes such as
`target` and `rel` are still dropped, and so is icon markup in menu labels (not
part of the October 24 decision). Supported formatting remains HTML; templates
keep their intentional `noescape` output.

### Pictures from a file and YouTube videos (October 24)

Before this task the admin snippet form and link descriptions stored raw HTML,
so a picture inserted from a file (Summernote stores it inline as
`<img src="data:image/…;base64,…">`), a YouTube video (`<iframe>`) and img
`alt`/`width`/`height` survived a save. Purifying those fields removed them
silently on the next save. The owner decided on October 24 that they must
survive again. The policy is shared, so the page editor, the content API and
frontend inline editing gain the same; that is intended.

- **Pictures.** `data:` is accepted only as an img `src`; a small URI filter in
  `HtmlContent::createPurifier()` refuses it in a link `href`, a CSS `url()`,
  an iframe or any other attribute. HTMLPurifier 4.19's `data:` scheme then
  decodes the payload and checks the bytes (`exif_imagetype`, else
  `getimagesize`): only PNG, JPEG and GIF pass, a mislabelled one of these is
  relabelled, and SVG, `text/html` or anything else drops the picture.
  **WebP is not accepted**: HTMLPurifier does not validate it, the CMS's own
  image uploads (`IO::IMAGE_MIME_TYPES`) don't accept it either, and allowing it
  would mean replacing HTMLPurifier's global `data:` scheme. A WebP (or BMP)
  picture inserted from a file is therefore still removed on save; upload it
  through the File Manager and insert it from there instead.
- **Videos.** `HTML.SafeIframe` with `URI.SafeIframeRegexp`
  `%^(?:https?:)?//www\.youtube(?:-nocookie)?\.com/embed/[\w-]+(?:\?[\w=&.-]*)?$%`:
  `www.youtube.com/embed/<id>` and `www.youtube-nocookie.com/embed/<id>` over
  http, https or protocol-relative (`//www.youtube.com/embed/<id>`, as Summernote
  0.8.11 inserts it), optionally with a query such as `?start=42`. `src`,
  `width`, `height` and `frameborder` are kept; Summernote's `note-video-clip`
  class is dropped. `allowfullscreen` is not kept: HTMLPurifier offers it only
  with `HTML.Trusted`, which this policy must not enable, and Summernote does
  not insert it for YouTube. Any other iframe (another host, a watch URL, a
  path that leaves `/embed/`, `javascript:` or `data:`) loses its `src` and
  `AutoFormat.RemoveEmpty` then removes it; `srcdoc` and event attributes are
  never kept.
- **Attributes.** img `alt`, `width` and `height` are kept. An image without
  `alt` gets an empty one (November 5). HTMLPurifier's default was the last path
  segment of its `src`: a file name for an ordinary image, but for a picture
  from a file the tail of its base64 text (191 characters in a small picture,
  about 3.5 KB in a larger one), which also used up the column. New images
  without `alt` therefore no longer get their file name as `alt` either; stored
  ones are not rewritten until they are saved again.
- **Characters a column can't store (November 5).** The purifier decodes entities,
  so `&#x1F600;` reached the utf8mb3 columns as a 4-byte character and `&#269;`
  (`č`) reached the latin1 `links.description` as a character it can't hold: a
  500 in strict mode (error 1366), and cut text otherwise. The same happened when
  they were typed. `HtmlContent::purify()` now writes 4-byte characters as numeric
  references (`&#128512;`), and `HtmlContent::latin1Html()` does so for anything
  outside ISO-8859-1 in `links.description`, so Czech text in a link description,
  which failed before this task too, now saves. In HTML output a reference shows the
  character, the rich-text editors load it back as the character, saving again changes
  nothing, and the size check counts the stored form (`č` takes 6 bytes in a link
  description). Text output doesn't decode it, so previews of stored HTML do that
  themselves (see below). Plain-text fields can't use this: an inline page title or an
  ordinary setting with an emoji stays a 400 or a form error. Other plain-text fields
  still fail with a 500, such as a plain menu label with an emoji or a Czech name in the
  profile (`users.name` is latin1); that is pre-existing and tracked in `455993c6`.
- **The maintenance message is the exception.** It is purified, so an emoji is stored
  as `&#128512;`, but its input on `/admin/settings/global/` is a plain text box, which
  shows `&#128512;` as stored. Saving it again changes nothing, and the public
  maintenance page echoes it as HTML, so a browser shows the character there. Left
  as it is: only someone who types an emoji into that one field sees it.
- **Previews of stored HTML (November 6).** The blog list and `BlogPreviewControl` (no
  template renders it at present) print a shortened text version of the stored HTML. `|striptags`
  keeps entities, and `|truncate` then turns its safe HTML back into a string, so
  output escaping ran a second time: `/blog/` showed `&#128512;` and `&amp;` as text.
  They now use Latte's `|stripHtml|truncate:n`, which strips tags and decodes once, cuts
  by characters so no half of a reference remains, and leaves text that the output
  escapes. A decoded `&lt;script&gt;` therefore stays visible text. RSS adds `|escapeHtml`,
  because readers treat a `description` as HTML: after the XML layer it holds
  `&lt;script&gt;`, and without that filter it would hold `<script>`. In the RSS source
  an `&` appears as `&amp;amp;`, which is correct. The search results print `|striptags`
  with no `|truncate`, so they stay HTML and were never affected.
- **Size.** `snippets.content`, `pages.document`, `pages.preview` and
  `links.description` are `TEXT` (65,535 bytes; `links.description` is latin1).
  A picture inserted from a file easily exceeds that, and strict MariaDB then
  fails with error 1406 (a 500). `HtmlContent::textSizeError()` checks the
  purified HTML before anything is written: the snippet form, link edit form
  and page editor show a form error naming the size ("Obsah má po úpravě
  80 149 bajtů, uložit lze nejvýše 65 535 bajtů. … Nic nebylo uloženo."), keep
  the submitted text in the editor and write nothing; the content API answers
  400 (`Field document is too long: … bytes after sanitization, at most
  65535`); frontend inline editing answers 400 (`Snippet is too long.`). The
  schema is unchanged. Changing these columns to `MEDIUMTEXT` (16 MB) would let
  larger pictures through; that is a proposal for the owner, not part of this
  task, and keeping pictures in the File Manager is the better practice anyway.

Helpdesk templates are not affected: they keep rejecting `data:` and iframes
(see below). The Helpdesk archive is purified with the content policy, so a
template that prints visitor input with `noescape` can now archive a YouTube
embed or a valid `data:` picture; the seeded templates escape visitor input.

`a.href` was added on October 23 (owner decision). Before this task only the
page editor, the content API and frontend inline editing purified, and they
dropped every `href`. The admin snippet form, link descriptions, the
maintenance message and menu labels stored raw HTML, so their links worked;
purifying them would have removed those links on the next save. One shared
policy keeps links in all writers, now including the page editor, the API and
inline editing. HTMLPurifier's default schemes also allowed `ftp`, `nntp` and
`news`; they are no longer accepted for links or images.

| Stored field / sink | First-party writers | Result |
| --- | --- | --- |
| Page document/preview HTML | Pages EditorControl; ContentApiService | Existing purification now uses the shared factory; over 65,535 bytes after purification a form error or API 400, nothing written |
| Snippet content/default and selected locale | Snippets EditFormControl; InlineEditing | Shared purification before update; unedited locales unchanged; over 65,535 bytes a form error or 400, nothing written |
| Link description, public Links template | LinksPresenter edit form | Purified before update, characters outside latin1 stored as numeric references; over 65,535 bytes a form error, nothing written |
| Menu title/default and localized navbar labels | InsertMenuControl; EditMenuControl; group-created root in InsertMenuMenusControl; MenuEditorControl AJAX rename; legacy Menu::setCategory helper | Plain labels stored as typed; labels with a complete tag purified; the stored label must fit `varchar(80)`, else an error and no write; navbar escapes plain labels on output |
| Maintenance message, frontend error page HTML | Settings EditSettingsControl | Only `maintenance_message` is purified; every value must fit `varchar(120)` after that, else a form error and no write |
| Helpdesk subject embedded in mail `<title>` | EditMailTemplateControl | Stored as typed; HTML-escaped when spliced into the mail frame; plain Latte variables still run; `{syntax` and tags with quotes or `&&` are rejected at save |
| Helpdesk body (Latte source) embedded in mail HTML | EditMailTemplateControl | Not purified (trusted Latte source); entities inside Latte tags decoded; direct active markup or a non-compiling template rejected with a form error |
| Helpdesk message HTML shown in admin history | Helpdesk::log | Rendered archive purified after template/parameter expansion |

### Menu labels

A label is markup only if it contains a complete tag (`<b>…`, `</x>`, `<!--`).
Everything else is plain text and is stored exactly as typed: `A&B "q" ž`,
`I <3 CMS`, `x<y z` and Czech text round-trip in the admin tree, the edit form
and every escaped menu view. Markup labels go through the shared purifier on
write, as before.

The navbar prints labels with `noescape`, so it now passes them through the
`menuLabel` filter (`HtmlContent::menuLabelHtml`). Plain labels are escaped
there, keeping any existing entity such as `&amp;` or `&nbsp;` so older labels
render as before. A partial tag such as `x<img src=x onerror=…` is plain text and
is escaped, never executed. Markup labels are printed as stored: labels written
since this task were purified on save; older rows are not rewritten.

The jsTree client sends a renamed node's text as escaped HTML
(`text=A&amp;B "q" ž`). The rename handler decodes it before storing, so a tree
rename and the edit form store the same value. Until October 21 such renames
were stored with literal entities, and plain labels containing `<` were
entity-encoded or truncated (`x<y z` became `x`).

`menu.title` and every `title_xx` column are `varchar(80)`. Purifying a markup
label turns each `&` into `&amp;`, so a typed label that fits can outgrow the
column (78 typed characters became 158). Every writer checks the stored form
with `HtmlContent::menuLabelError()` before writing anything:

- the edit form shows a form error and keeps the typed value;
- both insert forms are rendered by hand (one in a modal) and report with a
  flash message, as their existing duplicate and empty-name checks do. The
  group insert checks the root label before inserting the group, and also the
  group name itself against `menu_menus.title` `varchar(30)`, so it can't
  leave a group without its root;
- the jsTree rename answers `422` with a JSON error. The tree client shows its
  existing failure alert and the stored title is unchanged (the tree shows the
  typed name until a reload);
- the unused legacy `Menu::setCategory` throws `Nette\InvalidArgumentException`.

Until October 23 such a label was an HTTP 500 (MariaDB strict mode, error 1406).
Plain labels with 4-byte characters (emoji) still fail on the utf8mb3 schema; that
is pre-existing and tracked in `455993c6`.

Purified labels share the content policy, so a menu label can also hold a YouTube
iframe or a picture from a file, which then renders inside the navbar link. Only
someone with the menu permission can enter one, and only YouTube embeds pass, so
this is a layout choice and not a safety issue. Whether labels should be limited
to inline text is an open idea (`f22b50a0`).

### Helpdesk templates

Helpdesk templates are trusted Latte source, not content. Purifying them broke
them: `{if $a && $b}` became `&amp;&amp;`, `>` became `&gt;` (a ParseError at
send time), and `href="{$url}"` links were removed, although the seeded password
templates need them (a Latte placeholder is not a URL the purifier keeps). The
content policy therefore does not apply to template bodies. Instead, on save:

- the subject is stored as typed and HTML-escaped only where `Helpdesk` splices
  it into the frame, so `Tom & Jerry <3` round-trips in the form and reaches the
  mail header unchanged. It is still part of the Latte document, so a plain
  variable such as `Poptávka od {$name}` expands. The escaping runs before
  Latte compiles, so a tag containing quotes or `&`
  (`{$settings['site:title']}`, `{if $a && $b}`) and stray braces such as
  `Price {EUR}` fail the compile check and are a form error; `Price { EUR }`
  is literal text. Before this task such tags compiled; no seeded subject uses
  one. The owner decided on October 23 to keep this behaviour for now. One exception,
  since November 6: `{syntax` is a form error, because the subject is compiled with the
  body. Subject `{syntax double}` with body `on{{* c *}}error=` passed the body's rules
  and rendered `onerror=`. `Cena { syntax }` is still literal text;
- HTML entities inside Latte tags and `n:` attribute values are decoded. The
  admin editor is Summernote: a subject-only save submits the raw textarea, but
  any edit in the editor re-serializes `&&` as `&amp;&amp;`;
- the body is rejected with a form error naming the matched fragment if it
  contains active content (`Helpdesk::activeContent`, see below);
- the composed document (frame + escaped subject + body) must compile with Latte
  into valid PHP, so a broken `{if}` is a form error, not a failed send.

The active-content rules are context-free: they also match in text, comments
and attribute values. They can over-reject (the error names the fragment, so it
can be edited), and HTML structure such as a `>` inside a quoted value, a
comment or a `textarea` cannot hide a match. Latte constructs are a different
matter: Latte removes `{* comments *}`, replaces each tag with its output at
render time and can drop the text inside a control tag, so a name or scheme can
be assembled at render time. The rules therefore run on the template as Latte's
own tokenizer reads it (`Latte\Parser`), so a string that looks like a comment
(`{='{*'}<script>…{='*}'}`) can't hide markup; a body it can't read is a form
error. Comments are gone, `{l}` and `{r}` are braces, a print tag (`{$x}`) may
print nothing, and a control tag (`{if}`, `{else}`, `{/if}`, …) may also drop the
text up to the next one. Each word below still matches across those gaps:
`on{* c *}error=`, `on{if 1}error{/if}=`, `on{if 0}-{/if}error=`,
`{if 0}-{/if}onerror=`, `o{if 1}n{/if}{if 0}-{/if}error=`, `<scr{if 0}-{/if}ipt>`
and `java{if 0}-{/if}script:` are all rejected. Tags in values and around whole
attributes are not affected: `class="btn{if $x}-primary{/if}"`,
`{if $x}title="y"{/if}` and `href="{if $url}{$url}{else}/{/if}"` still save.
Rejected:

- `script`, `iframe`, `frame`, `frameset`, `object`, `embed`, `applet`, `base`,
  `form`, `meta`, `link`, `svg` and `math` tags;
- `on…=` after any character that can start an attribute name: whitespace, `/`
  or a quote (`<svg/onload=`, `<img src="x"onerror=`), after `[`, `(`, `,`
  or `}` for `n:attr` keys (`n:attr="onclick => …"`) and after any Latte tag
  (`{if $x}onclick=`), also when a tag supplies the rest of the name (`on{$x}=`);
- an attribute or `n:attr` value (after `=` or `=>`) whose URL starts with
  `javascript:`, `vbscript:` or `data:`. Entities are decoded first, including
  numeric references without `;` (`java&#9script:`), and tab, CR and LF are
  removed, as browsers do in URLs;
- a Latte tag inside a character reference (`java&#11{if 0}-{/if}5;cript:`), which
  would hide the reference from that decoding;
- an `n:attr` that is not a plain list of literal names (`title => …`,
  `'title' => …`): a computed name (`'on'.'click' => …`, `$key => …`), an
  array variable (`n:attr="$attributes"`), a spread (`...$a`, `(expand) $a`) or an
  array operator (`$a + […]`, `array_merge($a, …)`). No seeded template uses
  `n:attr`, and literal names are covered by the `on…` rule;
- `n:tag`, which names the element at render time (`<div n:tag="'iframe'" src=…>`
  renders an `iframe`; Latte itself only refuses `script` and `style`). `n:tag-if`
  stays allowed: it only keeps or removes the element;
- `{syntax …}` and `n:syntax`, since another tag syntax would change what the
  tokenizer sees as tags.

The guard covers direct markup under the default syntax. It is a best-effort guard
and not a boundary: the owner decided on November 6 to keep it as that, with no more
patterns and no sandbox, because more patterns won't close this class. Latte can
assemble markup at render time in many ways, so the known limits below are groups of
what it doesn't see, not a complete list. Each example is accepted at save and renders
active markup:

- `n:` attribute splitting: `<img src=x on n:if="1"error=alert(1)>` renders `onerror=`,
  and `<scr n:if="1"ipt src="…"></scr>` renders a `script` tag;
- control-flow reassembly: `{foreach [2,1] as $i}{if $i==1}rror=alert(1)>{else}<img
  src=x one{/if}{/foreach}`, `{define a}rror=alert(1)>{/define}<img src=x one{include
  a|noescape}` and `{block|replace:"x","onerror"}<img src=y x=alert(1)>{/block}`;
- string concatenation in `n:attr` values: `<a n:attr="href => 'java'.'script:alert(1)'">`;
- what a Latte expression prints at render time (`{='on'}click=`, `<{$tag}>`, a value
  a variable holds), `{include}` of a string, and a scheme assembled from variables.

Template editors can run any Latte expression on the server anyway, so none of this
adds a privilege: the helpdesk permission remains a trusted role. The
owner decided on October 23 that roles with the `helpdesk` permission keep
editing templates (not the superadmin only) and that templates are not
sandboxed (Latte `SecurityPolicy`) for now, since that would be a new feature.
Grant the permission only to people trusted with server-side code: a template
can call PHP functions. Until October 23 the rules needed
whitespace before `on`, ignored `meta`/`link`/`svg`/`math`, and missed tab or
newline inside a scheme. The admin history archive stays purified after
rendering.

Creation paths that accept no arbitrary HTML remain unchanged: new snippets
accept only a keyword; new links and tree nodes use fixed/default content.
The menu insertion check covers active translated titles and real MariaDB
ordering. Editing/renaming only changes fields the existing form supports.

Other text fields (page/contact/carousel titles, descriptions rendered with
normal Latte escaping, menu group names, ordinary settings) retain their text
semantics. Standalone URL fields retain their existing rendering/validation;
this task does not introduce a general URL policy. Custom theme templates and
direct SQL/import writers remain responsible for their own output contexts.

## Compatibility boundaries

- No bulk cleanup or read-time rewrite of existing rows. Editing a covered HTML
  field saves normalized HTML under the same policy as the page editor, so markup
  the policy doesn't keep (maps, Vimeo or Facebook iframes, `video`, WebP pictures)
  goes on the next save of that row. Task `1260c65e` counts such rows on production
  before the release, read-only.
- Helpdesk templates remain trusted Latte source (see above); nothing sandboxes
  template macros. The DB-owned full email frame
  (`helpdesk_templates.document`) has no first-party admin writer and is not
  rewritten; outgoing mail keeps that frame. Only the browser archive is
  sanitized after rendering, so its appearance can differ from the sent email.
  Since October 23 archived links stay clickable. A template that archives
  (`log` = 1) a password reset link therefore keeps it usable for whoever reads
  the history; no seeded template archives one.
- The global settings form validates every submitted value before writing any
  and saves them in one transaction. A value longer than 120 characters after
  purification (`&` becomes `&amp;`), or a plain-text one with a 4-byte character
  such as an emoji (the schema is utf8mb3), is a form error naming the setting; the
  typed values stay in the form. The purified maintenance message stores the
  character as a numeric reference instead. Until October 21 this was an HTTP 500
  after a possible partial save.
- Existing CSRF/permission gates and frontend inline guards remain in force.
  Helpdesk failure diagnostics belong to task `26a2cd83`.
- Deploy the new class with its callers and rebuild Nette/RobotLoader caches.
  The local `utilities:cache-clear` command cleared Latte only and did not make
  the new class visible to production-mode HTTP. Rebuilding the RobotLoader
  cache resolved that reproduced failure. The release hook's full
  `www/temp/cache` cleanup must run; clearing only Latte is insufficient.
- The shared purifier uses no definition cache (`Cache.DefinitionImpl` null, since
  November 5). HTMLPurifier's default cache lives under
  `vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer`.
  Where the web user can't write there, every purifying request wrote 4–9
  `Directory … not writable` warnings to `error.log` (measured by the October 24
  verification as www-data on PHP 8.2), and each policy change needed new entries.
  Purifying only happens on writes, so rebuilding the definition each time (about
  10 ms) costs nothing noticeable. The release step in `c8e1d96d` that made that
  directory writable is no longer needed.
- HTMLPurifier checks a `data:` picture through a temporary file in
  `sys_get_temp_dir()`. Where `open_basedir` excludes that directory, the check
  fails and the picture is dropped without an error. Release `c8e1d96d` checks this
  on the web runtime.

## Verification

November 6 (current): see the task `51270fae` result note for the counts on
PHP 7.4.33, 8.0.30 and 8.2.33 and the browser checks. Added coverage:

- `AdminHtmlWritesTest.phpt`: `{syntax` in a subject is a form error with nothing
  written, while the seeded subjects and text that only mentions the word save (the same
  body alone passes the rules, which is the negative control); the real preview chain
  of each template, rendered by Latte: a reference, `&amp;` and a decoded `&lt;script&gt;`
  in the blog list and in RSS, truncation with no half of a reference, and the old chain
  escaping the reference twice as the negative control;
- `admin-html-http.py` (13 PASS lines): the real Helpdesk form refuses the subject and
  saves the seeded subjects; a post saved through the real page editor shows correctly
  on `/blog/` and in `/rss.xml`, which is on only for that check and restored by cleanup.

November 5: see the task `51270fae` notes of that day. Added coverage:

- `AdminHtmlWritesTest.phpt`: tags that drop text inside names, schemes and
  character references, strings that look like comments, an
  unterminated tag, `(expand)`, array operators and `n:tag` are rejected; tags in
  values, around whole attributes and in query strings stay accepted. Numeric
  references replace 4-byte characters on every purified writer and anything
  outside latin1 in link descriptions; an image without `alt` gets an empty one;
- `InlineEditingTest.phpt`: a snippet stores an emoji as a numeric reference, typed
  or as an entity; a title with an emoji stays a 400;
- `admin-html-http.py` (11 PASS lines): the real snippet, link and page forms store
  those references with no 500 and keep them after saving the rendered form again;
  the real Helpdesk form rejects the bypasses of the October 24 verification.

October 24 (historical). Added coverage:

- `AdminHtmlWritesTest.phpt`: a `data:` PNG in img `src`, YouTube iframes
  (protocol-relative, https with `?start=`, youtube-nocookie over http) and
  img `alt`/`width`/`height` are kept, also through the snippet form and link
  descriptions; `data:text/html`, SVG and WebP pictures, `data:` in a link
  `href` (also `DATA:`) and in CSS `url()`, iframes from other hosts, watch
  URLs, `/embed/../`, `javascript:`/`data:` iframes, `srcdoc` and `onerror` are
  removed; an oversized picture is a form error naming its size (80,149 bytes)
  with nothing written, on the snippet form and the link edit form; every Latte
  bypass from the October 24 verification and the other computed `n:attr`
  forms are rejected, while `{* note *}`, `{l}`/`{r}`, `{ EUR }` and literal
  `n:attr` names are accepted;
- `ContentApiServiceTest.phpt`: `document` or `preview` over 65,535 bytes after
  purification is an `InvalidArgumentException` (API 400), nothing written;
- `InlineEditingTest.phpt`: a snippet that outgrows the column once purified is
  a 400, nothing written;
- `admin-html-http.py` (10 PASS lines): the real snippet, link and page editor
  forms keep the picture, video and attributes, remove the rest, and refuse an
  oversized picture with the size in the page and an unchanged snapshot; the
  real Helpdesk form rejects the Latte bypasses, a YouTube iframe and a `data:`
  picture. `csrf-fixture.php` now also creates a disposable page.

October 23 (historical): all 16 Tester files pass and 195 PHP/PHPT files lint
clean on PHP 7.4.33, 8.0.30 and 8.2.33; `latte-lint app` passes.
`admin-html-http.py`, `admin-csrf-http.py`, `admin-html-menu.php` and
`inline-editing-http.py` pass with disposable fixtures; no log entry was
added. These results were re-run after the owner decisions, which added:

- `HtmlContent::purify` keeps `https`, `mailto`, `tel` and relative links and
  drops `javascript:` (also entity-encoded or with a leading space),
  `vbscript:`, `data:` (since October 24 only outside img `src`), `ftp:`,
  `target` and `onclick`;
- the snippet form and link descriptions store links unchanged, in
  `AdminHtmlWritesTest.phpt` and through the real forms in `admin-html-http.py`
  (a `javascript:` link in the same submission loses its `href`). That round
  printed 9 PASS lines, not the 10 the October 23 note claimed;
- the subject cases above are pinned in `AdminHtmlWritesTest.phpt`.

Earlier October 23 checks:

- a 78-character markup label (158 after purification) is a form error on the
  menu edit page, a flash message on both insert forms (`158` and `118
  znaků`) and `422` on the tree rename; the fixture snapshot is unchanged and
  no exception is logged;
- Helpdesk bodies `<svg/onload=…>`, `<img src="x"onerror=…>` and
  `<a href="java&#9;script:…">` show the form error with nothing stored.

`AdminHtmlWritesTest.phpt` adds every bypass reported on October 22 (and a
`>` inside a quoted value, comments and `textarea` hiding a tag), accepted
templates whose text only resembles a rule (`online`, `Data:`, `class =>
'online'`), and the length check on each menu writer. Seeded templates 1, 2
and 4–6 in the local database still pass `templateError`. No browser was used
for these paths; the tree's failure alert is the existing generic one.

October 21 (historical): the same suites passed. In Chrome on the local stack:

- a subject-only save of a template containing `{if $phone && $email}` keeps the
  raw source, and so does an edit inside Summernote; both still render with the
  fake mailer (`tests/manual/helpdesk-send-fake.php`), links included;
- the subject `Tom & Jerry <3` round-trips and reaches the mail header;
- a template with an `onerror` attribute shows the form error and is not stored;
- a 118-character maintenance message containing `&` shows a form error (126
  characters after purification) with no setting changed and no 500;
- jsTree renames to `A&B "q" ž` and `I <3 CMS` (sent as `A&amp;B…` and
  `I &lt;3 CMS`) are stored as typed and display correctly after reload in the
  tree, the edit input and the public navbar; `x<y z` survives;
  `<b>Bold</b> & co<img … onerror=…>` is stored without `onerror`.

Historical September 24 results (superseded): 13 Tester files and 189 files
lint. The "seven valid form saves" then passed only because
`admin-csrf-http.py` HTML-unescaped the stored snippet and Helpdesk values
before comparing; that hid the template and subject encoding. The harness now
compares exactly: snippet content is posted as HTML, every other field as text.

`AdminHtmlWritesTest.phpt` exercises actual callbacks with SQLite, default and
localized snippet updates, insert/edit behavior, maintenance settings (including
the 118-character and emoji rejections with no partial save), link
descriptions, safe formatting, and plain menu labels. A fake mailer tests real
Helpdesk template expansion: Latte logic, `n:if` and `href` placeholders after a
subject-only and a Summernote-escaped save, the plain subject in the header,
rejection of active content and broken Latte, and post-render archive
sanitization including a trusted `noescape` placeholder. No mail leaves the test
process.

The local HTTP check submits script/event/unsafe-URL payloads through real
snippet, link and menu forms plus menu AJAX rename (raw and jsTree-escaped).
It also covers the Helpdesk template (active content rejected, logic and links
kept, subject-only save, fake send against the real row) and the 118-character
maintenance message. Bad tokens/roles must return 403 with an unchanged
snapshot. Fixture data/accounts are disposable.

```powershell
$prefix = 'permtest-' + [guid]::NewGuid().ToString('N').Substring(0,8)
try {
    docker compose exec -T app php tests/manual/admin-permissions-fixture.php create $prefix
    python tests/manual/admin-html-http.py $prefix
    docker compose exec -T app php tests/manual/admin-permissions-fixture.php check $prefix
} finally {
    docker compose exec -T app php tests/manual/admin-permissions-fixture.php cleanup $prefix
}
docker compose exec -T app php tests/manual/admin-html-menu.php
docker compose exec -T app composer test
```

`admin-html-menu.php` uses connection-local MariaDB temporary tables, including
an active English language; no real application menu/language rows are changed.
These manual checks are for the local Docker stack at `localhost:8090` only.
Run the existing `admin-csrf-http.py` and `inline-editing-http.py` with the same
disposable account setup to check adjacent form/signal behavior. The CSRF test
compares stored values exactly, with no entity decoding.

Acceptance should include visual checks of safe formatting and links in
snippets, links, navbar labels, maintenance messages and Helpdesk history. Review sanitized
output before saving existing content whose markup exceeds the editor policy.
HTTP tests establish persistence and authorization, not visual acceptance.
