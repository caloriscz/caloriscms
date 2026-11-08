"""Disposable real forms: asset replacement, duplicate and path boundaries."""
import base64
import json
import pathlib
import re
import subprocess
import sys
from urllib.parse import parse_qs, urljoin, urlsplit
from http_helpers import forms, login

prefix = sys.argv[1]
assert re.fullmatch(r'permtest-[a-f0-9]{8}', prefix)
base = 'http://localhost:8090'
root = pathlib.Path(__file__).resolve().parents[2] / 'www'
png = base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aCr8AAAAASUVORK5CYII=')
owned = set()

def fixture(mode):
    result = subprocess.check_output(['docker', 'compose', 'exec', '-T', 'app', 'php', 'tests/manual/csrf-fixture.php', mode, prefix], text=True)
    return json.loads(result) if result else None

def sql(query, *params):
    # Fixture rows only. Values are limited to a safe character set before
    # being quoted into the statement (user variables clash on collation).
    for value in params:
        assert re.fullmatch(r'[A-Za-z0-9 ._-]+', value), value
    for i, value in enumerate(params):
        query = query.replace('@p%d' % i, "'%s'" % value)
    return subprocess.check_output(['docker', 'compose', 'exec', '-T', 'db', 'mysql', '-ucaloris', '-pcaloris', '-N', 'caloriscms',
        '-e', query], text=True, stderr=subprocess.DEVNULL).strip()

def flashes(response):
    return re.findall(r'<div class="flash error">([^<]*)</div>', session.get(urljoin(base, response.headers['Location']), timeout=15).text)

def form_at(route, signal, field=None, value=None):
    url = base + '/admin/' + route
    page = session.get(url, timeout=15)
    assert page.status_code == 200, (route, page.status_code)
    return url, next(f for f in forms(page.text) if f['inputs'].get('_do') == signal and (field is None or f['inputs'].get(field) == str(value)))

def submit(route, signal, field='the_file', data=None, files=None, selector=None):
    url, form = form_at(route, signal, *(selector or (None, None)))
    result = session.post(urljoin(url, form['action']), data={**form['inputs'], **(data or {})},
        files=files or {field: (prefix + '.png', png, 'image/png')}, allow_redirects=False, timeout=15)
    assert result.status_code in (200, 302, 303), (signal, result.status_code, result.text[:120])
    return form

