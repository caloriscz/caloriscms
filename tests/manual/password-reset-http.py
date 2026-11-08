"""Local HTTP checks with a disposable resettest account; never sends mail."""
import html
import re
import sys
import requests

BASE = 'http://localhost:8090'
TOKEN = sys.argv[1]

def form(session):
    response = session.get(BASE + '/admin/sign/resetpass/', timeout=15)
    assert response.status_code == 200 and 'Server Error' not in response.text
    assert response.headers['Cache-Control'] == 'no-store'
    assert response.headers['Referrer-Policy'] == 'no-referrer'
    body = re.search(r'<form[^>]+id="frm-resetPass-resetForm"[\s\S]*?</form>', response.text).group()
    return html.unescape(re.search(r'name="_token_" value="([^"]+)"', body).group(1))

session = requests.Session()
csrf = form(session)
other = requests.Session()
other_csrf = form(other)
base_data = {'_do': 'resetPass-resetForm-submit', 'resetToken': TOKEN,
             'password': 'AfterReset1!', 'password2': 'AfterReset1!', 'name': 'Změnit'}
for token in ['', 'wrong', other_csrf]:
    response = session.post(BASE + '/admin/sign/resetpass/', data={**base_data, '_token_': token}, timeout=15)
    assert '/sign/resetpass/' in response.url and 'Server Error' not in response.text
    assert response.status_code == 403, 'Expected early form-token rejection'
print('Missing, invalid and other-session CSRF tokens rejected')
for changes in [{'password2': 'Mismatch1!'}, {'password': 'short', 'password2': 'short'}, {'resetToken': '18.legacy'}]:
    response = session.post(BASE + '/admin/sign/resetpass/', data={**base_data, '_token_': csrf, **changes}, timeout=15)
    assert '/sign/resetpass/' in response.url and 'Server Error' not in response.text
print('Mismatch, short password and invalid reset credential rejected')
response = session.post(BASE + '/admin/sign/resetpass/', data={**base_data, '_token_': csrf}, timeout=15)
assert '/admin/sign/in/' in response.url and 'Server Error' not in response.text
assert TOKEN not in response.url and 'AfterReset1!' not in response.url
csrf = form(session)
response = session.post(BASE + '/admin/sign/resetpass/', data={**base_data, '_token_': csrf}, timeout=15)
assert '/sign/resetpass/' in response.url and 'Odkaz je neplatný' in response.text
print('Valid reset redirects to login; token replay rejected')
