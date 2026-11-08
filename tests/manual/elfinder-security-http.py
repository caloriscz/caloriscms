"""Local patched connector checks; inert files, no external network targets.

No mutating command is sent to a real root, www/images/.htaccess or a real managed
folder, in any spelling. Root-level checks use a disposable media root selected
with ?path=<number>. The checks that write at the images root itself (extract in
the root, archive of a whole root, writes into a forged managed folder) run only
against a disposable copy of the tree:

    ELFINDER_HTTP_COPY=<host path of the copy>  ELFINDER_HTTP_BASE=<its URL>
    ELFINDER_HTTP_EXEC="docker exec -i -u www-data <container>"

The copy must be served at /var/www/html in that container. Copy mode never falls
back to the real stack: ELFINDER_HTTP_BASE is required and port 8090 is refused.
"""
import base64
import hashlib
import io
import os
import pathlib
import re
import shlex
import subprocess
import sys
import time
import urllib.parse
import zipfile
import requests
from http_helpers import login

prefix = sys.argv[1]
assert re.fullmatch(r'permtest-[a-f0-9]{8}', prefix)
repo = pathlib.Path(__file__).resolve().parents[2]
copy = os.environ.get('ELFINDER_HTTP_COPY')
root = pathlib.Path(copy).resolve() if copy else repo
assert not copy or root != repo, 'ELFINDER_HTTP_COPY must be a disposable copy, not this tree'
if copy:
    # The copy-only checks send forged rm, rename, paste and extract at the images
    # root: they must reach the copy, never the real stack (any host spelling of 8090).
    base = os.environ.get('ELFINDER_HTTP_BASE', '')
    assert base, 'ELFINDER_HTTP_BASE is required with ELFINDER_HTTP_COPY'
    assert urllib.parse.urlsplit(base).port not in (None, 8090), 'ELFINDER_HTTP_BASE must not be the real stack'
else:
    base = 'http://localhost:8090'
app_exec = shlex.split(os.environ.get('ELFINDER_HTTP_EXEC', '')) if copy else \
    ['docker', 'compose', 'exec', '-T', '-u', 'www-data', 'app']
assert app_exec, 'ELFINDER_HTTP_EXEC is required with ELFINDER_HTTP_COPY'
folder = (root / 'www/images' / prefix).resolve()
assert folder.parent == (root / 'www/images').resolve() and not folder.exists()
# Disposable dot files only; the real www/images/.htaccess is never a target.
htaccess = root / 'www/images/.htaccess'
htaccess_sha = hashlib.sha256(htaccess.read_bytes()).hexdigest()
root_dot = root / 'www/images' / ('.' + prefix + '-probe')
media_dot = root / 'www/media' / ('.' + prefix + '-probe')
assert not root_dot.exists() and not media_dot.exists()
temp = root / 'temp/elfinder'
planted = temp / (prefix + '-planted')
zips_before = set(temp.glob('elfzdl-*'))
thumbnails = []
png = base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aCr8AAAAASUVORK5CYII=')
session = login(base, prefix + '-full')
page = session.get(base + '/admin/', timeout=15)
csrf = re.search(r'name="admin-csrf" content="([a-f0-9]{64})"', page.text)[1]
bundle = re.search(r'src="(/js/all-back.js\?v=([a-f0-9]{16}))"', page.text)
assert bundle and hashlib.sha256(session.get(base + bundle[1], timeout=15).content).hexdigest().startswith(bundle[2])
assert 'Version 2.1.70 ' in session.get(base + bundle[1], timeout=15).text
assert session.get(base + '/vendor/elfinder/css/elfinder.min.css', timeout=15).status_code == 200
# The Info dialog's md5/sha256 and Quick Look on ZIP/TIFF load elFinder's workers.
for worker in ['calcfilehash.js', 'quicklook.unzip.js', 'quicklook.tiff.js']:
    assert session.get(base + '/vendor/elfinder/js/worker/' + worker, timeout=15).status_code == 200, worker
# Apache applies www/images/.htaccess; PHP's built-in server (the PHP 8 probe) does not.
apache = 'Apache' in session.get(base + '/', timeout=15).headers.get('Server', '')
endpoint = base + '/admin/?do=elfinder-options'
for client in [requests.Session(), login(base, prefix + '-denied')]:
    denied = client.get(endpoint, params={'cmd': 'open', 'init': 1}, allow_redirects=False, timeout=15)
    assert denied.status_code in (302, 403), denied.status_code
opened = session.get(endpoint, params={'cmd': 'open', 'init': 1, 'tree': 1}, timeout=15).json()
images = next(f for f in opened['files'] if f.get('name') == 'images' and f.get('isroot'))
media = next(f for f in opened['files'] if f.get('name') == 'media' and f.get('isroot'))
headers = {'X-CSRF-Token': csrf, 'X-elFinder-CSRF': opened['csrf']}

def target_hash(volume, relative):
    # elFinder's hash: volume id + URL-safe base64 of the path inside the root.
    return volume['volumeid'] + base64.b64encode(relative.encode()).decode().translate(str.maketrans('+/=', '-_.')).rstrip('.')

def post(data, files=None):
    return session.post(endpoint, data=data, files=files, headers=headers, timeout=15)

def names(target):
    listing = session.get(endpoint, params={'cmd': 'open', 'target': target}, timeout=15).json()
    return [f['name'] for f in listing['files'] if f.get('phash') == target]

def in_app(*command):
    # Windows can't name a file with a newline, so that fixture is made in the container,
    # as the web user, who has to be able to delete it.
    return subprocess.check_output([*app_exec, *command])

def mode(relative):
    return in_app('stat', '-c', '%a', relative).decode().strip()

def refused(response):
    # rm and duplicate answer a warning (errLocked, errPerm) with nothing removed or added.
    try:
        body = response.json()
    except ValueError:
        return response.status_code in (403, 404)
    return bool(body.get('error') or body.get('warning')) and not body.get('added') and not body.get('removed')

