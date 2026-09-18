"""Local HTTP permission matrix. Run denied, check fixture, then allowed phases."""
import sys
import re
import requests
from http_helpers import login as protected_login

prefix, phase, raw_ids = sys.argv[1:]
assert phase in ('denied', 'allowed')
ids = [int(value) for value in raw_ids.split(',')]
assert len(ids) == 6 and all(value > 0 for value in ids)
base = 'http://localhost:8090'
sections = [
    ('menu', 'menu', 'menuEditor-delete', 'node_id', 'menuEditor-menuInsert-insertForm-submit'),
    ('contacts', 'contacts', 'contactGrid-delete', 'contactGrid-id', 'editContact-editForm-submit'),
    ('links', 'pages', 'delete', 'id', 'editForm-submit'),
    ('snippets', 'pages', 'delete', 'id', 'editSnippetForm-editSnippetForm-submit'),
    ('helpdesk', 'helpdesk', 'delete', 'identifier', 'editHelpdeskEmailSettings-editForm-submit'),
    ('appearance', 'appearance', 'carouselManager-delete', 'carouselManager-id', 'editFormCarousel-editForm-submit'),
]

def login(role):
    return protected_login(base, prefix + '-' + role)

if phase == 'denied':
    for role in ['denied', 'pages', 'menu', 'contacts', 'helpdesk', 'appearance']:
        session = login(role)
        for (section, grant, signal, parameter, form), record_id in zip(sections, ids):
            if role == grant:
                continue
            url = base + '/admin/' + section + '/'
            result = session.get(url, timeout=15)
            assert result.status_code == 403, (role, section, 'GET', result.status_code)
            for method, ajax in [('get', False), ('post', False), ('post', True)]:
                mutation_url = url + 'default/' + str(record_id) + '/' if parameter == 'id' else url
                params = {} if parameter == 'id' else {parameter: record_id}
                kwargs = {'params': params, 'allow_redirects': False, 'timeout': 15}
                if method == 'get':
                    params['do'] = signal
                else:
                    kwargs['data'] = {'_do': signal}
                if ajax:
                    kwargs['headers'] = {'X-Requested-With': 'XMLHttpRequest'}
                result = getattr(session, method)(mutation_url, **kwargs)
                assert result.status_code == 403, (role, section, method, result.status_code)
                if ajax:
                    assert result.json() == {'error': True, 'code': 403}
            # A nested form sent on default instead of its normal detail action.
            result = session.post(url + 'default/' + str(record_id) + '/', data={
                '_do': form, 'id': record_id, 'title': 'unauthorized-change',
                'content': 'unauthorized-change'}, timeout=15)
            assert result.status_code == 403, (role, section, 'form', result.status_code)
        assert session.get(base + '/admin/profile/', timeout=15).status_code == 200
    print('Independent denied grants: GET, POST, AJAX and nested forms passed')
else:
    for (section, grant, signal, parameter, form), record_id in zip(sections, ids):
        session = login(grant)
        url = base + '/admin/' + section + '/'
        result = session.get(url, timeout=15)
        assert result.status_code == 200, (grant, section, 'GET', result.status_code)
        token = re.search(r'name="admin-csrf" content="([a-f0-9]{64})"', result.text)[1]
        # Only disposable records; no files or email are created by this check.
        mutation_url = url + 'default/' + str(record_id) + '/' if parameter == 'id' else url
        params = {} if parameter == 'id' else {parameter: record_id}
        result = session.post(mutation_url, params=params,
                              data={'_do': signal, '_csrf': token}, allow_redirects=False, timeout=15)
        assert result.status_code in (200, 302, 303), (grant, section, 'delete', result.status_code)
        print(section + ': independent grant permits listing and fixture mutation')
