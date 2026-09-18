"""Controlled error checks against local Docker using a disposable denied role."""
import pathlib
import sys
import requests
from http_helpers import login as protected_login

base = 'http://localhost:8090'
root = pathlib.Path(__file__).resolve().parents[2]
exception_log = root / 'www/log/exception.log'
before = exception_log.stat().st_size if exception_log.exists() else 0
session = protected_login(base, sys.argv[1] + '-denied')
for path, status, title in [('/admin/pages/', 403, 'Access Denied'),
                            ('/audit-missing-page-97fc2a75', 404, 'Page Not Found')]:
    result = session.get(base + path, timeout=15)
    assert result.status_code == status
    assert title in result.text and 'Server Error' not in result.text
    assert result.headers['Cache-Control'] == 'no-store'
    ajax = session.post(base + path, headers={'X-Requested-With': 'XMLHttpRequest'},
                        data={'_do': 'pageList-delete', 'password': 'audit-secret-must-not-log', 'id': '0'}, timeout=15)
    assert ajax.status_code == status and ajax.json() == {'error': True, 'code': status}
    print(f'{status}: complete HTML and AJAX JSON responses passed')
result = session.options(base + '/admin/sign/in/', timeout=15)
assert result.status_code == 405 and 'Method Not Allowed' in result.text
assert 'Allow' in result.headers
for path in ['/', '/admin/sign/in/', '/dokumenty/', '/galerie/']:
    result = session.get(base + path, timeout=15)
    assert result.status_code == 200 and 'Server Error' not in result.text
with exception_log.open('rb') as stream:
    stream.seek(before)
    assert not stream.read(), 'New exception logged during error response checks'
access = (root / 'www/log/access.log').read_text(encoding='utf-8')
assert '"presenter":"Admin:Pages"' in access
assert '"signal":"pageList-delete"' in access
assert 'audit-secret-must-not-log' not in access
print('405, success smokes, contextual logging and no secondary exceptions passed')
