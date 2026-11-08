"""Local admin form checks using the disposable permission fixture, no mail."""
import html
import re
import sys
import requests
from http_helpers import login as protected_login

BASE = 'http://localhost:8090'
prefix = sys.argv[1]

def login(role):
    return protected_login(BASE, prefix + '-' + role)

session = login('full')
response = session.get(BASE + '/admin/members/', timeout=15)
assert 'Server Error' not in response.text
body = re.search(r'<form[^>]+id="frm-insertMember-insertForm"[\s\S]*?</form>', response.text).group()
csrf = html.unescape(re.search(r'name="_token_" value="([^"]+)"', body).group(1))
data = {'username': prefix + '-created', 'email': prefix + '-created@example.invalid',
        '_do': 'insertMember-insertForm-submit'}
response = session.post(BASE + '/admin/members/', data=data, timeout=15)
assert '/members/edit/' not in response.url and 'Server Error' not in response.text
assert login('denied').post(BASE + '/admin/members/', data={**data, '_token_': csrf}, timeout=15).status_code == 403
response = session.post(BASE + '/admin/members/', data={**data, '_token_': csrf}, timeout=15)
assert '/members/edit/' in response.url and 'Server Error' not in response.text
assert 'pdd=' not in response.url and 'pwd=' not in response.url
assert 'Odeslat odkaz pro nastavení hesla' in response.text
body = re.search(r'<form[^>]+id="frm-sendLogin-sendLoginForm"[\s\S]*?</form>', response.text).group()
assert 'name="_token_"' in body
assert 'name="sendmail"' not in body
response = session.post(response.url, data={'_do': 'sendLogin-sendLoginForm-submit', 'contact_id': '0'}, timeout=15)
assert 'Server Error' not in response.text
print('Member creation rejects missing CSRF and denied roles; valid creation and protected resend form render; no mail sent')
