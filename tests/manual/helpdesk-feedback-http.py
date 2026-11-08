"""Local HTTP check of public Helpdesk feedback after the redirect; no browser claim.

Submits the rendered /kontakt form logged out, follows the redirect and expects
the Helpdesk alert once. Local Docker cannot deliver mail, so a valid submit
ends with the failure alert. Also checks that new log lines carry no submitted
name, e-mail or message. Usage: python helpdesk-feedback-http.py
"""
import pathlib
import secrets
from urllib.parse import urljoin, urlsplit
import requests
from http_helpers import forms

base = 'http://localhost:8090'
logs = pathlib.Path(__file__).resolve().parents[2] / 'www' / 'log'
marker = 'fix_' + secrets.token_hex(4)
sizes = {p: p.stat().st_size for p in logs.iterdir() if p.is_file()}

def submit(fields):
    session = requests.Session()
    page = session.get(base + '/kontakt', timeout=15)
    assert page.status_code == 200, page.status_code
    form = next(f for f in forms(page.text) if f.get('id') == 'frm-pageContact-helpdesk-sendForm')
    response = session.post(urljoin(base, form['action']), data={**form['inputs'], **fields},
        allow_redirects=False, timeout=15)
    assert response.status_code in (302, 303), response.status_code
    target = urlsplit(urljoin(base, response.headers['Location']))
    # Internal return page only, with the flash key carried along.
    assert (target.scheme, target.netloc) == ('http', 'localhost:8090'), response.headers['Location']
    assert not target.path.startswith('//') and '_fid=' in target.query, response.headers['Location']
    landing = session.get(target.geturl(), timeout=15)
    reload = session.get(target.geturl(), timeout=15)
    return landing.text, reload.text

valid = {'name': marker, 'email': marker + '@example.invalid', 'phone': '', 'message': marker + ' message body'}
for fields, alert in [(valid, 'E-mail nebyl odeslán'), ({**valid, 'name': ''}, 'Vyplňte jméno')]:
    landing, reload = submit(fields)
    assert 'alert alert-danger' in landing and alert in landing, alert
    assert alert not in reload and 'alert alert-danger' not in reload, 'feedback repeated on reload'
    print('PASS: "' + alert + '" shown once after the redirect, gone on reload', flush=True)

for path in logs.iterdir():
    if path.is_file():
        with path.open('rb') as handle:
            handle.seek(sizes.get(path, 0))
            assert marker.encode() not in handle.read(), path.name
print('PASS: new log entries contain no submitted name, e-mail or message', flush=True)
