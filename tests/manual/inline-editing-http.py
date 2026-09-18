"""Local Docker HTTP integration, with automatic cleanup of test-owned data."""
import json
import re
import subprocess
import sys
import requests
from http_helpers import login as protected_login

prefix = sys.argv[1]
base = 'http://localhost:8090'

def fixture(mode):
    result = subprocess.check_output(['docker', 'compose', 'exec', '-T', 'app', 'php',
        'tests/manual/inline-editing-fixture.php', mode, prefix], text=True)
    return json.loads(result) if result else None

def login(role):
    return protected_login(base, prefix + '-' + role)

try:
    ids = fixture('create')
    url = base + '/' + prefix + '/'
    original = fixture('read')
    editor = login('pages')
    page = editor.get(url, timeout=15)
    assert page.status_code == 200, page.status_code
    token = re.search(r'name="inline-csrf" content="([a-f0-9]{64})"', page.text)[1]
    assert 'contenteditable="true"' in page.text
    assert page.headers['Cache-Control'] == 'private, no-store'
    other = login('pages')
    other_token = re.search(r'name="inline-csrf" content="([a-f0-9]{64})"', other.get(url, timeout=15).text)[1]
    anonymous, denied = requests.Session(), login('denied')
    for client in [anonymous, denied]:
        body = client.get(url, timeout=15).text
        assert 'inline-csrf' not in body and 'contenteditable="true"' not in body
    for signal, field, record_id in [('pagetitle', 'editorId', ids['page']), ('snippet', 'snippetId', ids['snippet'])]:
        data = {'_do': signal, field: record_id, 'text': 'Forbidden', '_csrf': token}
        for client, csrf in [(anonymous, token), (denied, token), (editor, ''), (editor, 'wrong'), (editor, other_token)]:
            response = client.post(url, data={**data, '_csrf': csrf}, headers={'X-Requested-With': 'XMLHttpRequest'}, timeout=15)
            assert response.status_code == 403, (signal, response.status_code)
        # Direct fallback route dispatches GET; slug route preserves query-do exclusion.
        response = editor.get(base + '/homepage/default/', params={'do': signal, field: record_id,
            'text': 'Forbidden', '_csrf': token}, timeout=15)
        assert response.status_code == 403, response.status_code
        for mode in ['disable', 'missingrole', 'revoke']:
            fixture(mode)
            response = editor.post(url, data=data, timeout=15)
            assert response.status_code == 403, (mode, response.status_code)
            fixture('enable')
        assert fixture('read') == original
    title = 'Žluťoučký & + "quoted" = 100%'
    response = editor.post(url, data={'_do': 'pagetitle', '_csrf': token, 'editorId': ids['page'], 'text': title},
                           headers={'X-Requested-With': 'XMLHttpRequest'}, timeout=15)
    assert response.status_code == 200 and response.json() == {'saved': True, 'content': title}, response.text[:200]
    response = editor.post(url, data={'_do': 'snippet', '_csrf': token, 'snippetId': ids['snippet'],
        'text': '<strong>Safe &amp; sound</strong><script>bad()</script><img src="x" onerror="bad()">'},
        headers={'X-Requested-With': 'XMLHttpRequest'}, timeout=15)
    assert response.status_code == 200 and response.json()['saved'] is True
    saved = fixture('read')
    assert saved['title'] == title and '<strong>Safe &amp; sound</strong>' in saved['snippet']
    assert '<script' not in saved['snippet'] and 'onerror' not in saved['snippet']
    for invalid in ['', '<b>markup</b>', 'x' * 251]:
        response = editor.post(url, data={'_do': 'pagetitle', '_csrf': token, 'editorId': ids['page'], 'text': invalid}, timeout=15)
        assert response.status_code == 400
    assert fixture('read') == saved
    assert 'Žluťoučký &amp; +' in editor.get(url, timeout=15).text
    print('PASS: anonymous/restricted/disabled/missing-role/revoked/GET/invalid-token no-write checks; safe AJAX saves and special characters')
finally:
    fixture('cleanup')
