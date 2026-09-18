"""Remaining CSRF checks with disposable local records and real rendered tokens."""
import json
import re
import subprocess
import sys
from urllib.parse import urljoin, urlsplit
import requests
from http_helpers import forms, login

prefix = sys.argv[1]
base = 'http://localhost:8090'

def fixture(mode):
    output = subprocess.check_output(['docker', 'compose', 'exec', '-T', 'app', 'php',
        'tests/manual/csrf-fixture.php', mode, prefix], text=True)
    return json.loads(output) if output else None

def token(session):
    page = session.get(base + '/admin/', timeout=15)
    assert page.status_code == 200
    return re.search(r'name="admin-csrf" content="([a-f0-9]{64})"', page.text)[1]

def check_forms(body):
    found = forms(body)
    for form in found:
        if form.get('method', 'get').lower() == 'post':
            assert '_token_' in form['inputs'] or '_csrf' in form['inputs'], form.get('id', form.get('action'))
    return found

try:
    ids = fixture('create')
    session = login(base, prefix + '-full')
    other = login(base, prefix + '-full')
    csrf, other_csrf = token(session), token(other)
    original = fixture('snapshot')
    signals = [
        ('menu/', 'menuEditor-delete', {'node_id': ids['menu']}),
        ('menu/', 'menuEditor-rename', {'node_id': ids['menu'], 'text': 'forged'}),
        ('menu/', 'menuEditor-sort', {'id_from': ids['menu'], 'id_to': '', 'position': 0}),
        ('menu/', 'menuEditor-create', {'node_id': ids['menu'], 'menu': ids['menu_menus']}),
        ('menu/', 'deleteImage', {'identifier': ids['menu']}),
        ('contacts/', 'contactGrid-delete', {'contactGrid-id': ids['contacts']}),
        ('contacts/', 'deleteCategory', {'id': ids['contacts_categories']}),
        ('links/default/' + str(ids['links']) + '/', 'delete', {}),
        ('links/', 'categoryPanel-rename', {'node_id': ids['links_categories'], 'text': 'forged'}),
        ('links/', 'categoryPanel-sort', {'id_from': ids['links_categories'], 'position': 0}),
        ('snippets/default/' + str(ids['snippets']) + '/', 'delete', {}),
        ('helpdesk/', 'delete', {'identifier': ids['helpdesk_messages']}),
        ('helpdesk/', 'deleteTemplate', {'identifier': ids['helpdesk']}),
        ('appearance/', 'carouselManager-delete', {'carouselManager-id': ids['carousel']}),
        ('appearance/', 'carouselManager-images', {'sortable': ids['carousel']}),
        ('', 'elfinder-options', {'cmd': 'mkdir', 'name': prefix}),
    ]
    for route, signal, params in signals:
        url = base + '/admin/' + route
        response = session.get(url, params={**params, 'do': signal, '_csrf': csrf}, timeout=15)
        assert response.status_code == 403, (signal, 'GET', response.status_code)
        for bad in ['', 'bad', other_csrf]:
            response = session.post(url, params=params, data={'_do': signal, '_csrf': bad},
                headers={'X-Requested-With': 'XMLHttpRequest'}, timeout=15)
            assert response.status_code == 403, (signal, response.status_code)
    assert fixture('snapshot') == original, 'Rejected signals changed database state'
    print('PASS: 16 signal paths reject GET and missing/wrong/other-session tokens without writes', flush=True)

    cases = [
        ('menu/detail/' + str(ids['menu']) + '/', 'menuEdit-editForm-submit', 'title', 'menu', 'title'),
        ('contacts/detail/' + str(ids['contacts']) + '/', 'editContact-editForm-submit', 'name', 'contacts', 'name'),
        ('links/detail/' + str(ids['links']) + '/', 'editForm-submit', 'title', 'links', 'title'),
        ('snippets/detail/' + str(ids['snippets']) + '/', 'editSnippetForm-editSnippetForm-submit', 'content', 'snippets', 'content'),
        ('helpdesk/emails/' + str(ids['helpdesk']) + '/', 'editMailTemplate-editForm-submit', 'subject', 'helpdesk', 'subject'),
        ('appearance/carousel-detail/' + str(ids['carousel']) + '/', 'editFormCarousel-editForm-submit', 'title', 'carousel', 'title'),
        ('profile/', 'editProfile-editForm-submit', 'name', None, None),
    ]
    for route, signal, field, table, column in cases:
        url = base + '/admin/' + route
        page = session.get(url, timeout=15)
        assert page.status_code == 200, (route, page.status_code)
        found = check_forms(page.text)
        form = next(f for f in found if f['inputs'].get('_do') == signal)
        other_form = next(f for f in forms(other.get(url, timeout=15).text) if f['inputs'].get('_do') == signal)
        initial = fixture('snapshot')
        data = {**form['inputs'], field: prefix + ' & + updated ' + str(ids['menu'])}
        action = urljoin(url, form['action'])
        for bad in ['', 'bad', other_form['inputs']['_token_']]:
            response = session.post(action, data={**data, '_token_': bad}, files={'the_file': ('', b'')}, timeout=15)
            assert response.status_code in (200, 403), (signal, 'invalid', response.status_code)
            assert fixture('snapshot') == initial, (signal, 'invalid token wrote data')
        response = session.post(action, data=data, files={'the_file': ('', b'')}, allow_redirects=False, timeout=15)
        assert response.status_code in (200, 302, 303), (signal, 'valid', response.status_code)
        if table:
            assert fixture('read')[table][column] == data[field], (signal, 'valid form did not save')
        else:
            assert fixture('snapshot')['users'] != initial['users']
        print('PASS: ' + signal + ' rejects bad tokens and accepts valid form', flush=True)

    # AJAX rename uses the same URL parameters and POST token as the tree client.
    name = prefix + ' & + tree'
    response = session.post(base + '/admin/menu/', params={'do': 'menuEditor-rename', 'node_id': ids['menu'], 'text': name},
        data={'_csrf': csrf}, headers={'X-Requested-With': 'XMLHttpRequest'}, timeout=15)
    assert response.status_code == 200 and fixture('read')['menu']['title'] == name
    # A rendered POST button works without JavaScript and removes only its fixture.
    page = session.get(base + '/admin/snippets/', timeout=15)
    delete = next(f for f in check_forms(page.text) if urlsplit(f.get('action', '')).path.rstrip('/').endswith('/' + str(ids['snippets']))
                  and 'do=delete' in f.get('action', ''))
    response = session.post(urljoin(base, delete['action']), data=delete['inputs'], allow_redirects=False, timeout=15)
    assert response.status_code in (302, 303) and fixture('read')['snippets'] is False
    # Logout is a POST action too; GET must keep the session authenticated.
    assert session.get(base + '/admin/sign/out/', timeout=15).status_code == 403
    assert session.get(base + '/admin/', timeout=15).status_code == 200
    response = session.post(base + '/admin/sign/out/', data={'_csrf': csrf}, timeout=15)
    assert '/sign/in' in response.url
    print('PASS: AJAX tree rename, rendered delete form and protected logout', flush=True)
finally:
    fixture('cleanup')