try:
    # CMS token is valid, but connector's own netmount token must also be valid.
    rejected = session.post(endpoint, data={'cmd': 'netmount', 'protocol': 'ftp', 'host': 'example.invalid'},
        headers={'X-CSRF-Token': csrf}, timeout=15)
    assert rejected.json().get('csrfReload') is True, rejected.text[:200]
    folder.mkdir()
    with zipfile.ZipFile(folder / 'fixture.zip', 'w') as archive:
        for ext in ['phtml', 'phar', 'php5', 'php3', 'txt']:
            archive.writestr('fixture.' + ext, 'Inert security regression data')
    listing = session.get(endpoint, params={'cmd': 'open', 'target': images['hash']}, timeout=15).json()
    target = next(f['hash'] for f in listing['files'] if f['name'] == prefix)
    listing = session.get(endpoint, params={'cmd': 'open', 'target': target}, timeout=15).json()
    archive = next(f['hash'] for f in listing['files'] if f['name'] == 'fixture.zip')
    extracted = session.post(endpoint, data={'cmd': 'extract', 'target': archive}, headers=headers, timeout=15)
    assert extracted.status_code == 200, extracted.text[:200]
    assert not any(p.suffix in ('.phtml', '.phar', '.php5', '.php3') for p in folder.rglob('*'))
    uploaded = session.post(endpoint, data={'cmd': 'upload', 'target': target},
        files={'upload[]': ('safe.txt', b'Safe upload', 'text/plain')}, headers=headers, timeout=15)
    assert uploaded.json().get('added') and (folder / 'safe.txt').read_bytes() == b'Safe upload', uploaded.text[:200]
    print('PASS: patched served client/hash, protected netmount, safe archive extraction and normal upload', flush=True)

    # Dot files (www/images/.htaccess guards /images) are hidden, unreadable,
    # locked and read-only in both roots, whatever the client sends.
    folder_dot = folder / ('.' + prefix + '-dot')
    source = folder / 'src'
    source.mkdir()
    # Letters-only names too: duplicate read ".env" as an empty name plus an
    # extension, so its copy (" copy 1.env") lost the dot (N8).
    letters_dot = folder / '.env'
    letters_src = source / '.htpasswd'
    for path, content in [(root_dot, b'root dot must survive'), (media_dot, b'media dot must survive'),
            (folder_dot, b'dot must survive'), (source / folder_dot.name, b'attacker'), (source / 'plain.txt', b'plain'),
            (letters_dot, b'letters dot must survive'), (letters_src, b'letters dot must survive')]:
        path.write_bytes(content)
    snapshot = lambda: [p.read_bytes() for p in (root_dot, media_dot, folder_dot, source / folder_dot.name, letters_dot, letters_src)]
    before = snapshot()
    for listed in [names(images['hash']), names(media['hash']), names(target)]:
        assert not [n for n in listed if n.startswith('.')], listed
    ls = session.get(endpoint, params={'cmd': 'ls', 'target': target}, timeout=15).json()
    assert not [n for n in ls.get('list', {}).values() if n.startswith('.')], ls
    dots = {
        'root': target_hash(images, root_dot.name), 'media': target_hash(media, media_dot.name),
        'folder': target_hash(images, prefix + '/' + folder_dot.name), 'source': target_hash(images, prefix + '/src/' + folder_dot.name),
        'htaccess': target_hash(images, '.htaccess'), 'letters': target_hash(images, prefix + '/.env'),
        'letters_src': target_hash(images, prefix + '/src/.htpasswd'),
    }
    for label, dot in dots.items():
        read = session.get(endpoint, params={'cmd': 'file', 'target': dot}, timeout=15)
        assert read.status_code in (403, 404) and b'must survive' not in read.content and b'Require' not in read.content, (label, read.status_code)
        assert refused(session.get(endpoint, params={'cmd': 'get', 'target': dot}, timeout=15)), label
        # info still answers for a known hash, but only as hidden, unreadable and locked.
        info = session.get(endpoint, params={'cmd': 'info', 'targets[]': dot}, timeout=15).json()
        assert all((f['read'], f['write'], f['locked'], f['hidden']) == (0, 0, 1, 1) for f in info.get('files', [])), (label, info)
    for label, dot in dots.items():
        # url only builds a link from a name the caller already knows; a one-time
        # link can't read a local file (elFinder looks for it in its temp directory).
        onetime = session.get(endpoint, params={'cmd': 'url', 'target': dot, 'options[onetime]': '1'}, timeout=15).json().get('url', '')
        token = re.search(r'target=([a-f0-9]+)', onetime)
        if token:
            served = session.get(endpoint, params={'cmd': 'file', 'onetime': '1', 'target': token[1]}, timeout=15)
            assert served.status_code == 404 and b'must survive' not in served.content, (label, served.status_code)
    # dim isn't checked here: it ignores read (0c4539cc item A1), and these aren't images.
    for dot_url in ['/images/' + root_dot.name, '/media/' + media_dot.name, '/images/.htaccess', '/media/.htaccess',
            '/images/' + prefix + '/.env']:
        assert not apache or session.get(base + dot_url, timeout=15).status_code == 403, dot_url
    for volume in [images, media]:
        tree = session.get(endpoint, params={'cmd': 'tree', 'target': volume['hash']}, timeout=15).json()
        assert not [f['name'] for f in tree.get('tree', []) if f['name'].startswith('.')], tree
        for q in [prefix, 'env', 'htpasswd']:
            found = session.get(endpoint, params={'cmd': 'search', 'q': q, 'target': volume['hash']}, timeout=15).json()
            assert not [f['name'] for f in found.get('files', []) if f['name'].startswith('.')], found
    for label in ['folder', 'root', 'media', 'letters', 'letters_src']:
        dot = dots[label]
        assert refused(post({'cmd': 'rm', 'targets[]': dot})), label
        assert refused(post({'cmd': 'rename', 'target': dot, 'name': prefix + '-renamed.txt'})), label
        assert refused(post({'cmd': 'duplicate', 'targets[]': dot})), label
        assert refused(post({'cmd': 'put', 'target': dot, 'content': 'overwritten'})), label
    # duplicate is refused as a whole: an ordinary file listed with a dot file isn't copied either.
    assert refused(post({'cmd': 'duplicate', 'targets[]': [target_hash(images, prefix + '/src/plain.txt'), dots['letters_src']]}))
    copies = lambda directory: [p.name for p in directory.iterdir() if ' copy ' in p.name]
    assert not [p for p in folder.rglob('*') if ' copy ' in p.name], 'duplicate created a copy'
    assert not copies(root / 'www/images') and not copies(root / 'www/media'), 'duplicate created a copy in a root'
    # The control: an ordinary file and an ordinary folder in a writable folder still
    # duplicate, and the folder's copy holds none of its dot files.
    copied = post({'cmd': 'duplicate', 'targets[]': target_hash(images, prefix + '/src/plain.txt')}).json()
    assert [f['name'] for f in copied.get('added', [])] == ['plain copy 1.txt'], copied
    copied = post({'cmd': 'duplicate', 'targets[]': target_hash(images, prefix + '/src')}).json()
    assert 'src copy 1' in [f['name'] for f in copied.get('added', [])], copied
    assert sorted(p.name for p in (folder / 'src copy 1').rglob('*')) == ['plain copy 1.txt', 'plain.txt']
    for overwrite in ('0', '1'):
        assert refused(post({'cmd': 'upload', 'target': target, 'overwrite': overwrite},
            files={'upload[]': (folder_dot.name, b'overwritten', 'text/plain')})), overwrite
    for cut in ('0', '1'):
        assert refused(post({'cmd': 'paste', 'dst': target, 'targets[]': dots['source'], 'cut': cut})), cut
    for command, name in [('mkdir', '.' + prefix + '-dir'), ('mkfile', '.' + prefix + '-file')]:
        assert refused(post({'cmd': command, 'target': target, 'name': name})), command
    # The other upload forms: renames[] with a backup suffix, into a dot folder
    # through upload_path[], and a chunked upload that merges onto a dot name.
    assert refused(post({'cmd': 'upload', 'target': target, 'renames[]': folder_dot.name, 'suffix': '~'},
        files={'upload[]': (folder_dot.name, b'overwritten', 'text/plain')}))
    dot_folder = folder / ('.' + prefix + '-dir')
    dot_folder.mkdir()
    assert refused(post({'cmd': 'upload', 'target': target, 'upload_path[]': target_hash(images, prefix + '/' + dot_folder.name)},
        files={'upload[]': (prefix + '-in-dot.txt', b'overwritten', 'text/plain')}))
    assert not list(dot_folder.iterdir())
    part = post({'cmd': 'upload', 'target': target, 'chunk': folder_dot.name + '.0_0.part', 'cid': '1', 'range': '0,11,11'},
        files={'upload[]': ('blob', b'overwritten', 'application/octet-stream')}).json()
    if part.get('_chunkmerged'):
        part = post({'cmd': 'upload', 'target': target, 'chunk': part['_chunkmerged'], 'upload[]': part['_name'], 'cid': '1'}).json()
    assert not part.get('added') and (part.get('error') or part.get('warning')), part
    # archive zips a tree natively, past the attributes and following symlinks, and
    # saves the ZIP in the public folder: a folder with a dot file inside is refused
    # and nothing is created (a whole root: see the disposable media root below, and
    # the images root in a disposable copy).
    assert refused(post({'cmd': 'archive', 'targets[]': target, 'type': 'application/zip'}))
    assert not list((root / 'www/images').glob('*.zip'))
    # zipdl checks read on every item (getItemsInHand), so it skips hidden entries:
    # a folder with a dot file downloads again (behaviour restored by removing the
    # old refusal) but without the dot file, and a whole root excludes .htaccess and
    # the managed folders. Nothing hidden reaches the ZIP.
    dot_ready = post({'cmd': 'zipdl', 'targets[]': target}).json().get('zipdl')
    assert dot_ready, 'zipdl of a folder with a dot file'
    dot_dl = session.get(endpoint, params={'cmd': 'zipdl', 'download': '1',
        'targets[]': [target, dot_ready['file'], dot_ready['name'], dot_ready['mime']]}, timeout=15)
    assert dot_dl.status_code == 200, dot_dl.status_code
    with zipfile.ZipFile(io.BytesIO(dot_dl.content)) as made_zip:
        assert not [n for n in made_zip.namelist() if n.startswith('.') or '/.' in n], made_zip.namelist()
    root_ready = post({'cmd': 'zipdl', 'targets[]': images['hash']}).json().get('zipdl')
    assert root_ready, 'zipdl of the images root'
    root_dl = session.get(endpoint, params={'cmd': 'zipdl', 'download': '1',
        'targets[]': [images['hash'], root_ready['file'], root_ready['name'], root_ready['mime']]}, timeout=15)
    with zipfile.ZipFile(io.BytesIO(root_dl.content)) as made_zip:
        assert not [n for n in made_zip.namelist() if n.startswith('.') or '/.' in n
            or re.match(r'(?:tn|admin|menu|paths|carousel|profiles)(?:/|$)', n)], made_zip.namelist()
    # A folder without dot files still zips, both ways, with its content only.
    clean = source / 'clean'
    clean.mkdir()
    (clean / 'plain.txt').write_bytes(b'plain')
    clean_hash = target_hash(images, prefix + '/src/clean')
    archived = post({'cmd': 'archive', 'targets[]': clean_hash, 'type': 'application/zip'}).json()
    assert [f['name'] for f in archived.get('added', [])] == ['clean.zip'], archived
    with zipfile.ZipFile(source / 'clean.zip') as made_zip:
        assert sorted(made_zip.namelist()) == ['clean/', 'clean/plain.txt'], made_zip.namelist()
    ready = post({'cmd': 'zipdl', 'targets[]': clean_hash}).json().get('zipdl')
    assert ready, 'zipdl of a clean folder'
    download = session.get(endpoint, params={'cmd': 'zipdl', 'download': '1',
        'targets[]': [clean_hash, ready['file'], ready['name'], ready['mime']]}, timeout=15)
    assert download.status_code == 200, download.status_code
    with zipfile.ZipFile(io.BytesIO(download.content)) as made_zip:
        assert 'clean/plain.txt' in made_zip.namelist() and not [n for n in made_zip.namelist() if '/.' in n], made_zip.namelist()
    with zipfile.ZipFile(folder / 'dots.zip', 'w') as dot_archive:
        for name in ['.' + prefix + '-zip', 'sub/.' + prefix + '-zip', '.htaccess', 'keep.txt', 'n\nl.txt']:
            dot_archive.writestr(name, 'Inert dot-file regression data')
    zipped = next(f['hash'] for f in session.get(endpoint, params={'cmd': 'open', 'target': target}, timeout=15).json()['files']
        if f['name'] == 'dots.zip')
    extracted = post({'cmd': 'extract', 'target': zipped}).json()
    # unzip strips the newline from that entry's name; a name that still held it
    # would be refused by acceptedName (ElfinderSecurityTest.phpt covers that).
    added = sorted(f['name'] for f in extracted.get('added', []))
    assert [n for n in added if n != 'nl.txt'] == ['keep.txt', 'sub'] and 'errSave' in extracted.get('warning', []), extracted
    assert (folder / 'sub').is_dir() and not list((folder / 'sub').iterdir()), 'sub keeps no dot entry'
    # Into a new folder: elFinder renames it from its temp directory, which is now
    # on the same filesystem (from the system temp this was errExtract here, and
    # folders inside an archive were silently lost).
    extracted = post({'cmd': 'extract', 'target': zipped, 'makedir': '1'}).json()
    assert [f['name'] for f in extracted.get('added', [])] == ['dots'] and (folder / 'dots/keep.txt').exists(), extracted
    # The new folder is elFinder's 0777 quarantine folder renamed into place: it gets
    # the mode of a folder made in the File Manager (N6), also with cmd in the query.
    assert mode('www/images/{}/dots'.format(prefix)) == '755', mode('www/images/{}/dots'.format(prefix))
    in_app('rm', '-rf', 'www/images/{}/dots'.format(prefix))
    queried = session.post(endpoint + '&cmd=extract', data={'target': zipped, 'makedir': '1'}, headers=headers, timeout=15).json()
    assert [f['name'] for f in queried.get('added', [])] == ['dots'], queried
    assert mode('www/images/{}/dots'.format(prefix)) == '755', mode('www/images/{}/dots'.format(prefix))
    unpacked = [p for p in folder.rglob('*') if p.name.endswith('-zip') or (p.name == '.htaccess')]
    assert not unpacked and (folder / 'keep.txt').exists(), unpacked
    assert snapshot() == before and hashlib.sha256(htaccess.read_bytes()).hexdigest() == htaccess_sha
    print('PASS: disposable dot files in both roots (hyphenated and letters-only names) are not listed'
        ' (open/ls/tree/search), not readable (file/get/info; no one-time link serves them; direct URL 403 under Apache),'
        ' not removable, renamable, duplicable or writable (rm/rename/duplicate/put), and a duplicate listing an ordinary'
        ' file with a dot file copies nothing; an ordinary file and folder still duplicate, the folder without its dot'
        ' files; dot files are not overwritten by upload (overwrite 0 or 1, renames[], chunked, upload_path[] into a dot'
        ' folder) or paste (copy or cut), not created by mkdir/mkfile/extract; archive of a folder with a dot file is'
        ' refused; zipdl of a folder with dot files and of the whole images root downloads with hidden entries (dot files,'
        ' managed folders, .htaccess) excluded; a folder made by extract is 0755; .htaccess (read commands only)'
        ' unchanged', flush=True)

    # A folder already named with a newline: the dot rule has to cross it. The plain
    # file next to the dot file is the control that the folder itself is readable.
    newline_folder = prefix + '/nl\nfolder'
    newline_dot = '.' + prefix + '-nl'
    in_app('php', '-r', "$d = 'www/images/{}/nl' . chr(10) . 'folder'; mkdir($d);"
        " file_put_contents($d . '/{}', 'newline dot must survive'); file_put_contents($d . '/plain.txt', 'plain');".format(prefix, newline_dot))
    read_dot = lambda: in_app('php', '-r', "echo file_get_contents('www/images/{}/nl' . chr(10) . 'folder/{}');".format(prefix, newline_dot))
    newline_target = target_hash(images, newline_folder)
    newline_hash = target_hash(images, newline_folder + '/' + newline_dot)
    assert 'nl\nfolder' in names(target) and names(newline_target) == ['plain.txt'], names(newline_target)
    shown = session.get(endpoint, params={'cmd': 'file', 'target': target_hash(images, newline_folder + '/plain.txt')}, timeout=15)
    assert shown.status_code == 200 and shown.content == b'plain', shown.status_code
    read = session.get(endpoint, params={'cmd': 'file', 'target': newline_hash}, timeout=15)
    assert read.status_code in (403, 404) and b'must survive' not in read.content, read.status_code
    assert refused(session.get(endpoint, params={'cmd': 'get', 'target': newline_hash}, timeout=15))
    info = session.get(endpoint, params={'cmd': 'info', 'targets[]': newline_hash}, timeout=15).json()
    assert all((f['read'], f['write'], f['locked'], f['hidden']) == (0, 0, 1, 1) for f in info.get('files', [])), info
    assert refused(post({'cmd': 'rm', 'targets[]': newline_hash}))
    assert refused(post({'cmd': 'rename', 'target': newline_hash, 'name': prefix + '-renamed.txt'}))
    assert refused(post({'cmd': 'archive', 'targets[]': newline_target, 'type': 'application/zip'}))
    assert read_dot() == b'newline dot must survive'
    # New names with control characters are refused; an ordinary one is the control.
    sample = target_hash(images, prefix + '/fixture.txt')
    for bad in ['a\nb', 'a\rb', 'a\tb', 'a\x7fb', 'a\u0085b']:
        assert refused(post({'cmd': 'mkdir', 'target': target, 'name': bad})), repr(bad)
        assert refused(post({'cmd': 'mkfile', 'target': target, 'name': bad + '.txt'})), repr(bad)
        assert refused(post({'cmd': 'rename', 'target': sample, 'name': bad + '.txt'})), repr(bad)
    # A multipart file name can't hold a raw newline; browsers send it as %0A.
    for bad in ['a\tb.txt', 'a\x7fb.txt']:
        assert refused(post({'cmd': 'upload', 'target': target}, files={'upload[]': (bad, b'x', 'text/plain')})), repr(bad)
    # duplicate's suffix becomes part of the new name but skips acceptedName, so a
    # control character in it is refused too, and no such name is created (D8).
    assert refused(post({'cmd': 'duplicate', 'targets[]': sample, 'suffix': 'x\ny'})), 'duplicate suffix'
    assert not [p for p in folder.rglob('*') if any(ord(c) < 32 or ord(c) == 127 for c in p.name)], 'control char entered a name'
    renamed = post({'cmd': 'rename', 'target': sample, 'name': 'Žluťoučký a.b.txt'}).json()
    assert renamed.get('added') and (folder / 'Žluťoučký a.b.txt').exists(), renamed
    print('PASS: a dot file in a folder with a newline in its name stays hidden, unreadable and locked (a plain file there'
        ' is listed and readable); new names with control characters are refused (mkdir/mkfile/rename/upload and a'
        " duplicate's suffix), ordinary ones are not", flush=True)

    # cmd in a POST's query string must not skip the handlers: elFinder registers a
    # named one only when $_POST names the command, and the connector reads both. The
    # archive handler still fires (dot folder refused, no ZIP written).
    with_query = lambda command, data, files=None: session.post(endpoint + '&cmd=' + command, data=data, files=files, headers=headers, timeout=15)
    assert refused(with_query('archive', {'targets[]': target, 'type': 'application/zip'}))
    assert not list((root / 'www/images').glob('*.zip'))

    # Only download=1 fetches without tokens. Any other value is what elFinder treats
    # as preparing a ZIP, or something the client never sends: 403, nothing prepared.
    prepared = lambda: set(temp.glob('elfzdl-*'))
    before_zips = prepared()
    for value in ['0', '', ' ', ' 0', '00', '2', 'true']:
        tokenless = session.get(endpoint, params={'cmd': 'zipdl', 'download': value, 'targets[]': clean_hash}, timeout=15)
        assert tokenless.status_code == 403 and b'zipdl' not in tokenless.content, (value, tokenless.status_code)
        assert prepared() == before_zips, value
    assert session.get(endpoint, params={'cmd': 'zipdl', 'targets[]': clean_hash}, timeout=15).status_code == 403
    assert prepared() == before_zips
    # The CMS token decision reads the same PHP superglobals the connector executes
    # from, so a forged query key, a NUL-truncated one, and X-HTTP-Method-Override
    # can't make Nette and the connector disagree (D3). All of these are 403 with
    # nothing prepared or removed. The real rm the connector would run on cmd=rm is
    # the control: a visible file stays.
    probe = folder / (prefix + '-d3keep.txt')
    probe.write_bytes(b'keep')
    probe_hash = target_hash(images, prefix + '/' + probe.name)
    def raw(qs, headers=None, method='GET'):
        return session.request(method, endpoint + '&' + qs, headers=headers or {}, timeout=15)
    # a second ' cmd' key: PHP strips the leading space, so $_GET['cmd'] becomes the
    # last one (zipdl / rm) while Nette still parses the first (open).
    assert raw('cmd=open&%20cmd=zipdl&targets%5B%5D=' + clean_hash).status_code == 403
    assert raw('cmd=open&%20cmd=rm&targets%5B%5D=' + probe_hash,
        headers={'X-elFinder-CSRF': opened['csrf']}).status_code == 403 and probe.exists()
    for qs in ['download=1&%20download=0', '%2Bdownload=', 'download%00x=0']:
        assert raw('cmd=zipdl&targets%5B%5D=' + clean_hash + '&' + qs).status_code == 403
    # HEAD of the same rm attempt, and a multipart POST with the method override and
    # only the connector token: both 403, the file still there.
    assert raw('cmd=open&%20cmd=rm&targets%5B%5D=' + probe_hash,
        headers={'X-elFinder-CSRF': opened['csrf']}, method='HEAD').status_code == 403 and probe.exists()
    override = session.post(endpoint, data={'cmd': 'rm', 'targets[]': probe_hash}, files={'x': ('x', b'x')},
        headers={'X-elFinder-CSRF': opened['csrf'], 'X-HTTP-Method-Override': 'GET'}, timeout=15)
    assert override.status_code == 403 and probe.exists(), override.status_code
    assert prepared() == before_zips
    # normal reads and the download=1 fetch are unaffected.
    assert session.get(endpoint, params={'cmd': 'open', 'target': target}, timeout=15).status_code == 200
    print('PASS: the *.pre archive handler fires with cmd in a POST query string; zipdl without tokens prepares nothing, only'
        ' download=1 is exempt; forged query keys (cmd=open&%20cmd=zipdl/rm, download tricks), HEAD and X-HTTP-Method-Override'
        ' are 403 with nothing prepared or removed; normal reads still work', flush=True)

    # With CriOS in the User-Agent elFinder takes the file from targets[1] (Chrome on
    # iOS fetches it twice), so anything but a prepared ZIP's name is refused.
    ios = {'User-Agent': 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15'
        ' (KHTML, like Gecko) CriOS/120.0.0.0 Mobile/15E148 Safari/604.1'}
    planted.write_bytes(b'planted must survive')
    errors = root / 'www/log/error.log'
    log_size = errors.stat().st_size if errors.exists() else 0
    for name in [planted.name, '.', '..', '../' + planted.name, 'elfzdl-', 'elfzdl-../' + planted.name]:
        fetched = session.get(endpoint, params={'cmd': 'zipdl', 'download': '1',
            'targets[]': [clean_hash, name, 'x.zip', 'application/zip']}, headers=ios, timeout=15)
        assert fetched.status_code == 404 and b'planted' not in fetched.content, (name, fetched.status_code)
    # The same with cmd in the query string of a POST, past the handler registration.
    posted = session.post(endpoint + '&cmd=zipdl', data={'download': '1', 'targets[]': [clean_hash, planted.name, 'x.zip', 'application/zip']},
        headers={**headers, **ios}, timeout=15)
    assert posted.status_code == 404 and b'planted' not in posted.content, posted.status_code
    assert planted.read_bytes() == b'planted must survive', 'a named file in the temp directory was served or deleted'
    assert (errors.stat().st_size if errors.exists() else 0) == log_size, 'the CriOS requests logged PHP errors'
    # The Chrome on iOS flow: prepare, a first fetch that keeps the file, a second that removes it.
    ready = session.post(endpoint, data={'cmd': 'zipdl', 'targets[]': clean_hash}, headers={**headers, **ios}, timeout=15).json()['zipdl']
    assert re.fullmatch(r'elfzdl-[a-zA-Z0-9]+', ready['file']), ready
    fetch = {'cmd': 'zipdl', 'download': '1', 'targets[]': [clean_hash, ready['file'], ready['name'], ready['mime']]}
    for accept, kept in [('text/html,application/xhtml+xml', True), ('*/*', False)]:
        fetched = session.get(endpoint, params=fetch, headers={**ios, 'Accept': accept}, timeout=15)
        assert fetched.status_code == 200 and fetched.content[:2] == b'PK', (accept, fetched.status_code)
        with zipfile.ZipFile(io.BytesIO(fetched.content)) as made_zip:
            assert 'clean/plain.txt' in made_zip.namelist()
        if kept:
            assert (temp / ready['file']).exists()
    for _ in range(50):
        if not (temp / ready['file']).exists():
            break
        time.sleep(0.1)
    assert not (temp / ready['file']).exists(), 'the second fetch left the ZIP behind'
    assert session.get(endpoint, params=fetch, headers=ios, timeout=15).status_code == 404
    # The prepared-ZIP name is bound to the session that prepared it: another session
    # that knows the name gets 404, so it cannot fetch or delete it (owner decision 4).
    bound = session.post(endpoint, data={'cmd': 'zipdl', 'targets[]': clean_hash}, headers={**headers, **ios}, timeout=15).json()['zipdl']
    cross = {'cmd': 'zipdl', 'download': '1', 'targets[]': [clean_hash, bound['file'], bound['name'], bound['mime']]}
    other = login(base, prefix + '-full')
    other.get(endpoint, params={'cmd': 'open', 'init': 1}, timeout=15)
    assert other.get(endpoint, params=cross, headers={**ios, 'Accept': 'text/html'}, timeout=15).status_code == 404
    assert (temp / bound['file']).exists(), 'another session deleted the prepared ZIP'
    owning = session.get(endpoint, params=cross, headers={**ios, 'Accept': '*/*'}, timeout=15)
    assert owning.status_code == 200 and owning.content[:2] == b'PK', owning.status_code
    print('PASS: with CriOS only a prepared ZIP can be fetched (a named temp file, . and .. get 404, nothing served, deleted or'
        ' logged); the name is bound to the preparing session (another session gets 404); the two-fetch flow of Chrome on iOS'
        ' still works', flush=True)

    # A chunked upload in two parts and its merge commit still work.
    chunks = [b'chunk-one|', b'chunk-two']
    total = sum(len(c) for c in chunks)
    start = 0
    for index, data in enumerate(chunks):
        part = post({'cmd': 'upload', 'target': target, 'chunk': 'chunked.txt.{}_1.part'.format(index), 'cid': '42',
            'range': '{},{},{}'.format(start, len(data), total)},
            files={'upload[]': ('blob', data, 'application/octet-stream')}).json()
        start += len(data)
    assert part.get('_chunkmerged'), part
    merged = post({'cmd': 'upload', 'target': target, 'chunk': part['_chunkmerged'], 'upload[]': part['_name'], 'cid': '42'}).json()
    assert [f['name'] for f in merged.get('added', [])] == ['chunked.txt'], merged
    assert (folder / 'chunked.txt').read_bytes() == b''.join(chunks)
    # The client's cleanup after a failed chunk (no file, upload[]=chunkfail,
    # mimes=chunkfail) is accepted and removes that upload's parts (N4).
    held = post({'cmd': 'upload', 'target': target, 'chunk': 'held.txt.0_1.part', 'cid': '43', 'range': '0,4,8'},
        files={'upload[]': ('blob', b'held', 'application/octet-stream')}).json()
    parts = list(temp.glob('ELF' + hashlib.md5(b'43_held.txt').hexdigest() + '*'))
    assert parts and not held.get('error'), (held, parts)
    failed = post({'cmd': 'upload', 'target': target, 'chunk': 'held.txt.0_1.part', 'cid': '43',
        'upload[]': 'chunkfail', 'mimes': 'chunkfail'}).json()
    assert 'Neplatný upload.' not in str(failed) and not failed.get('added'), failed
    assert not [p for p in parts if p.exists()], 'chunkfail left the parts behind'
    # A nested upload field (upload[a][b]) is refused with and without a chunk: on
    # PHP 8 it was a TypeError (N5). Nothing is written and nothing is logged.
    exc_log = root / 'www/log/exception.log'
    exc_size = exc_log.stat().st_size if exc_log.exists() else 0
    for extra in [{}, {'chunk': 'nested.png.0_0.part', 'cid': '44', 'range': '0,{0},{0}'.format(len(png))}]:
        nested = post({'cmd': 'upload', 'target': target, **extra}, files={'upload[a][b]': ('nested.png', png, 'image/png')})
        assert nested.status_code == 200 and nested.json().get('error') and not nested.json().get('added'), nested.text[:300]
    assert not (folder / 'nested.png').exists()
    assert (exc_log.stat().st_size if exc_log.exists() else 0) == exc_size, 'the nested upload logged an exception'
    # cmd=file with a non-empty onetime is 404 (N3): elFinder served a temp file named
    # by a pointer planted with a partial chunk, and deleted it, from any session.
    victim = temp / (prefix + '-victim')
    victim.write_bytes(b'victim must survive')
    pointer = ('{"file":"%s","name":"x.txt","mime":"text/plain"}' % base64.b64encode(victim.name.encode()).decode()).encode()
    # Part 0 of 2 with the whole pointer as its range: the .part file holds exactly it.
    post({'cmd': 'upload', 'target': target, 'chunk': 'p.0_1.part', 'cid': '45', 'range': '0,{0},{0}'.format(len(pointer))},
        files={'upload[]': ('blob', pointer, 'application/octet-stream')})
    planted_part = 'ELF' + hashlib.md5(b'45_p').hexdigest() + '.part'
    assert (temp / planted_part).read_bytes() == pointer, 'the partial chunk was not stored'
    for client in [requests.Session(), login(base, prefix + '-pictures'), session]:
        for onetime, onetime_target in [('1', planted_part[3:]), ('yes', planted_part[3:]), ('1', 'x')]:
            served = client.get(endpoint, params={'cmd': 'file', 'onetime': onetime, 'target': onetime_target},
                allow_redirects=False, timeout=15)
            assert served.status_code in (302, 403, 404) and b'victim' not in served.content, served.status_code
    assert victim.read_bytes() == b'victim must survive'
    post({'cmd': 'upload', 'target': target, 'chunk': 'p.0_1.part', 'cid': '45', 'upload[]': 'chunkfail', 'mimes': 'chunkfail'})
    assert not (temp / planted_part).exists()
    print('PASS: a two-part chunked upload merges with the right bytes; the client\'s chunkfail cleanup removes that'
        ' upload\'s parts; a nested upload field is refused with and without a chunk, nothing logged; cmd=file with'
        ' onetime is 404 for a planted pointer from no, another and the same session, and the pointed temp file survives', flush=True)

    # The server ignores a crafted overwrite=1: a duplicate name is renamed.
    again = post({'cmd': 'upload', 'target': target, 'overwrite': '1'}, files={'upload[]': ('safe.txt', b'Overwrite attempt', 'text/plain')})
    assert [f['name'] for f in again.json().get('added', [])] == ['safe-1.txt'], again.text[:300]
    assert (folder / 'safe.txt').read_bytes() == b'Safe upload' and (folder / 'safe-1.txt').read_bytes() == b'Overwrite attempt'
    query = with_query('upload', {'target': target, 'overwrite': '1'}, {'upload[]': ('safe.txt', b'Overwrite attempt', 'text/plain')})
    assert [f['name'] for f in query.json().get('added', [])] == ['safe-2.txt'], query.text[:300]
    assert (folder / 'safe.txt').read_bytes() == b'Safe upload'
    # Normal mkdir, rename, upload with a thumbnail and delete still work.
    made = post({'cmd': 'mkdir', 'target': target, 'name': 'made'}).json()
    assert [f['name'] for f in made.get('added', [])] == ['made'] and (folder / 'made').is_dir(), made
    renamed = post({'cmd': 'rename', 'target': target_hash(images, prefix + '/safe-1.txt'), 'name': 'renamed.txt'}).json()
    assert renamed.get('added') and (folder / 'renamed.txt').exists() and not (folder / 'safe-1.txt').exists(), renamed
    picture = post({'cmd': 'upload', 'target': target}, files={'upload[]': ('pixel.png', png, 'image/png')}).json()
    added = picture.get('added', [{}])[0]
    assert added.get('name') == 'pixel.png' and added.get('tmb') == 1, picture
    thumbs = session.get(endpoint, params={'cmd': 'tmb', 'targets[]': added['hash']}, timeout=15).json()
    tmb_name = thumbs.get('images', {}).get(added['hash'])
    assert tmb_name, thumbs
    thumbnails.append(root / 'www/images/.tmb' / tmb_name)
    tmb = session.get(base + '/images/.tmb/' + tmb_name, timeout=15)
    assert tmb.status_code == 200 and tmb.content[:8] == b'\x89PNG\r\n\x1a\n', tmb.status_code
    assert tmb_name not in names(images['hash'])
    # A non-ASCII name keeps its first letters (PHP 7.4's basename() lost them in a
    # non-UTF-8 locale), so a second upload gets a suffix instead of replacing the file.
    first = post({'cmd': 'upload', 'target': target}, files={'upload[]': ('Čáp.png', png, 'image/png')}).json()
    assert [f['name'] for f in first.get('added', [])] == ['Čáp.png'], first
    second = post({'cmd': 'upload', 'target': target}, files={'upload[]': ('Čáp.png', png + b'\0', 'image/png')}).json()
    assert [f['name'] for f in second.get('added', [])] == ['Čáp-1.png'], second
    assert (folder / 'Čáp.png').read_bytes() == png and (folder / 'Čáp-1.png').read_bytes() == png + b'\0'
    assert 'Čáp.png' in names(target)
    thumbs = session.get(endpoint, params={'cmd': 'tmb', 'targets[]': first['added'][0]['hash']}, timeout=15).json()
    non_ascii_tmb = root / 'www/images/.tmb' / thumbs['images'][first['added'][0]['hash']]
    thumbnails.append(non_ascii_tmb)
    assert non_ascii_tmb.is_file(), thumbs
    # SVG never goes out inline through the connector, with or without an XML declaration.
    script = b'<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'
    for name, body in [('bare.svg', script), ('declared.svg', b'<?xml version="1.0"?>' + script)]:
        svg = post({'cmd': 'upload', 'target': target}, files={'upload[]': (name, body, 'image/svg+xml')}).json()
        assert [f['name'] for f in svg.get('added', [])] == [name], svg
        served = session.get(endpoint, params={'cmd': 'file', 'target': svg['added'][0]['hash']}, timeout=15)
        assert served.status_code == 200 and served.headers['Content-Disposition'].startswith('attachment'), (name, served.headers)
    text = session.get(endpoint, params={'cmd': 'file', 'target': target_hash(images, prefix + '/safe.txt')}, timeout=15)
    assert text.headers['Content-Disposition'].startswith('inline'), text.headers
    removed = post({'cmd': 'rm', 'targets[]': target}).json()
    assert removed.get('removed') and not folder.exists(), removed
    # Deleting a folder deletes the dot files inside it (elFinder removes the tree
    # natively); the dot files in the roots, and .htaccess, are untouched.
    assert [root_dot.read_bytes(), media_dot.read_bytes()] == before[:2]
    assert hashlib.sha256(htaccess.read_bytes()).hexdigest() == htaccess_sha
    assert not non_ascii_tmb.exists(), 'thumbnail of a deleted non-ASCII file stayed behind'
    print('PASS: crafted overwrite=1 upload renamed (safe-1.txt), original kept; mkdir, rename, PNG upload with .tmb'
        ' thumbnail (served 200) and recursive delete work; a non-ASCII name survives and a duplicate gets -1;'
        ' SVG is served as an attachment', flush=True)
