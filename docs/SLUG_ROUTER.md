# CMS slug query parameters

`SlugRouter::match()` returns flat Nette route parameters. Query values come from
Nette's parsed HTTP request instead of splitting on `&` and `=`. For example,
`?q=a%26b%3Dc%2Bd+e` becomes `q = "a&b=c+d e"`.

Query behavior follows PHP/Nette conventions:

- `?empty=&bare` gives two empty strings.
- Repeated scalar keys use the final value; `?tag=a&tag=b` gives `tag = b`.
- Bracket notation produces arrays, such as `?filters[]=one&filters[]=two`.
- CMS route identity (`presenter`, `module`, `action`, `page_id`, `slug`, `locale`,
  `prefix`, `method`) cannot be overridden by query input. An ID from the path
  also wins over a query ID.
- Query-string `do` is deliberately not forwarded. The old nested query result
  did not dispatch those signals, and legacy frontend write handlers have a
  separate outstanding security review. This does not claim to protect POST/AJAX
  frontend editing or replace its authorization/CSRF work.

Generated query strings use `http_build_query(..., PHP_QUERY_RFC3986)` so spaces,
ampersands, equals signs, plus signs and array keys are encoded correctly.
Internal presenter/method parameters are omitted. Null query values are omitted
according to PHP's normal builder behavior.

URLs retain the host, port and application base directory. Locale and CMS prefix
paths remain supported; prefix/slug takes precedence when a two-segment path is
ambiguous with slug/id. IDs use a single slash, including when `action` is omitted.
Non-default actions without an ID retain their trailing slash. Default Czech
links use the existing unsuffixed `slug` column.

Regression coverage: `tests/Routing/SlugRouterTest.phpt` uses the real router,
SlugManager and an isolated SQLite fixture. It checks query round trips, route
identity, locale/prefix/id paths, ports/subdirectories, template selection and
unknown pages. It does not replace a full production routing crawl.
