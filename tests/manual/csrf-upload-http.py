"""Disposable upload and file-manager CSRF boundary checks; no browser claims."""
import base64
import pathlib
import re
import sys
import requests
from http_helpers import forms, login

prefix = sys.argv[1]
assert re.fullmatch(r'permtest-[a-f0-9]{8}', prefix)
base = 'http://localhost:8090'
root = pathlib.Path(__file__).resolve().parents[2] / 'www' / 'images'
target_file = root / (prefix + '.png')
target_dir = root / prefix
assert not target_file.exists() and not target_dir.exists(), 'Fixture already exists'
png = base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aCr8AAAAASUVORK5CYII=')
session = login(base, prefix + '-full')
page = session.get(base + '/admin/files/', timeout=15)
assert page.status_code == 200
form = next(f for f in forms(page.text) if f['inputs'].get('_do') == 'dropUploadFiles-dropUploadForm-submit')
csrf = re.search(r'name="admin-csrf" content="([a-f0-9]{64})"', page.text)[1]
try:
    for invalid in ['', 'wrong']:
        response = session.post(base + '/admin/files/', data={**form['inputs'], '_token_': invalid},
            files={'file': (prefix + '.png', png, 'image/png')}, timeout=15)
        assert response.status_code == 403 and not target_file.exists()
    response = session.post(base + '/admin/files/', data=form['inputs'],
        files={'file': (prefix + '.png', png, 'image/png')}, timeout=15)
    assert response.status_code == 200 and target_file.read_bytes() == png
    # Use the real read endpoint to discover the permitted images root.
    endpoint = base + '/admin/?do=elfinder-options'
    opened = session.get(endpoint, params={'cmd': 'open', 'init': 1, 'tree': 1}, timeout=15)
    assert opened.status_code == 200
    roots = opened.json()['files']
    images = next(f for f in roots if f.get('name') == 'images' and f.get('isroot'))
    data = {'cmd': 'mkdir', 'target': images['hash'], 'name': prefix}
    denied = session.post(endpoint, data=data, timeout=15)
    assert denied.status_code == 403 and not target_dir.exists(), (denied.status_code, denied.url, denied.text[:160])
    headers = {'X-CSRF-Token': csrf, 'X-elFinder-CSRF': opened.json()['csrf']}
    created = session.post(endpoint, data=data, headers=headers, timeout=15)
    assert created.status_code == 200 and target_dir.is_dir(), created.text[:160]
    folder = created.json()['added'][0]['hash']
    removed = session.post(endpoint, data={'cmd': 'rm', 'targets[]': folder}, headers=headers, timeout=15)
    assert removed.status_code == 200 and not target_dir.exists()
    print('PASS: upload rejects bad tokens; valid multipart upload; elFinder reads and protected mkdir/remove')
finally:
    # Exact prefix-owned targets only; never remove a populated directory recursively.
    if target_file.exists(): target_file.unlink()
    if target_dir.exists(): target_dir.rmdir()