finally:
    if folder.exists():
        assert folder.parent == (root / 'www/images').resolve() and folder.name == prefix
        in_app('rm', '-rf', 'www/images/' + prefix)
    for zip_left in set(temp.glob('elfzdl-*')) - zips_before:
        zip_left.unlink()
    # A folder's ZIP lands next to it, should the archive refusal ever fail.
    for dot in [root_dot, media_dot, planted, temp / (prefix + '-victim'), root / 'www/images' / (prefix + '.zip')] + thumbnails:
        if dot.is_file():
            dot.unlink()
    assert hashlib.sha256(htaccess.read_bytes()).hexdigest() == htaccess_sha

# ---- Forged // hashes, extract into a managed folder, symlinks, unreadable subfolders ----
# D1, D5, D6 and D7 of the agent-verifier note of 2026-11-07, as a pictures-only user
# with only the images root: the threat model is a signed-in admin who crafts hashes
# and knows the absolute document root. Disposable fixtures only; the real
# www/images/.htaccess is read-probed, never written or removed.
docroot = '/var/www/html/www/images'
pics = login(base, prefix + '-pictures')
pcsrf = re.search(r'name="admin-csrf" content="([a-f0-9]{64})"', pics.get(base + '/admin/', timeout=15).text)[1]
popened = pics.get(endpoint, params={'cmd': 'open', 'init': 1, 'tree': 1}, timeout=15).json()
pimg = next(f for f in popened['files'] if f.get('name') == 'images' and f.get('isroot'))
phdr = {'X-CSRF-Token': pcsrf, 'X-elFinder-CSRF': popened['csrf']}
pfolder = (root / 'www/images' / prefix).resolve()
assert pfolder.parent == (root / 'www/images').resolve() and not pfolder.exists()
rootdot = root / 'www/images' / ('.' + prefix + '-fh')

