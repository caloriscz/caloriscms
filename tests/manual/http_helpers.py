"""Read rendered Nette tokens instead of bypassing login/form protection."""
from html.parser import HTMLParser
import requests

class FormParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.forms = []
        self.current = None
        self.textarea = None
        self.select = None

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == 'form':
            self.current = {**attrs, 'inputs': {}}
            self.forms.append(self.current)
        elif tag == 'input' and self.current is not None and 'name' in attrs:
            if attrs.get('type', 'text') in ('hidden', 'text', 'email', 'password') or (
                    attrs.get('type') in ('checkbox', 'radio') and 'checked' in attrs):
                self.current['inputs'][attrs['name']] = attrs.get('value', '')
        elif tag == 'textarea' and self.current is not None:
            self.textarea = attrs.get('name')
            self.current['inputs'][self.textarea] = ''
        elif tag == 'select' and self.current is not None:
            self.select = attrs.get('name')
        elif tag == 'option' and self.current is not None and self.select:
            if self.select not in self.current['inputs'] or 'selected' in attrs:
                self.current['inputs'][self.select] = attrs.get('value', '')

    def handle_data(self, data):
        if self.textarea and self.current is not None:
            self.current['inputs'][self.textarea] += data

    def handle_endtag(self, tag):
        if tag == 'form':
            self.current = None
        elif tag == 'textarea':
            self.textarea = None
        elif tag == 'select':
            self.select = None

def forms(body):
    parser = FormParser()
    parser.feed(body)
    return parser.forms

def login(base, username):
    session = requests.Session()
    page = session.get(base + '/admin/sign/in/', timeout=15)
    form = next(f for f in forms(page.text) if f.get('id') == 'frm-signIn-signInForm')
    data = {**form['inputs'], 'username': username, 'password': 'LocalPermissionSmoke1!'}
    result = session.post(base + '/admin/sign/in/', data=data, timeout=15)
    assert result.status_code == 200 and '/sign/' not in result.url, result.status_code
    return session
