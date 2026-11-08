"""Local integration regression for Nette/connector command parsing; no browser claim."""
import base64
import pathlib
import re
import sys
from urllib.parse import urlencode
from http_helpers import login

prefix = sys.argv[1]
assert re.fullmatch(r'permtest-[a-f0-9]{8}', prefix)
base = 'http://localhost:8090'
# 1x1 transparent PNG; the images root only accepts image or text uploads.
PNG = base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==')
target =pathlib.Path(__file__).resolve().parents[2] / 'www' / 'images' / prefix
assert not target.exists(), 'Fixture already exists'
session = login(base, prefix + '-full')
page = session.get(base + '/admin/', timeout=15)
assert page.status_code == 200
csrf = re.search(r'name="admin-csrf" content="([a-f0-9]{64})"', page.text)[1]
endpoint = base + '/admin/?do=elfinder-options'
opened = session.get(endpoint, params={'cmd': 'open', 'init': 1, 'tree': 1}, timeout=15)
assert opened.status_code == 200, opened.text[:160]
payload = opened.json()
images = next(f for f in payload['files'] if f.get('name') == 'images' and f.get('isroot'))
connector = {'X-elFinder-CSRF': payload['csrf']}
headers = {**connector, 'X-CSRF-Token': csrf}
try:
    # Read-only POSTs still need the CMS token. GET/HEAD download/read paths survive.
    for token in [None, 'wrong']:
        h = connector if token is None else {**connector, 'X-CSRF-Token': token}
        response = session.post(endpoint, data={'cmd': 'open', 'target': images['hash']}, headers=h, timeout=15)
        assert response.status_code == 403
    response = session.post(endpoint, data={'cmd': 'open', 'target': images['hash']}, headers=headers, timeout=15)
    assert response.status_code == 200 and 'files' in response.json()
    assert session.head(endpoint, params={'cmd': 'open', 'target': images['hash']}, timeout=15).status_code == 200

    # With the real connector token supplied, these must be stopped by the CMS.
    # The old CMS read exception saw query cmd=open; elFinder reparsed the raw
    # body (targets + >max_input_vars pairs) and instead executed mkdir.
    create = {'cmd': 'mkdir', 'target': images['hash'], 'name': prefix}
    raw = urlencode(create) + '&' + urlencode({'padding' + str(i): '0' for i in range(1100)})
    for method in ['POST', 'GET']:
        response = session.request(method, endpoint, params={'cmd': 'open', 'targets[]': 'decoy'},
            data=raw, headers={**connector, 'Content-Type': 'text/plain'}, timeout=15)
        assert response.status_code == 403 and not target.exists(), (method, response.status_code)
    # Even a small GET body must never use the read exception.
    response = session.get(endpoint, params={'cmd': 'open'}, data='cmd=mkdir', headers=connector, timeout=15)
    assert response.status_code == 403 and not target.exists()
    response = session.get(endpoint, params={'cmd': 'mkdir', 'target': images['hash'], 'name': prefix}, headers=headers, timeout=15)
    assert response.status_code == 403 and not target.exists()

    created = session.post(endpoint, data=create, headers=headers, timeout=15)
    assert created.status_code == 200 and target.is_dir(), created.text[:160]
    folder = created.json()['added'][0]['hash']

    # Upload as each client transport sends it. The iframe transport posts a
    # hidden form with customData only, so it carries neither token header.
    upload = {'cmd': 'upload', 'target': folder}
    files = lambda: {'upload[]': ('fix.png', PNG, 'image/png')}
    response = session.post(endpoint, data=upload, files=files(), timeout=15)
    assert response.status_code == 403 and not (target / 'fix.png').exists()
    # The XHR transport the admin dialog selects sends every customHeader.
    first = session.post(endpoint, data=upload, files=files(), headers=headers, timeout=15)
    assert first.status_code == 200 and first.json().get('added'), first.text[:160]
    second = session.post(endpoint, data=upload, files=files(), headers=headers, timeout=15)
    assert [f['name'] for f in second.json().get('added', [])] == ['fix-1.png'], second.text[:160]
    assert (target / 'fix.png').read_bytes() == PNG and (target / 'fix-1.png').read_bytes() == PNG

    removed = session.post(endpoint, data={'cmd': 'rm', 'targets[]': folder}, headers=headers, timeout=15)
    assert removed.status_code == 200 and not target.exists(), removed.text[:160]
    print('PASS: CMS blocks raw-body command substitution with valid connector token; header-less iframe-style upload denied; '
        'XHR-style upload works and a duplicate name becomes fix-1.png; GET/HEAD reads and authorized POST open/mkdir/rm work')
finally:
    # Only this test's directory and the two files it uploads; never recursively
    # remove runtime data.
    if target.exists():
        for name in ['fix.png', 'fix-1.png']:
            if (target / name).exists():
                (target / name).unlink()
        target.rmdir()
