"""Actual multipart uploads and page-owned file deletion; disposable local fixtures."""
import json
import pathlib
import re
import struct
import subprocess
import sys
import zlib
from http_helpers import login, forms

account, prefix = sys.argv[1:3]
base = 'http://localhost:8090'
root = pathlib.Path(__file__).resolve().parents[2] / 'www'
def fixture(mode):
    out = subprocess.check_output(['docker', 'compose', 'exec', '-T', 'app', 'php', 'tests/manual/media-fixture.php', mode, prefix], text=True)
    return json.loads(out) if mode != 'cleanup' else None
def chunk(kind, data):
    return struct.pack('!I', len(data)) + kind + data + struct.pack('!I', zlib.crc32(kind + data))
png = b'\x89PNG\r\n\x1a\n' + chunk(b'IHDR', struct.pack('!2I5B', 1, 1, 8, 2, 0, 0, 0)) + chunk(b'IDAT', zlib.compress(b'\x00\xff\x00\x00')) + chunk(b'IEND', b'')
ids = fixture('create')
try:
    session = login(base, account + '-full')
    pid = ids['id']
    for table, route, signal, content, name in [
        ('media', 'detail-files', 'dropZoneMedia-dropForm-submit', b'file contents', 'sample.txt'),
        ('pictures', 'detail-images', 'dropZonePictures-dropUploadForm-submit', png, 'sample.png')]:
        url = f'{base}/admin/pages/{route}/{pid}/'
        page = session.get(url, timeout=15)
        assert page.status_code == 200, (route, page.status_code)
        form = next(f for f in forms(page.text) if f['inputs'].get('_do') == signal)
        token = re.search(r'name="admin-csrf" content="([a-f0-9]{64})"', page.text)[1]
        response = session.post(url, data={**form['inputs'], '_token_': 'bad'}, files={'file': (name, content)}, timeout=15)
        assert response.status_code == 403 and not fixture('read')[table]
        response = session.post(url, data=form['inputs'], files={'file': (name, content)}, timeout=15)
        assert response.status_code == 200 and response.json()['saved'], (route, response.status_code, response.text[:120])
        path = root / table / str(pid) / name
        assert path.read_bytes() == content
        records = fixture('read')[table]
        assert len(records) == 1 and records[0]['filesize'] == len(content)
        duplicate = session.post(url, data=form['inputs'], files={'file': (name, content)}, timeout=15)
        assert duplicate.status_code == 409 and path.read_bytes() == content
        if table == 'pictures':
            assert (path.parent / 'tn' / name).is_file()
            # The real protected individual delete uses the captured row/page ID.
            deleted = session.post(url, params={'do': 'imageBrowser-delete', 'imageBrowser-id': records[0]['id']}, data={'_csrf': token}, timeout=15)
            assert deleted.status_code == 200 and not path.exists() and not fixture('read')[table]
            assert session.post(url, data=form['inputs'], files={'file': (name, content)}, timeout=15).status_code == 200
    deleted = session.post(base + '/admin/pages/', params={'do': 'pageList-delete', 'pageList-id': pid}, data={'_csrf': token}, timeout=15)
    state = fixture('read')
    assert deleted.status_code == 200, deleted.status_code
    assert not state['page'] and not state['media'] and not state['pictures']
    assert not state['media_dir'] and not state['pictures_dir']
    print('PASS: real media/picture upload, CSRF rejection, duplicate conflict, thumbnail, individual and page deletion')
finally:
    fixture('cleanup')