def ppost(data, files=None):
    return pics.post(endpoint, data=data, files=files, headers=phdr, timeout=15)

def forged(absolute):
    return pimg['volumeid'] + base64.b64encode(absolute.encode()).decode().translate(str.maketrans('+/=', '-_.')).rstrip('.')

try:
    pfolder.mkdir()
    rootdot.write_bytes(b'forged-hash probe must survive')
    # D1: a forged //absolute-path hash must not bypass the dot or managed rules.
    # Every spelling resolves to the same in-root file, and all are refused.
    for ab in [docroot + '/' + rootdot.name, '/' + docroot + '/' + rootdot.name, '//' + docroot + '/' + rootdot.name,
               './.' + prefix + '-fh', prefix + '/../.' + prefix + '-fh']:
        fh = forged(ab)
        r = pics.get(endpoint, params={'cmd': 'file', 'target': fh}, timeout=15)
        assert r.status_code in (403, 404) and b'must survive' not in r.content, (ab, r.status_code)
        assert refused(pics.get(endpoint, params={'cmd': 'get', 'target': fh}, timeout=15)), ab
        assert refused(ppost({'cmd': 'rm', 'targets[]': fh})), ab
        assert refused(ppost({'cmd': 'put', 'target': fh, 'content': 'x'})), ab
        assert refused(ppost({'cmd': 'rename', 'target': fh, 'name': prefix + '-r.txt'})), ab
        info = pics.get(endpoint, params={'cmd': 'info', 'targets[]': fh}, timeout=15).json().get('files', [])
        assert all((f['read'], f['write'], f['locked'], f['hidden']) == (0, 0, 1, 1) for f in info), (ab, info)
    assert rootdot.read_bytes() == b'forged-hash probe must survive'
    # The real .htaccess, the managed carousel folder and the images root through a
    # forged // hash: read commands only. All are hidden, unreadable and locked; the
    # canonical root hash stays locked too.
    assert pics.get(endpoint, params={'cmd': 'file', 'target': forged('/' + docroot + '/.htaccess')}, timeout=15).status_code in (403, 404)
    for ab in ['/' + docroot + '/.htaccess', '/' + docroot + '/carousel', '/' + docroot, '/' + docroot + '/', '/proc/self/root' + docroot]:
        info = pics.get(endpoint, params={'cmd': 'info', 'targets[]': forged(ab)}, timeout=15).json().get('files', [])
        # elFinder's own root check finds no /proc/self/root/... path; the others resolve.
        assert (info or ab.startswith('/proc/')) and all((f['read'], f['write'], f.get('locked'), f.get('hidden')) == (0, 0, 1, 1)
            for f in info), (ab, info)
    info = pics.get(endpoint, params={'cmd': 'info', 'targets[]': pimg['hash']}, timeout=15).json()['files'][0]
    assert info.get('locked') == 1 and info.get('isroot') == 1, info
    if copy:
        # In a disposable copy only: a forged hash into the managed carousel folder.
        for ab in ['/' + docroot + '/carousel', '//' + docroot + '/carousel']:
            assert refused(ppost({'cmd': 'mkfile', 'target': forged(ab), 'name': prefix + '-x.txt'})), ab
        assert not (root / 'www/images/carousel' / (prefix + '-x.txt')).exists()
    print('PASS: a forged hash for a root dot file (absolute path with one, two and three leading slashes, ./ and a ..'
        ' segment) cannot read, delete, overwrite or rename it, and the file survives; through two leading slashes the'
        ' real .htaccess, the carousel folder and the images root itself are hidden,'
        ' unreadable and locked, and /proc/self/root/... is not found (read commands only); the canonical root stays locked'
        + ('; in the copy, mkfile into a forged carousel is refused' if copy else ''), flush=True)

    # N1: a forged hash spelling a volume root (exactly two leading slashes, which
    # elFinder's _normpath keeps) unlocked it: rm removed the whole root. Checked on
    # a disposable media root selected with ?path=<number>, as a media-only user.
    media_id = str(int(prefix[-8:], 16))
    mroot = root / 'www/media' / media_id
    assert not mroot.exists()
    mroot.mkdir()
    (mroot / 'plain.txt').write_bytes(b'media plain must survive')
    (mroot / ('.' + prefix + '-m')).write_bytes(b'media dot must survive')
    (mroot / '.env').write_bytes(b'media letters dot must survive')
    mendpoint = endpoint + '&path=' + media_id
    msess = login(base, prefix + '-media')
    mcsrf = re.search(r'name="admin-csrf" content="([a-f0-9]{64})"', msess.get(base + '/admin/', timeout=15).text)[1]
    mopened = msess.get(mendpoint, params={'cmd': 'open', 'init': 1}, timeout=15).json()
    mvol = mopened['cwd']
    assert mvol.get('isroot') == 1 and mvol['name'] == media_id, mvol
    mhdr = {'X-CSRF-Token': mcsrf, 'X-elFinder-CSRF': mopened['csrf']}
    mpost = lambda data: msess.post(mendpoint, data=data, headers=mhdr, timeout=15)
    mabs = '/var/www/html/www/media/' + media_id
    mforged = lambda absolute: mvol['volumeid'] + base64.b64encode(absolute.encode()).decode().translate(
        str.maketrans('+/=', '-_.')).rstrip('.')
    assert mvol.get('locked') == 1 and refused(mpost({'cmd': 'rm', 'targets[]': mvol['hash']}))
    for spelling in ['/' + mabs, '/' + mabs + '/', '//' + mabs, '/proc/self/root' + mabs, '/' + mabs + '/x/..']:
        fh = mforged(spelling)
        info = msess.get(mendpoint, params={'cmd': 'info', 'targets[]': fh}, timeout=15).json().get('files', [])
        assert (info or spelling.startswith('/proc/')) and all(f.get('locked') == 1 and not f.get('write') for f in info), (spelling, info)
        assert refused(mpost({'cmd': 'rm', 'targets[]': fh})), spelling
        assert refused(mpost({'cmd': 'rename', 'target': fh, 'name': prefix + '-moved'})), spelling
        assert refused(mpost({'cmd': 'mkdir', 'target': fh, 'name': prefix + '-made'})), spelling
    for name in ['plain.txt', '.' + prefix + '-m']:
        fh = mforged('/' + mabs + '/' + name)
        served = msess.get(mendpoint, params={'cmd': 'file', 'target': fh}, timeout=15)
        assert served.status_code in (403, 404) and b'must survive' not in served.content, (name, served.status_code)
        assert refused(mpost({'cmd': 'rm', 'targets[]': fh})), name
    # archive of the whole (disposable) root is refused, nothing is written.
    assert refused(mpost({'cmd': 'archive', 'targets[]': mvol['hash'], 'type': 'application/zip'}))
    # The media root is read-only (mkfile and put are refused there), so duplicate is
    # too: of an ordinary file, a letters-only dot file, and the root itself (N8).
    for name in ['plain.txt', '.env', '.' + prefix + '-m']:
        assert refused(mpost({'cmd': 'duplicate', 'targets[]': target_hash(mvol, name)})), name
    assert refused(mpost({'cmd': 'duplicate', 'targets[]': mvol['hash']}))
    # (opening the root makes elFinder's .tmb folder there)
    assert sorted(p.name for p in mroot.iterdir() if p.name != '.tmb') == sorted(['plain.txt', '.env', '.' + prefix + '-m'])
    assert not (root / 'www/media' / (prefix + '-moved')).exists()
    assert (mroot / 'plain.txt').read_bytes() == b'media plain must survive'
    # The canonical spelling still reads (the control).
    plain = msess.get(mendpoint, params={'cmd': 'file', 'target': target_hash(mvol, 'plain.txt')}, timeout=15)
    assert plain.status_code == 200 and plain.content == b'media plain must survive', plain.status_code
    print('PASS: on a disposable media root, the canonical root hash is locked and its rm refused; a forged root hash'
        ' (two leading slashes, a trailing slash, three slashes, an x/.. segment) is locked and unwritable and'
        ' /proc/self/root/... is not found, and rm, rename and mkdir into each are refused; forged hashes of a file and a dot file inside are'
        ' unreadable and not removable; archive of the whole root is refused; duplicate of a file, a letters-only and a'
        ' hyphenated dot file and the root is refused; nothing created, moved or removed', flush=True)

    if copy:
        # In a disposable copy only, at the images root itself: the same forged root
        # rm/rename, archive of the whole root, and extract of an archive sitting in
        # the root (D5, N2), which is forced into a new folder (decision 3).
        for spelling in ['/' + docroot, '/proc/self/root' + docroot]:
            assert refused(ppost({'cmd': 'rm', 'targets[]': forged(spelling)})), spelling
            assert refused(ppost({'cmd': 'rename', 'target': forged(spelling), 'name': prefix + '-moved'})), spelling
        assert (root / 'www/images/.htaccess').exists() and not (root / 'www' / (prefix + '-moved')).exists()
        assert refused(ppost({'cmd': 'archive', 'targets[]': pimg['hash'], 'type': 'application/zip'}))
        assert not list((root / 'www/images').glob('*.zip')) and not list((root / 'www').glob('*.zip'))
        # duplicate at the images root (N8): a letters-only dot file, the copy's
        # .htaccess, the managed carousel and the root itself; nothing is created.
        root_letters = root / 'www/images/.env'
        assert not root_letters.exists()
        root_letters.write_bytes(b'root letters dot must survive')
        for rel in ['.env', '.htaccess', 'carousel']:
            assert refused(ppost({'cmd': 'duplicate', 'targets[]': target_hash(pimg, rel)})), rel
        assert refused(ppost({'cmd': 'duplicate', 'targets[]': pimg['hash']}))
        assert not [p.name for p in (root / 'www/images').iterdir() if ' copy' in p.name]
        assert root_letters.read_bytes() == b'root letters dot must survive'
        root_letters.unlink()
        in_app('sh', '-c', "cd {d} && mkdir -p {p}/X/carousel && echo ex > {p}/X/carousel/{p}-ex.txt".format(d=docroot, p=prefix))
        xcar = target_hash(pimg, prefix + '/X/carousel')
        arc = ppost({'cmd': 'archive', 'targets[]': xcar, 'type': 'application/zip'}).json()
        zh = next(f['hash'] for f in arc['added'] if f['name'] == 'carousel.zip')
        assert ppost({'cmd': 'paste', 'dst': pimg['hash'], 'targets[]': zh, 'cut': '1'}).json().get('added')
        for spelling in ['/' + docroot + '/carousel.zip', '/proc/self/root' + docroot + '/carousel.zip']:
            assert refused(ppost({'cmd': 'extract', 'target': forged(spelling), 'makedir': '0'})), spelling
        canonical = ppost({'cmd': 'extract', 'target': target_hash(pimg, 'carousel.zip'), 'makedir': '0'}).json()
        made = [f['name'] for f in canonical.get('added', [])]
        assert len(made) == 1 and made[0].startswith('carousel-'), canonical
        assert mode('www/images/' + made[0]) == '755', mode('www/images/' + made[0])
        assert not (root / 'www/images/carousel' / (prefix + '-ex.txt')).exists(), 'extract merged into managed carousel'
        assert refused(ppost({'cmd': 'upload', 'target': pimg['hash'], 'upload_path[]': target_hash(pimg, 'carousel')},
            files={'upload[]': (prefix + '-up.txt', b'x', 'text/plain')}))
        assert not (root / 'www/images/carousel' / (prefix + '-up.txt')).exists()
        print('PASS (copy): at the images root, a forged root hash cannot be removed or renamed and archive of the whole'
            ' root is refused; duplicate of a letters-only dot file, .htaccess, carousel and the root is refused with'
            ' nothing created; extract of an archive in the root with a forged target is refused and with the canonical'
            ' one goes into a new 0755 folder, not into the managed carousel; upload_path[] into carousel is refused', flush=True)

    # D6: archive and zipdl follow a symlink out of the root, so a tree with one is refused.
    in_app('sh', '-c', "cd {d} && mkdir -p {p}/L && echo real > {p}/L/real.txt && ln -s /etc {p}/L/etclink".format(d=docroot, p=prefix))
    lh = target_hash(pimg, prefix + '/L')
    assert refused(ppost({'cmd': 'archive', 'targets[]': lh, 'type': 'application/zip'}))
    assert refused(ppost({'cmd': 'zipdl', 'targets[]': lh}))
    assert not list((root / 'www/images' / prefix / 'L').glob('*.zip'))
    in_app('sh', '-c', "rm -f {d}/{p}/L/etclink".format(d=docroot, p=prefix))
    print('PASS: archive and zipdl refuse a tree containing a symlink (no out-of-root content leaks)', flush=True)

    # D7: an unreadable subfolder gives a refusal, not a 500 and no exception.log line.
    exc = root / 'www/log/exception.log'
    before_sz = exc.stat().st_size if exc.exists() else 0
    in_app('sh', '-c', "cd {d} && mkdir -p {p}/U/nope && echo x > {p}/U/a.txt && chmod 000 {p}/U/nope".format(d=docroot, p=prefix))
    uh = target_hash(pimg, prefix + '/U')
    r = ppost({'cmd': 'archive', 'targets[]': uh, 'type': 'application/zip'})
    assert refused(r) and r.status_code == 200
    new = exc.read_bytes()[before_sz:] if (exc.exists() and exc.stat().st_size > before_sz) else b''
    assert b'RecursiveDirectoryIterator' not in new and b'ElfinderControl' not in new, new[-200:]
    in_app('sh', '-c', "chmod 755 {d}/{p}/U/nope".format(d=docroot, p=prefix))
    print('PASS: an unreadable subfolder makes archive refuse (200 warning) with no RecursiveDirectoryIterator 500', flush=True)
