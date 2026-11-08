"""Opt-in local HTML writer checks with disposable rows and rendered CSRF tokens."""
import base64
import json
import re
import subprocess
import sys
import xml.etree.ElementTree as ElementTree
from urllib.parse import urljoin
import requests
from http_helpers import forms, login

prefix = sys.argv[1]
base = 'http://localhost:8090'

def fixture(mode):
    out = subprocess.check_output(['docker', 'compose', 'exec', '-T', 'app', 'php',
        'tests/manual/csrf-fixture.php', mode, prefix], text=True)
    return json.loads(out) if out else None

def safe(value):
    assert '<strong>Safe &amp; sound</strong>' in value, value
    for fragment in ['<script', 'onerror', 'onclick', 'javascript:', '<iframe']:
        assert fragment not in value.lower(), fragment

payload = '<strong>Safe &amp; sound</strong><script>alert(1)</script>' \
    '<img src="javascript:alert(1)" onerror="alert(1)"><a href="javascript:alert(1)">Link</a>'
try:
    ids = fixture('create')
    session = login(base, prefix + '-full')
    denied = login(base, prefix + '-denied')
    cases = [
        ('snippets/detail/', 'snippets', 'editSnippetForm-editSnippetForm-submit', 'content'),
        ('links/detail/', 'links', 'editForm-submit', 'description'),
        ('menu/detail/', 'menu', 'menuEdit-editForm-submit', 'title'),
    ]
    for route, table, signal, field in cases:
        url = base + '/admin/' + route + str(ids[table]) + '/'
        page = session.get(url, timeout=15)
        assert page.status_code == 200, (route, page.status_code)
        form = next(f for f in forms(page.text) if f['inputs'].get('_do') == signal)
        data = {**form['inputs'], field: payload}
        action = urljoin(url, form['action'])
        before = fixture('snapshot')
        bad = session.post(action, data={**data, '_token_': 'bad'},
            files={'the_file': ('', b'')}, allow_redirects=False, timeout=15)
        assert bad.status_code == 403 and fixture('snapshot') == before
        bad = denied.post(action, data=data, files={'the_file': ('', b'')},
            allow_redirects=False, timeout=15)
        assert bad.status_code == 403 and fixture('snapshot') == before
        result = session.post(action, data=data, files={'the_file': ('', b'')},
            allow_redirects=False, timeout=15)
        assert result.status_code in (302, 303), (route, result.status_code)
        safe(fixture('read')[table][field])
        print('PASS: ' + signal + ' purifies HTML; invalid token/role cannot write', flush=True)

    # Links keep web, mail, phone and relative targets; other schemes lose the href.
    linked = '<p><a href="https://example.invalid/a">w</a> <a href="mailto:info@example.invalid">m</a>' \
        ' <a href="tel:+420123456789">t</a> <a href="/kontakt/">r</a> <a href="javascript:alert(1)">j</a></p>'
    for route, table, signal, field in cases[:2]:
        url = base + '/admin/' + route + str(ids[table]) + '/'
        form = next(f for f in forms(session.get(url, timeout=15).text) if f['inputs'].get('_do') == signal)
        result = session.post(urljoin(url, form['action']), data={**form['inputs'], field: linked},
            files={'the_file': ('', b'')}, allow_redirects=False, timeout=15)
        assert result.status_code in (302, 303), (route, result.status_code)
        stored = fixture('read')[table][field]
        assert stored == linked.replace('<a href="javascript:alert(1)">', '<a>'), stored
    print('PASS: snippet and link description keep https/mailto/tel/relative links; javascript: loses the href', flush=True)

    # Pictures from a file (data: PNG in img src only), YouTube videos as
    # Summernote inserts them and img alt/width/height survive on every
    # content writer; other data: URLs and iframes go, onerror still goes.
    png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
    kept = '<p><img src="' + png + '" alt="Pixel" width="120" height="40" /></p>'         '<p><iframe frameborder="0" src="//www.youtube.com/embed/dQw4w9WgXcQ" width="640" height="360"></iframe></p>'         '<p><a href="https://example.invalid/a">w</a></p>'
    media = kept.replace(' />', ' data-filename="pixel.png">').replace('height="360"', 'height="360" class="note-video-clip"')         + '<p><img src="data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg=="><a href="' + png + '">d</a>'         '<iframe src="https://example.invalid/embed/x"></iframe><iframe src="javascript:alert(1)"></iframe>'         '<img src="/x.png" onerror="alert(1)"></p>'
    expected = kept + '<p><a>d</a><img src="/x.png" alt="" /></p>'
    # A real PNG header passes HTMLPurifier's check; 60,000 padding bytes make
    # about 80 kB of base64, past the 65,535-byte TEXT columns.
    big = 'data:image/png;base64,' + base64.b64encode(base64.b64decode(png[22:]) + b'\0' * 60000).decode()
    oversized = '<p><img src="' + big + '" alt="Big" /></p>'
    media_cases = cases[:2] + [('pages/detail/', 'pages', 'editor-editForm-submit', 'document')]
    for route, table, signal, field in media_cases:
        url = base + '/admin/' + route + str(ids[table]) + '/'
        form = next(f for f in forms(session.get(url, timeout=15).text) if f['inputs'].get('_do') == signal)
        result = session.post(urljoin(url, form['action']), data={**form['inputs'], field: media},
            files={'the_file': ('', b'')}, allow_redirects=False, timeout=15)
        assert result.status_code in (302, 303), (route, result.status_code)
        stored = fixture('read')[table][field]
        assert stored == expected, (route, stored)
        before = fixture('snapshot')
        result = session.post(urljoin(url, form['action']), data={**form['inputs'], field: oversized},
            files={'the_file': ('', b'')}, allow_redirects=False, timeout=15)
        assert result.status_code == 200, (route, result.status_code)
        assert 'Obsah má po úpravě 80 149 bajtů, uložit lze nejvýše 65 535 bajtů.' in result.text, route
        assert re.search(r'class="alert alert-danger">\s*Obsah má po úpravě', result.text), route + ' error not shown as an alert'
        assert fixture('snapshot') == before and fixture('read')[table][field] == expected, route + ' changed data'
    print('PASS: snippet, link description and page keep a data: PNG, a YouTube video and alt/width/height;'
        ' other data:/iframes and onerror removed; an oversized picture is a form error with nothing written', flush=True)

    # What a column can't store, typed or as an entity, is a numeric reference and
    # not a 500: 4-byte characters on utf8mb3, and anything outside latin1 in
    # links.description. The rendered form loads it back unchanged.
    utf8 = {'snippets': '<p>Čáp &#128512;</p>', 'pages': '<p>Čáp &#128512;</p>', 'links': '<p>&#268;áp &#128512;</p>'}
    for route, table, signal, field in media_cases:
        url = base + '/admin/' + route + str(ids[table]) + '/'
        for typed in ['<p>Čáp \U0001F600</p>', '<p>&#268;áp &#x1F600;</p>']:
            form = next(f for f in forms(session.get(url, timeout=15).text) if f['inputs'].get('_do') == signal)
            result = session.post(urljoin(url, form['action']), data={**form['inputs'], field: typed},
                files={'the_file': ('', b'')}, allow_redirects=False, timeout=15)
            assert result.status_code in (302, 303), (route, typed, result.status_code)
            assert fixture('read')[table][field] == utf8[table], (route, fixture('read')[table][field])
        form = next(f for f in forms(session.get(url, timeout=15).text) if f['inputs'].get('_do') == signal)
        result = session.post(urljoin(url, form['action']), data=form['inputs'], files={'the_file': ('', b'')},
            allow_redirects=False, timeout=15)
        assert result.status_code in (302, 303) and fixture('read')[table][field] == utf8[table], route
    print('PASS: emoji and characters outside latin1 are stored as numeric references, typed or as entities; no 500', flush=True)

    # Helpdesk templates are trusted Latte source: active content is rejected
    # (form error, no write), not purified; logic and href placeholders survive.
    url = base + '/admin/helpdesk/emails/' + str(ids['helpdesk']) + '/'
    def template_form():
        page = session.get(url, timeout=15)
        return next(f for f in forms(page.text) if f['inputs'].get('_do') == 'editMailTemplate-editForm-submit')
    form = template_form()
    action = urljoin(url, form['action'])
    before = fixture('snapshot')
    for token, client in [('bad', session), (None, denied)]:
        data = {**form['inputs'], 'body': '<p>x</p>'}
        if token:
            data['_token_'] = token
        assert client.post(action, data=data, allow_redirects=False, timeout=15).status_code == 403
        assert fixture('snapshot') == before
    for body in [payload, '<svg/onload=alert(1)>', '<img src="x"onerror=alert(1)>', '<a href="java&#9;script:alert(1)">x</a>',
            # Latte comments and tags inside a name or scheme, a computed n:attr name.
            '<img src=x on{* c *}error=alert(1)>', '<scr{* c *}ipt>alert(1)</script>', '<p on{if 1}error{/if}=alert(1)>x</p>',
            '<p n:attr="[\'on\'.\'click\' => \'alert(1)\']">x</p>', '<a href="java{* c *}script:alert(1)">x</a>',
            # Text a control tag may drop, strings that look like comments, spreads, n:tag.
            '<img src=x on{if 0}-{/if}error=alert(1)>', '<img src=x {if 0}-{/if}onerror=alert(1)>',
            '<a href="java{if 0}-{/if}script:alert(1)">x</a>', '{=\'{*\'}<script>alert(1)</script>{=\'*}\'}',
            '<p n:attr="title => \'t\', (expand) $a">x</p>', '<div n:tag="\'iframe\'" src="https://example.invalid">x</div>',
            # Helpdesk templates keep rejecting what content fields now keep.
            '<iframe src="//www.youtube.com/embed/dQw4w9WgXcQ"></iframe>', '<img src="' + png + '">']:
        rejected = session.post(action, data={**form['inputs'], 'body': body}, allow_redirects=False, timeout=15)
        assert rejected.status_code == 200 and 'alert alert-danger' in rejected.text, body
        assert fixture('snapshot') == before, body
    template = '<p>{if $phone && $email}Both given{/if}</p><p n:if="strlen($message) > 3"><a href="{$url}">{$url}</a></p>'
    saved = session.post(action, data={**form['inputs'], 'subject': 'Tom & Jerry <3', 'body': template},
        allow_redirects=False, timeout=15)
    assert saved.status_code in (302, 303), saved.status_code
    row = fixture('read')['helpdesk']
    assert row['subject'] == 'Tom & Jerry <3' and row['body'] == template, row
    form = template_form()
    assert form['inputs']['subject'] == 'Tom & Jerry <3' and form['inputs']['body'] == template
    # Subject-only save, posting the rendered form back as a browser does.
    saved = session.post(action, data={**form['inputs'], 'subject': 'Tom & Jerry <3 again'}, allow_redirects=False, timeout=15)
    assert saved.status_code in (302, 303) and fixture('read')['helpdesk']['body'] == template
    sent = json.loads(subprocess.check_output(['docker', 'compose', 'exec', '-T', 'app', 'php',
        'tests/manual/helpdesk-send-fake.php', str(ids['helpdesk'])], text=True, encoding='utf-8'))
    assert sent['sent'] is True and sent['subject'] == 'Tom & Jerry <3 again', sent['subject']
    assert 'Both given' in sent['body'] and 'href="https://example.invalid/x"' in sent['body']
    print('PASS: Helpdesk template rejects active content (also split by Latte comments/tags or a computed n:attr name),'
        ' keeps Latte logic/links and plain subject, sends after subject-only save', flush=True)

    # The subject is compiled with the body: another tag syntax there would change
    # what the body's rules saw. The body alone passes, so it is the subject that's refused.
    form = template_form()
    before = fixture('snapshot')
    for subject in ['{syntax double}', 'Cena {syntax off}']:
        rejected = session.post(action, data={**form['inputs'], 'subject': subject, 'body': '<img src=x on{{* c *}}error=alert(1)>'},
            allow_redirects=False, timeout=15)
        assert rejected.status_code == 200 and 'syntaxi Latte' in rejected.text and 'alert alert-danger' in rejected.text, subject
        assert fixture('snapshot') == before, subject
    for subject in ['Poptávka', 'Poptávka od návštěvníka', 'Vytvoření nového hesla', 'Nový e-mail', 'Informace o novém hesle']:
        saved = session.post(action, data={**form['inputs'], 'subject': subject}, allow_redirects=False, timeout=15)
        assert saved.status_code in (302, 303) and fixture('read')['helpdesk']['subject'] == subject, subject
    print('PASS: {syntax in the Helpdesk subject is a form error with nothing written; the seeded subjects save', flush=True)

    # Purification can grow the maintenance message past varchar(120): a form
    # error with nothing written, not a 500 or a partial save.
    url = base + '/admin/settings/global/'
    page = session.get(url, timeout=15)
    form = next(f for f in forms(page.text) if f['inputs'].get('_do') == 'editSettings-editSettingsForm-submit')
    long = 'x' * 108 + ' R&D & co.'
    assert len(long) == 118 and 'set[maintenance_message]' in form['inputs']
    before = fixture('snapshot')
    data = {**form['inputs'], 'set[maintenance_message]': long, 'set[site:title]': prefix}
    result = session.post(urljoin(url, form['action']), data=data, allow_redirects=False, timeout=15)
    assert result.status_code == 200 and 'alert alert-danger' in result.text, result.status_code
    assert fixture('snapshot') == before, 'Rejected settings save changed data'
    print('PASS: 118-character maintenance message with & gives a form error; no setting changed', flush=True)

    # Purification grows this markup label from 78 to 158 characters, past
    # menu.title varchar(80): an error before any write on every menu writer.
    grows = 'y' * 50 + '<b>x</b>' + '&' * 20
    url = base + '/admin/menu/detail/' + str(ids['menu']) + '/'
    form = next(f for f in forms(session.get(url, timeout=15).text) if f['inputs'].get('_do') == 'menuEdit-editForm-submit')
    before = fixture('snapshot')
    result = session.post(urljoin(url, form['action']), data={**form['inputs'], 'title': grows}, allow_redirects=False, timeout=15)
    assert result.status_code == 200 and '158 znaků' in result.text, result.status_code
    assert fixture('snapshot') == before, 'Rejected menu edit changed data'
    # Both insert forms are rendered by hand and report with a flash message.
    url = base + '/admin/menu/?menu=' + str(ids['menu_menus'])
    page = session.get(url, timeout=15)
    for signal, title, length in [('menuInsert-insertForm-submit', grows, 158),
            ('menuMenusInsert-insertForm-submit', '<b>x</b>' + '&' * 22, 118)]:
        form = next(f for f in forms(page.text) if f['inputs'].get('_do') == signal)
        result = session.post(urljoin(url, form['action']), data={**form['inputs'], 'title': title}, allow_redirects=False, timeout=15)
        assert result.status_code in (302, 303), (signal, result.status_code)
        assert fixture('snapshot') == before, signal + ' changed data'
        assert str(length) + ' znaků' in session.get(urljoin(url, result.headers['Location']), timeout=15).text, signal
    print('PASS: menu labels that outgrow varchar(80) give an error on edit and both inserts; nothing written', flush=True)

    page = session.get(base + '/admin/', timeout=15)
    csrf = re.search(r'name="admin-csrf" content="([a-f0-9]{64})"', page.text)[1]
    def rename(text, status=200):
        result = session.post(base + '/admin/menu/',
            params={'do': 'menuEditor-rename', 'node_id': ids['menu'], 'text': text},
            data={'_csrf': csrf}, headers={'X-Requested-With': 'XMLHttpRequest'}, timeout=15)
        assert result.status_code == status, (result.status_code, text)
        return result
    title = fixture('read')['menu']['title']
    result = rename(grows.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;'), 422)
    assert '158 znaků' in result.json()['error'] and fixture('read')['menu']['title'] == title
    print('PASS: tree rename that outgrows varchar(80) answers 422; title unchanged', flush=True)
    rename(payload)
    safe(fixture('read')['menu']['title'])
    for plain in ['R&D & + Žluťoučký', 'A&B "q" ž', 'I <3 CMS', 'x<y z']:
        rename(plain)
        assert fixture('read')['menu']['title'] == plain
        # The jsTree client sends node text escaped like this.
        rename(plain.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;'))
        assert fixture('read')['menu']['title'] == plain
    print('PASS: tree AJAX purifies markup and stores plain labels (raw or jsTree-escaped) as typed', flush=True)

    # Previews of stored HTML, through the real page editor, /blog/ and /rss.xml (RSS is
    # on only for this check; cleanup restores it). The characters show, and a decoded
    # &lt;script&gt; can't become markup.
    url = base + '/admin/pages/detail/' + str(ids['pages']) + '/'
    form = next(f for f in forms(session.get(url, timeout=15).text) if f['inputs'].get('_do') == 'editor-editForm-submit')
    document = '<p>Čáp \U0001F600 a &amp; b &lt;script&gt;alert(1)&lt;/script&gt; konec</p>'
    result = session.post(urljoin(url, form['action']), data={**form['inputs'], 'document': document},
        files={'the_file': ('', b'')}, allow_redirects=False, timeout=15)
    assert result.status_code in (302, 303), result.status_code
    stored = fixture('read')['pages']['document']
    assert stored == '<p>Čáp &#128512; a &amp; b &lt;script&gt;alert(1)&lt;/script&gt; konec</p>', stored
    fixture('blog')
    public = requests.Session()
    blog = public.get(base + '/blog/', timeout=15)
    assert blog.status_code == 200 and prefix in blog.text, blog.status_code
    article = blog.text[blog.text.index(prefix):]
    assert 'Čáp \U0001F600 a &amp; b &lt;script&gt;alert(1)&lt;/script&gt; konec' in article, article[:400]
    assert '&amp;#128512;' not in article and '&amp;amp;' not in article and '<script>alert(1)' not in article, article[:400]
    feed = public.get(base + '/rss.xml', timeout=15)
    assert feed.status_code == 200, feed.status_code
    assert b'<script' not in feed.content
    item = next(i for i in ElementTree.fromstring(feed.content).iter('item') if i.findtext('title') == prefix)
    # What a reader gets after the XML layer is escaped HTML with the character in it.
    assert item.findtext('description') == 'Čáp \U0001F600 a &amp; b &lt;script&gt;alert(1)&lt;/script&gt; konec', item.findtext('description')
    print('PASS: /blog/ and /rss.xml previews show the emoji and & once, and a decoded <script> stays text', flush=True)
finally:
    fixture('cleanup')