try:
    ids = fixture('create')
    session = login(base, prefix + '-full')
    denied = login(base, prefix + '-denied')
    csrf = re.search(r'name="admin-csrf" content="([a-f0-9]{64})"', session.get(base + '/admin/', timeout=15).text)[1]
    path = root / 'images' / (prefix + '.png')
    assert not path.exists()
    owned.add(path)
    form = submit('files/', 'dropUploadFiles-dropUploadForm-submit', 'file')
    assert path.read_bytes() == png
    # Dropzone shows the JSON "error" string: readable text, not "true".
    duplicate = session.post(base + '/admin/files/', data=form['inputs'], files={'file': (prefix + '.png', png + b'changed', 'image/png')},
        headers={'X-Requested-With': 'XMLHttpRequest'}, timeout=15)
    assert duplicate.status_code == 409 and 'už existuje' in duplicate.json()['error'] and path.read_bytes() == png
    fake = session.post(base + '/admin/files/', data=form['inputs'], files={'file': (prefix + '-fake.png', b'not an image', 'image/png')},
        headers={'X-Requested-With': 'XMLHttpRequest'}, timeout=15)
    assert fake.status_code == 400 and 'Neplatný obrázek' in fake.json()['error'] and not (root / 'images' / (prefix + '-fake.png')).exists()
    marker = root / 'temp' / (prefix + '.txt')
    owned.add(marker)
    marker.write_text('must survive')
    bad = session.post(base + '/admin/files/', params={'do': 'delete', 'path': '../temp/' + marker.name}, data={'_csrf': csrf}, timeout=15)
    assert bad.status_code == 400 and marker.read_text() == 'must survive'
    # Legacy names (spaces) can be deleted; dotfiles cannot.
    legacy = root / 'images' / (prefix + ' legacy.png')
    dotfile = root / 'images' / ('.' + prefix)
    owned.update([legacy, dotfile])
    legacy.write_bytes(png)
    dotfile.write_text('must survive')
    deleted = session.post(base + '/admin/files/', params={'do': 'delete', 'path': legacy.name}, data={'_csrf': csrf}, allow_redirects=False, timeout=15)
    assert deleted.status_code in (302, 303) and not legacy.exists(), deleted.status_code
    bad = session.post(base + '/admin/files/', params={'do': 'delete', 'path': dotfile.name}, data={'_csrf': csrf}, timeout=15)
    assert bad.status_code == 400 and dotfile.read_text() == 'must survive'
    print('PASS: Dropzone duplicate 409 / fake image 400 with readable JSON; traversal and dotfile 400; legacy spaced name deleted', flush=True)

    # PHP 7.4's basename() drops leading non-ASCII letters in the C locale, so
    # 'Čáp-….jpg' used to post 'p-….jpg'. The rendered page (it needs exif) now
    # posts the full name, and only the chosen file goes.
    zlut = root / 'images' / ('Žluť-' + prefix + '.png')
    cap = root / 'images' / ('Čáp-' + prefix + '.jpg')
    p = root / 'images' / ('p-' + prefix + '.jpg')
    managed = root / 'images' / 'menu' / (prefix + '.png')
    owned.update([zlut, cap, p, managed])
    for file in (zlut, cap, p, managed):
        file.write_bytes(png)
    page = session.get(base + '/admin/files/', timeout=15)
    assert page.status_code == 200 and page.text.rstrip().endswith('</html>'), page.status_code
    targets = {parse_qs(urlsplit(f['action']).query)['path'][0]: f for f in forms(page.text) if 'do=delete' in f.get('action', '')}
    assert {zlut.name, cap.name, p.name} <= set(targets), sorted(targets)
    for file in (zlut, cap):
        deleted = session.post(urljoin(base + '/admin/files/', targets[file.name]['action']), data=targets[file.name]['inputs'],
            allow_redirects=False, timeout=15)
        assert deleted.status_code == 303 and not file.exists(), (file.name, deleted.status_code)
    assert p.read_bytes() == png
    # A separator (into a managed folder or out of images/) is still a 400.
    for bad_path in ['menu/' + managed.name, 'menu\\' + managed.name, '../temp/' + marker.name, '..', '']:
        bad = session.post(base + '/admin/files/', params={'do': 'delete', 'path': bad_path}, data={'_csrf': csrf}, timeout=15)
        assert bad.status_code == 400, (bad_path, bad.status_code)
    assert managed.read_bytes() == png and marker.read_text() == 'must survive'
    print('PASS: /admin/files/ renders; names starting with a non-ASCII letter delete from the rendered form (303) and only the chosen file goes;'
        ' separators, traversal and empty paths 400', flush=True)

    route = 'appearance/carousel-detail/' + str(ids['carousel']) + '/'
    # A row from before name sanitizing: replacing its image retires the legacy file.
    legacy = root / 'images/carousel' / (prefix + ' legacy.jpg')
    owned.add(legacy)
    legacy.write_bytes(png)
    sql('UPDATE carousel SET image = @p0 WHERE id = %d' % ids['carousel'], legacy.name)
    submit(route, 'editFormCarousel-editForm-submit')
    first = root / 'images/carousel' / fixture('read')['carousel']['image']
    owned.add(first)
    assert first.read_bytes() == png and not legacy.exists()
    # Invalid images give readable errors; the row and its file are unchanged.
    # Non-image bytes fail the form's MIME rule (form error, re-rendered);
    # a real PNG with a disallowed extension fails storage validation (flash).
    url, form = form_at(route, 'editFormCarousel-editForm-submit')
    result = session.post(urljoin(url, form['action']), data=form['inputs'], files={'the_file': (prefix + '.png', b'not an image', 'image/png')},
        allow_redirects=False, timeout=15)
    assert result.status_code == 200 and 'Neplatný obrázek' in result.text, result.status_code
    url, form = form_at(route, 'editFormCarousel-editForm-submit')
    result = session.post(urljoin(url, form['action']), data=form['inputs'], files={'the_file': (prefix + '.tif', png, 'image/png')},
        allow_redirects=False, timeout=15)
    assert result.status_code in (302, 303) and any('Neplatný obrázek' in f for f in flashes(result)), result.status_code
    assert root / 'images/carousel' / fixture('read')['carousel']['image'] == first and first.read_bytes() == png
    submit(route, 'editFormCarousel-editForm-submit')
    second = root / 'images/carousel' / fixture('read')['carousel']['image']
    owned.add(second)
    assert first != second and not first.exists() and second.read_bytes() == png
    url, form = form_at(route, 'editFormCarousel-editForm-submit')
    rejected = denied.post(urljoin(url, form['action']), data=form['inputs'], files={'the_file': (prefix + '.png', png, 'image/png')}, timeout=15)
    assert rejected.status_code == 403 and second.read_bytes() == png
    legacy = root / 'images/paths' / (prefix + ' logo.png')
    owned.add(legacy)
    legacy.write_bytes(png)
    sql('UPDATE settings SET setvalue = @p0 WHERE id = %d' % ids['settings'], legacy.name)
    submit('appearance/', 'savePaths-editForm-submit', 'path', selector=('path_id', ids['settings']))
    setting = root / 'images/paths' / fixture('read')['settings']['setvalue']
    owned.add(setting)
    assert setting.read_bytes() == png and not legacy.exists()
    print('PASS: carousel/appearance replacement retires prior (also legacy-named) files; invalid image flash; denied role rejected', flush=True)

    # Adding a slide: sorted is set (NOT NULL, no default) and a hex-named file stored.
    before = sql("SELECT COUNT(*) FROM carousel")
    url, form = form_at('appearance/carousel-detail/', 'insertFormCarousel-insertForm-submit')
    result = session.post(urljoin(url, form['action']), data={**form['inputs'], 'title': prefix + '-new'},
        allow_redirects=False, timeout=15)
    assert result.status_code in (302, 303) and any('Vyberte obrázek' in f for f in flashes(result))
    assert sql("SELECT COUNT(*) FROM carousel") == before
    submit('appearance/carousel-detail/', 'insertFormCarousel-insertForm-submit', data={'title': prefix + '-new'})
    row = sql('SELECT id, sorted, image FROM carousel WHERE title = @p0', prefix + '-new').split('\t')
    added = root / 'images/carousel' / row[2]
    owned.add(added)
    assert re.fullmatch(r'[0-9a-f]{24}\.png', row[2]) and row[1] != 'NULL' and added.read_bytes() == png, row
    sql('DELETE FROM carousel WHERE id = %d AND title = @p0' % int(row[0]), prefix + '-new')
    print('PASS: carousel insert without image is a readable flash with no row; with image adds a sorted row', flush=True)

    link = root / 'links-media' / ('link-' + str(ids['links']) + '.jpg')
    assert not link.exists()
    owned.add(link)
    submit('links/detail/' + str(ids['links']) + '/', 'editForm-submit')
    assert link.read_bytes() == png
    menu = root / 'images/menu' / (str(ids['menu']) + '.png')
    hover = root / 'images/menu' / (str(ids['menu']) + '_h.png')
    assert not menu.exists() and not hover.exists()
    owned.update([menu, hover])
    submit('menu/detail-images/' + str(ids['menu']) + '/', 'menuUpdateImages-updateImagesForm-submit',
        files={'the_file': (prefix + '.png', png, 'image/png'), 'the_file_2': (prefix + '.png', png, 'image/png')})
    assert menu.read_bytes() == png and hover.read_bytes() == png
    print('PASS: link replacement and multiple menu images persist', flush=True)

    # File manager cannot mutate page-managed media or hidden managed image areas.
    endpoint = base + '/admin/?do=elfinder-options'
    opened = session.get(endpoint, params={'cmd': 'open', 'init': 1, 'tree': 1}, timeout=15).json()
    media = next(f for f in opened['files'] if f.get('name') == 'media' and f.get('isroot'))
    assert not media['write']
    rejected = session.post(endpoint, data={'cmd': 'mkdir', 'target': media['hash'], 'name': prefix},
        headers={'X-CSRF-Token': csrf, 'X-elFinder-CSRF': opened['csrf']}, timeout=15)
    assert rejected.json().get('error') and not (root / 'media' / prefix).exists()
    # The carousel row is deleted through its legacy-named file as well.
    legacy = root / 'images/carousel' / (prefix + ' legacy2.jpg')
    owned.add(legacy)
    legacy.write_bytes(png)
    sql('UPDATE carousel SET image = @p0 WHERE id = %d' % ids['carousel'], legacy.name)
    second = legacy
    for route, signal, params, file in [
        ('links/detail/' + str(ids['links']) + '/', 'delete', {}, link),
        ('appearance/', 'carouselManager-delete', {'carouselManager-id': ids['carousel']}, second),
        ('menu/', 'menuEditor-delete', {'node_id': ids['menu']}, menu),
    ]:
        deleted = session.post(base + '/admin/' + route, params={'do': signal, **params}, data={'_csrf': csrf}, allow_redirects=False, timeout=15)
        assert deleted.status_code in (200, 302, 303) and not file.exists(), (signal, deleted.status_code)
    assert not hover.exists()
    print('PASS: elFinder media writes refused; link/carousel/menu row deletion removes owned files', flush=True)
finally:
    # Each path is a fixture-created file recorded above, never a directory tree.
    for file in owned:
        if file.is_file():
            file.unlink()
    fixture('cleanup')