finally:
    in_app('sh', '-c', "chmod -R u+rwx {d}/{p} 2>/dev/null; rm -f {d}/{p}/L/etclink 2>/dev/null; true".format(d=docroot, p=prefix))
    media_left = root / 'www/media' / str(int(prefix[-8:], 16))
    if media_left.exists():
        assert media_left.parent == (root / 'www/media').resolve() and media_left.name.isdigit()
        in_app('rm', '-rf', 'www/media/' + media_left.name)
    if pfolder.exists():
        assert pfolder.parent == (root / 'www/images').resolve() and pfolder.name == prefix
        in_app('rm', '-rf', 'www/images/' + prefix)
    if rootdot.exists():
        rootdot.unlink()
    # Only the copy's images root is ever written at root level.
    for p in list((root / 'www/images').glob('carousel-*')) + list((root / 'www/images').glob('* copy*')) if copy else []:
        if p.is_dir():
            in_app('rm', '-rf', 'www/images/' + p.name)
        elif p.is_file():
            p.unlink()
    if copy and (root / 'www/images/.env').is_file():
        (root / 'www/images/.env').unlink()
    cz = root / 'www/images/carousel.zip'
    if copy and cz.exists():
        cz.unlink()
    assert hashlib.sha256(htaccess.read_bytes()).hexdigest() == htaccess_sha
