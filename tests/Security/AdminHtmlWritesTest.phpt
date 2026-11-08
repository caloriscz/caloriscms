<?php
declare(strict_types=1);

use App\Model\Helpdesk;
use App\Security\HtmlContent;
use Nette\Application\AbortException;
use Nette\Forms\BootstrapUIForm;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';
$db = createTestDatabase();
$assetRoot = sys_get_temp_dir() . '/html-writes-' . bin2hex(random_bytes(8));
mkdir($assetRoot);
define('APP_DIR', $assetRoot);
register_shutdown_function(static function () use ($assetRoot): void { Nette\Utils\FileSystem::delete($assetRoot); });
$db->query('CREATE TABLE snippets (id INTEGER PRIMARY KEY, keyword TEXT, content TEXT, content_en TEXT)');
$db->query('CREATE TABLE menu_menus (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, description TEXT)');
$db->query('CREATE TABLE menu (id INTEGER PRIMARY KEY AUTOINCREMENT, menu_menus_id INTEGER, title TEXT, description TEXT, pages_id INTEGER, parent_id INTEGER, url TEXT)');
$db->query('CREATE TABLE links (id INTEGER PRIMARY KEY, title TEXT, description TEXT, url TEXT, links_categories_id INTEGER)');
$db->query('CREATE TABLE settings (id INTEGER PRIMARY KEY, setkey TEXT, setvalue TEXT, description_cs TEXT)');
$db->query('CREATE TABLE helpdesk (id INTEGER PRIMARY KEY, subject TEXT, body TEXT, email TEXT, helpdesk_templates_id INTEGER, log INTEGER)');
$db->query('CREATE TABLE helpdesk_templates (id INTEGER PRIMARY KEY, document TEXT)');
$db->query('CREATE TABLE helpdesk_messages (id INTEGER PRIMARY KEY AUTOINCREMENT, message TEXT, helpdesk_id INTEGER, email TEXT, ipaddress TEXT, date_created TEXT, status INTEGER)');
$db->query('CREATE TABLE languages (id INTEGER PRIMARY KEY, code TEXT, title TEXT, used INTEGER, "default" INTEGER)');
$db->table('languages')->insert(['id' => 1, 'code' => 'en', 'title' => 'English', 'used' => 1, 'default' => null]);
$db->table('snippets')->insert(['id' => 1, 'content' => '<script>untouched</script>', 'content_en' => 'English']);
$db->table('menu')->insert(['id' => 1, 'title' => 'Original']);
$db->table('links')->insert(['id' => 1]);
$db->table('helpdesk')->insert(['id' => 1, 'helpdesk_templates_id' => 1, 'log' => 1]);
$db->table('helpdesk_templates')->insert(['id' => 1, 'document' => '<html><head><title>%TITLE%</title></head><body>%CONTENT%</body></html>']);
$db->table('settings')->insert(['id' => 1, 'setkey' => 'maintenance_message', 'setvalue' => 'Original', 'description_cs' => 'Údržba: zpráva']);
$db->table('settings')->insert(['id' => 2, 'setkey' => 'site_title', 'setvalue' => 'Original']);

$dirty = '<div class="container" style="color: red"><strong>Safe &amp; sound</strong>'
    . '<script>alert(1)</script><img src="javascript:alert(1)" onerror="alert(1)">'
    . '<a href="javascript:alert(1)" onclick="alert(1)">Link</a>'
    . '<iframe src="https://example.invalid"></iframe></div>';
$safe = static function (string $html): void {
    Assert::contains('<strong>Safe &amp; sound</strong>', $html);
    Assert::contains('class="container"', $html);
    foreach (['<script', 'onerror', 'onclick', 'javascript:', '<iframe'] as $unsafe) {
        Assert::notContains($unsafe, strtolower($html));
    }
};
$safe(HtmlContent::purify($dirty));
Assert::same(HtmlContent::purify($dirty), (new App\Forms\Pages\EditorControl($db))->purify($dirty));
// Links keep web, mail, phone and relative targets; any other scheme, a
// target and event attributes are dropped.
$linked = '<p><a href="https://example.invalid/a?b=1&amp;c=2">w</a> <a href="mailto:info@example.invalid">m</a>'
    . ' <a href="tel:+420123456789">t</a> <a href="/kontakt/">r</a></p>';
Assert::same($linked, HtmlContent::purify($linked));
foreach (['javascript:alert(1)', 'jav&#97;script:alert(1)', ' JavaScript:alert(1)', 'vbscript:x', 'data:text/html,x', 'ftp://example.invalid/x'] as $url) {
    Assert::same('<a>x</a>', HtmlContent::purify('<a href="' . $url . '">x</a>'), $url);
}
Assert::same('<a href="https://example.invalid">x</a>', HtmlContent::purify('<a href="https://example.invalid" target="_blank" onclick="x()">x</a>'));
// Pictures inserted from a file, YouTube videos as Summernote inserts them and
// img alt/width/height survive (owner decision of October 24).
$png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
$media = '<p><img src="' . $png . '" alt="Pixel" width="120" height="40" /></p>'
    . '<p><iframe frameborder="0" src="//www.youtube.com/embed/dQw4w9WgXcQ" width="640" height="360"></iframe></p>'
    . '<p><iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ?start=42"></iframe>'
    . '<iframe src="http://www.youtube-nocookie.com/embed/dQw4w9WgXcQ"></iframe></p>';
Assert::same($media, HtmlContent::purify($media));
// Summernote's data-filename and class are dropped. An image without alt gets an
// empty one, not the end of its src (kilobytes of base64 for a picture from a file).
Assert::same('<p><img src="' . $png . '" style="width:25%;" alt="" /></p>',
    HtmlContent::purify('<p><img src="' . $png . '" data-filename="a.png" style="width: 25%;"></p>'));
// What a column can't store (typed or as an entity) becomes a numeric reference
// instead of an SQL failure: 4-byte characters everywhere (the schema is utf8mb3),
// and everything outside latin1 in links.description. Czech text stays as typed
// elsewhere, and saving again changes nothing.
Assert::same('<p>Čáp &#128512;</p>', HtmlContent::purify("<p>Čáp \u{1F600}</p>"));
Assert::same('<p>&#128512;</p>', HtmlContent::purify('<p>&#x1F600;</p>'));
Assert::same('<p>&#128512;</p>', HtmlContent::purify(HtmlContent::purify('<p>&#128512;</p>')));
Assert::same('<p>&#268;áp &#269;</p>', HtmlContent::latin1Html(HtmlContent::purify('<p>Čáp &#269;</p>')));
Assert::same('<p>á é ý &#8364; &#128512;</p>', HtmlContent::latin1Html(HtmlContent::purify("<p>á é ý € \u{1F600}</p>")));
Assert::same('<iframe frameborder="0" src="//www.youtube.com/embed/dQw4w9WgXcQ" width="640" height="360"></iframe>',
    HtmlContent::purify('<iframe frameborder="0" src="//www.youtube.com/embed/dQw4w9WgXcQ" width="640" height="360" class="note-video-clip"></iframe>'));
// data: only as an img src and only PNG, JPEG or GIF bytes; iframes only from
// YouTube's embed paths. Anything else loses the attribute or the element.
foreach (['<img src="data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==" />',
    '<img src="data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"/>') . '" />',
    '<img src="data:image/webp;base64,UklGRhoAAABXRUJQVlA4TA0AAAAvAAAAEAcQERGIiP4HAA==" />',
    '<iframe src="https://example.invalid/embed/x"></iframe>', '<iframe src="javascript:alert(1)"></iframe>',
    '<iframe src="data:text/html,&lt;script&gt;alert(1)&lt;/script&gt;"></iframe>',
    '<iframe src="https://www.youtube.com/watch?v=dQw4w9WgXcQ"></iframe>', '<iframe src="https://www.youtube.com.example.invalid/embed/x"></iframe>',
    '<iframe src="https://www.youtube.com/embed/../redirect?q=https://example.invalid"></iframe>',
    '<iframe src="https://player.vimeo.com/video/1"></iframe>', '<object data="//www.youtube.com/embed/dQw4w9WgXcQ"></object>'] as $removed) {
    Assert::same('', HtmlContent::purify($removed), $removed);
}
foreach (['data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
    'DATA:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
    'data:text/html,&lt;script&gt;alert(1)&lt;/script&gt;'] as $url) {
    Assert::same('<a>x</a>', HtmlContent::purify('<a href="' . $url . '">x</a>'), $url);
}
Assert::same('<p>x</p>', HtmlContent::purify('<p style="background-image: url(' . $png . ')">x</p>'));
Assert::same('<iframe src="//www.youtube.com/embed/dQw4w9WgXcQ"></iframe>',
    HtmlContent::purify('<iframe src="//www.youtube.com/embed/dQw4w9WgXcQ" srcdoc="&lt;script&gt;alert(1)&lt;/script&gt;" onload="alert(1)" allowfullscreen></iframe>'));
Assert::same('<img src="' . $png . '" alt="x" />', HtmlContent::purify('<img src="' . $png . '" alt="x" onerror="alert(1)">'));
// The columns are TEXT (65,535 bytes); a bigger result is refused, naming its size.
Assert::null(HtmlContent::textSizeError(str_repeat('x', 65535)));
Assert::contains('65 536 bajtů', HtmlContent::textSizeError(str_repeat('x', 65536)));
// A real PNG header is enough for HTMLPurifier's check; the padding makes it big.
$oversized = '<p><img src="data:image/png;base64,' . base64_encode(base64_decode(substr($png, 22)) . str_repeat("\0", 60000)) . '" alt="Big" /></p>';
Assert::true(strlen(HtmlContent::purify($oversized)) > HtmlContent::TEXT_COLUMN_BYTES);
// Plain menu labels round-trip as typed; only a complete tag makes markup.
foreach (['R&D & + Žluťoučký', 'A&B "q" ž', 'I <3 CMS', 'x<y z', 'a > b'] as $plain) {
    Assert::same($plain, HtmlContent::menuLabel($plain));
}
// The jsTree client sends escaped HTML; typed markup in the tree is still purified.
Assert::same('A&B "q" ž', HtmlContent::menuLabelFromTree('A&amp;B "q" ž'));
Assert::same('I <3 CMS', HtmlContent::menuLabelFromTree('I &lt;3 CMS'));
$safe(HtmlContent::menuLabelFromTree(htmlspecialchars($dirty)));
// The navbar prints labels with noescape: plain text is escaped there,
// including an unterminated tag, while existing entities and markup stay.
Assert::same('A&amp;B &quot;q&quot; ž', HtmlContent::menuLabelHtml('A&B "q" ž'));
Assert::same('I &lt;3 CMS', HtmlContent::menuLabelHtml('I <3 CMS'));
Assert::same('x&lt;img src=x onerror=alert(1)', HtmlContent::menuLabelHtml('x<img src=x onerror=alert(1)'));
Assert::same('Tom&amp;Jerry', HtmlContent::menuLabelHtml('Tom&amp;Jerry'));
Assert::same('<strong>B</strong> &amp; co', HtmlContent::menuLabelHtml('<strong>B</strong> &amp; co'));
Assert::same('', HtmlContent::menuLabelHtml(null));

$presenter = new class extends Nette\Application\UI\Presenter {
    public array $flashes = [];
    public function redirect(string $destination, $args = []): void { throw new AbortException; }
    public function flashMessage($message, string $type = 'info'): \stdClass
    {
        $this->flashes[] = $message;
        return (object) ['message' => $message, 'type' => $type];
    }
};
$form = static function (array $values): BootstrapUIForm {
    $form = new class extends BootstrapUIForm {
        public function getHttpData(?int $type = null, ?string $htmlName = null) { return []; }
    };
    foreach ($values as $key => $value) { $form->addText($key)->setDefaultValue($value); }
    return $form;
};
$save = static function ($control, string $method, BootstrapUIForm $form) use ($presenter): void {
    $control->setParent($presenter, 'writer');
    try { $control->$method($form); } catch (AbortException $expected) {
    } finally { $control->setParent(null); }
};
// No read-time migration. Only the submitted locale changes.
Assert::same('<script>untouched</script>', $db->table('snippets')->get(1)->content);
$save(new App\Forms\Snippets\EditFormControl($db), 'editSnippetFormSucceeded', $form(['snippet_id' => 1, 'l' => 'en', 'content' => $dirty]));
$safe($db->table('snippets')->get(1)->content_en);
Assert::same('<script>untouched</script>', $db->table('snippets')->get(1)->content);
$save(new App\Forms\Snippets\EditFormControl($db), 'editSnippetFormSucceeded', $form(['snippet_id' => 1, 'l' => '', 'content' => $dirty]));
$safe($db->table('snippets')->get(1)->content);
// Links in snippets worked before this form was purified and still do.
$save(new App\Forms\Snippets\EditFormControl($db), 'editSnippetFormSucceeded', $form(['snippet_id' => 1, 'l' => 'en', 'content' => $linked]));
Assert::same($linked, $db->table('snippets')->get(1)->content_en);
// So do pictures and videos. An oversized picture is a form error naming its
// size, and nothing is written.
$save(new App\Forms\Snippets\EditFormControl($db), 'editSnippetFormSucceeded', $form(['snippet_id' => 1, 'l' => 'en', 'content' => $media]));
Assert::same($media, $db->table('snippets')->get(1)->content_en);
$tooBig = $form(['snippet_id' => 1, 'l' => 'en', 'content' => $oversized]);
$save(new App\Forms\Snippets\EditFormControl($db), 'editSnippetFormSucceeded', $tooBig);
Assert::count(1, $tooBig->getErrors());
Assert::match('Obsah má po úpravě 80 149 bajtů, uložit lze nejvýše 65 535 bajtů.%A%Nic nebylo uloženo.', $tooBig->getErrors()[0]);
Assert::same($media, $db->table('snippets')->get(1)->content_en);

// Snippet creation accepts a keyword only, not forged content fields.
$save(new App\Forms\Snippets\InsertFormControl($db), 'insertFormSucceeded', $form(['title' => 'new', 'content' => $dirty]));
Assert::null($db->table('snippets')->where('keyword', 'new')->fetch()->content);
// Menu labels must fit menu.title varchar(80) once purified; $dirty does not.
$dirtyLabel = '<span class="container"><strong>Safe &amp; sound</strong></span><script>alert(1)</script>'
    . '<img src="javascript:alert(1)" onerror="alert(1)"><a href="javascript:alert(1)" onclick="alert(1)">L</a>';
$save(new App\Forms\Menu\EditMenuControl($db), 'editFormSucceeded', $form(['id' => 1, 'title' => $dirtyLabel, 'description' => 'Text', 'page' => 0, 'parent' => 0, 'url' => '/safe']));
$safe($db->table('menu')->get(1)->title);
// menu_menus.title is varchar(30): the group name is stored as typed, its root label purified.
$save(new App\Forms\Menu\InsertMenuMenusControl($db), 'insertFormSucceeded', $form(['title' => '<b onclick="x()">A &amp; B</b>', 'description' => 'Text']));
Assert::same('<b onclick="x()">A &amp; B</b>', $db->table('menu_menus')->fetch()->title);
Assert::same('<b>A &amp; B</b>', $db->table('menu')->where('id > ?', 1)->fetch()->title);

// Purifying markup turns '&' into '&amp;': 78 typed characters become 158.
// Every menu writer rejects that before writing anything (strict mode would
// make it a 500); a label that fits, also in Czech, is accepted.
$grows = str_repeat('y', 50) . '<b>x</b>' . str_repeat('&', 20);
Assert::same(78, mb_strlen($grows));
Assert::same(158, mb_strlen(HtmlContent::menuLabel($grows)));
Assert::contains('158 znaků', HtmlContent::menuLabelError(HtmlContent::menuLabel($grows)));
Assert::null(HtmlContent::menuLabelError(str_repeat('ž', 80)));
$snapshot = static function () use ($db): array {
    return [$db->table('menu')->order('id')->fetchPairs('id', 'title'), $db->table('menu_menus')->count('*')];
};
$before = $snapshot();
$edit = $form(['id' => 1, 'title' => $grows, 'description' => 'Changed', 'page' => 0, 'parent' => 0, 'url' => '/changed']);
$save(new App\Forms\Menu\EditMenuControl($db), 'editFormSucceeded', $edit);
Assert::count(1, $edit->getErrors());
Assert::contains('158 znaků', $edit->getErrors()[0]);
Assert::same('/safe', $db->table('menu')->get(1)->url);
foreach ([['title' => $grows, 'title_en' => 'Fits'], ['title' => 'Fits', 'title_en' => $grows]] as $titles) {
    $presenter->flashes = [];
    $save(new App\Forms\Menu\InsertMenuControl($db), 'insertFormSucceeded', $form($titles + ['parent' => '', 'menu_menus_id' => 1, 'url' => '', 'url_en' => '']));
    Assert::contains('158 znaků', $presenter->flashes[0] ?? '');
}
foreach (['<b>x</b>' . str_repeat('&', 22), str_repeat('m', 31)] as $group) {
    $presenter->flashes = [];
    $save(new App\Forms\Menu\InsertMenuMenusControl($db), 'insertFormSucceeded', $form(['title' => $group, 'description' => 'Text']));
    Assert::count(1, $presenter->flashes, $group);
}
Assert::exception(static function () use ($db, $grows): void {
    (new App\Model\Menu($db))->setCategory($grows, null);
}, Nette\InvalidArgumentException::class, '%a%158 znaků%a%');
Assert::same($before, $snapshot());

$settingsForm = new class extends BootstrapUIForm {
    public array $submitted = [];
    public function getHttpData(?int $type = null, ?string $htmlName = null) { return $this->submitted; }
};
$settingsForm->addText('category_id')->setDefaultValue('1');
$settingsForm->submitted = ['set' => ['maintenance_message' => $dirty, 'site_title' => 'R&D & + title']];
$save(new App\Forms\Settings\EditSettingsControl($db), 'editSettingsSucceeded', $settingsForm);
$safe($db->table('settings')->get(1)->setvalue);
Assert::same('R&D & + title', $db->table('settings')->get(2)->setvalue);

// 118 characters grow past varchar(120) once '&' becomes '&amp;'. That is a
// form error with nothing written, not a strict-mode 500 after a partial save.
$long = str_repeat('x', 108) . ' R&D & co.';
Assert::same(118, mb_strlen($long));
// A plain-text setting can't hold an emoji; the purified maintenance message
// stores it as a numeric reference (below).
foreach ([['site_title' => 'Changed', 'maintenance_message' => $long], ['site_title' => 'Pozor 🙂', 'maintenance_message' => 'Changed']] as $rejected) {
    $settingsForm = new class extends BootstrapUIForm {
        public array $submitted = [];
        public function getHttpData(?int $type = null, ?string $htmlName = null) { return $this->submitted; }
    };
    $settingsForm->addText('category_id')->setDefaultValue('1');
    $settingsForm->submitted = ['set' => $rejected];
    $save(new App\Forms\Settings\EditSettingsControl($db), 'editSettingsSucceeded', $settingsForm);
    Assert::count(1, $settingsForm->getErrors());
    Assert::same('R&D & + title', $db->table('settings')->get(2)->setvalue);
    $safe($db->table('settings')->get(1)->setvalue);
}
$settingsForm = new class extends BootstrapUIForm {
    public array $submitted = [];
    public function getHttpData(?int $type = null, ?string $htmlName = null) { return $this->submitted; }
};
$settingsForm->addText('category_id')->setDefaultValue('1');
$settingsForm->submitted = ['set' => ['maintenance_message' => 'Pozor 🙂']];
$save(new App\Forms\Settings\EditSettingsControl($db), 'editSettingsSucceeded', $settingsForm);
Assert::same('Pozor &#128578;', $db->table('settings')->get(1)->setvalue);

// Helpdesk templates are trusted Latte source, not purified content. Active
// content is rejected outright and nothing is written.
$rejected = $form(['id' => 1, 'subject' => 'Subject', 'body' => $dirty . '<p>{$name}</p>']);
$save(new App\Forms\Helpdesk\EditMailTemplateControl($db), 'editFormSucceeded', $rejected);
Assert::true($rejected->hasErrors());
Assert::null($db->table('helpdesk')->get(1)->body);
// The rules are context-free, so neither attribute boundaries without
// whitespace, a '>' in a quoted value, comments, raw-text elements, entities
// (also without ';') nor tab/newline inside a scheme hide active content.
foreach (['<p onclick="x()">a</p>', '<a href="jav&#97;script:alert(1)">x</a>', '<form action="/x"></form>',
    '<svg/onload=alert(1)>', '<b/onclick=alert(1)>x</b>', '<img src="x"onerror=alert(1)>',
    '<img alt=">" onerror=alert(1) src=x>', '<IMG SRC=x ONERROR' . "\n" . '=alert(1)>',
    '<a href="java&#9;script:alert(1)">x</a>', '<a href="java&#9script:alert(1)">x</a>',
    "<a href=\"java\tscript:alert(1)\">x</a>", "<a href=\"java\nscript:alert(1)\">x</a>",
    '<a href="javascript&colon;alert(1)">x</a>', '<a href="&#0000106avascript:alert(1)">x</a>',
    '<a href=" &#1;JavaScript:alert(1)">x</a>', '<img src="data:image/svg+xml,x">',
    '<meta http-equiv="refresh" content="0;url=https://example.invalid">', '<link rel="stylesheet" href="https://example.invalid/x.css">',
    '<math><mi xlink:href="javascript:alert(1)">x</mi></math>',
    '<p n:attr="onclick => \'alert(1)\'">x</p>', '<p n:attr="\'onclick\' => \'alert(1)\'">x</p>',
    '<a n:attr="href => \'javascript:alert(1)\'">x</a>', '<p {if $name}onclick="alert(1)"{/if}>x</p>',
    '<!-- <p title=" --> <img src=x onerror=alert(1)> <!-- " -->',
    '<textarea><p title="</textarea><img src=x onerror=alert(1)>"></textarea>',
    '{if $name}unclosed', '{if $name &&& }x{/if}',
    // Latte removes comments and replaces tags at render time, so they cannot
    // split a name or scheme; computed n:attr names and other tag syntaxes
    // are rejected (October 24 verification).
    '<img src=x on{* c *}error=alert(1)>', "<img src=x on{*\n*}error=alert(1)>", '<scr{* c *}ipt>alert(1)</script>',
    '<p on{if 1}error{/if}=alert(1)>x</p>', '<p on{=""}click=alert(1)>x</p>', '<p on{="}"}click=alert(1)>x</p>',
    '<a href="java{* c *}script:alert(1)">x</a>', '<a href="java{if 1}script{/if}:alert(1)">x</a>',
    '<p n:attr="[\'on\'.\'click\' => \'alert(1)\']">x</p>', '<p n:attr="$attributes">x</p>', '<p n:attr="$key => 1">x</p>',
    '<p n:attr="title => 1, ...$attributes">x</p>', '<p n:attr="strtolower(\'ONCLICK\') => 1">x</p>',
    '{syntax double}<img src=x on{{* c *}}error=alert(1)>{/syntax}', '<p n:syntax="double">x</p>',
    '<iframe src="//www.youtube.com/embed/dQw4w9WgXcQ"></iframe>', '<img src="data:image/png;base64,iVBORw0KGgo=">',
    // A control tag may drop the text it holds, so that text can't split a name
    // or scheme either (November 5, after the October 24 verification). Strings
    // can't pass for comments, as Latte reads them too.
    '<img src=x on{if 0}-{/if}error=alert(1)>', '<img src=x {if 0}-{/if}onerror=alert(1)>', '<img src=x {if 0}a b{/if}onerror=alert(1)>',
    '<img src=x o{if 1}n{/if}{if 0}-{/if}error=alert(1)>', '<img src=x on{if 0}-{else}error{/if}=alert(1)>', '<img src=x on{$x}=alert(1)>',
    '<img {if 0}x="{/if}o{if 0}-{/if}nerror=alert(1) {if 0}"{/if}>',
    '<a href="java{if 0}-{/if}script:alert(1)">x</a>', '<a href="java{if 1}script{/if}{if 0}-{/if}:alert(1)">x</a>',
    '<a href="{if 0}-{/if}javascript:alert(1)">x</a>', '<img src="da{if 0}-{/if}ta:image/svg+xml,x">',
    '<a href="java&#11{if 0}-{/if}5;cript:alert(1)">x</a>', '<a href="javascript&#5{$x}8;alert(1)">x</a>', '<a href="&{if 0}-{/if}#106;avascript:alert(1)">x</a>',
    '<scr{if 0}-{/if}ipt>alert(1)</script>', '<{if 0}-{/if}script>alert(1)</script>', '<{if 1}/{/if}{if 0}-{/if}form>',
    '{=\'{*\'}<script>alert(1)</script>{=\'*}\'}', '<img src=x on{=[\'{\']}error=alert(1)>',
    // n:attr names must be a plain literal list, and n:tag names the element at render time.
    '<p n:attr="title => \'t\', (expand) $a">x</p>', '<p n:attr="title => \'t\', ( EXPAND ) $a">x</p>', '<p n:attr="title => \'t\', $a">x</p>',
    '<p n:attr="$a + [\'title\' => \'t\']">x</p>', '<p n:attr="[\'title\' => \'t\'] + $a">x</p>', '<p n:attr="array_merge($a, [\'title\' => \'t\'])">x</p>',
    '<p n:attr="[\'a\' => 1] + [\'b\' => 2]">x</p>', '<div n:tag="\'iframe\'" src="https://example.invalid">x</div>', '<div n:tag="$name">x</div>',
    // Not valid Latte for the tokenizer: an unterminated tag.
    '<p>{$name</p>'] as $body) {
    $rejected = $form(['id' => 1, 'subject' => 'Subject', 'body' => $body]);
    $save(new App\Forms\Helpdesk\EditMailTemplateControl($db), 'editFormSucceeded', $rejected);
    Assert::true($rejected->hasErrors(), $body);
    Assert::null($db->table('helpdesk')->get(1)->body);
}
// The error names what matched, so an over-rejection can be found and edited.
Assert::contains('„/onclick=“', Helpdesk::templateError('<b/onclick=alert(1)>x</b>', ''));
Assert::contains('„on{…}-{…}error=“', Helpdesk::templateError('<img src=x on{if 0}-{/if}error=alert(1)>', ''));
Assert::contains('chyba v Latte', Helpdesk::templateError('<p>{$name</p>', ''));
// Ordinary template markup and text that merely resembles a rule are accepted,
// also with tags inside attributes and values. The last one stays stored for
// the send below.
foreach (['<p class="online">Platba online: {$amount}</p><p>Data: {$data}</p>',
    '<img src="{$baseUri}/logo.png" alt="Logo" width="120"><a href="mailto:{$email}">{$email}</a>',
    '<style>p { color: red }</style><table style="width: 100%"><tr><td>{$message}</td></tr></table>',
    '<p n:attr="title => $name, class => \'online\'">{$name}</p>', '<p title="{$name}">{* note *}{l}x{r} { EUR }</p>',
    '<p n:attr="[\'title\' => $name, \'class\' => [\'a\', \'b\' => $x],]">x</p>', '<p n:if="$name" n:tag-if="$email">x</p>',
    '<p class="btn{if $name}-primary{/if}" {if $email}title="x"{/if}>x</p>', '<a href="{if $url}{$url}{else}/{/if}">x</a>',
    '<a href="{$url}?code={$code}&email={$email}">{$url}</a>', '<p>{$key}={$value}, {if $a}<b>on</b>{else}off{/if}</p>',
    '<p>{$name}</p>{$extra|noescape}'] as $body) {
    $accepted = $form(['id' => 1, 'subject' => 'Subject', 'body' => $body]);
    $save(new App\Forms\Helpdesk\EditMailTemplateControl($db), 'editFormSucceeded', $accepted);
    Assert::same([], $accepted->getErrors(), $body);
    Assert::same($body, $db->table('helpdesk')->get(1)->body);
}
// The subject is HTML-escaped before Latte compiles it (kept as is by the owner
// on October 23): a plain variable works, a tag with quotes or '&&' is rejected.
$subjectError = static function (string $subject): ?string {
    return Helpdesk::templateError('<p>x</p>', Helpdesk::composeDocument('<title>%TITLE%</title>%CONTENT%', $subject, '<p>x</p>'));
};
foreach (['Poptávka od {$name}', 'Cena { EUR }', 'Tom & Jerry <3'] as $subject) {
    Assert::null($subjectError($subject), $subject);
}
foreach (['Cena {EUR}', 'Web {$settings[\'site:title\']}', '{if $name && $email}x{/if}'] as $subject) {
    Assert::notNull($subjectError($subject), $subject);
}

// The subject is compiled with the body, so another tag syntax there would change
// what the body's rules saw: subject '{syntax double}' with this body rendered onerror.
$bodyBefore = $db->table('helpdesk')->get(1)->body;
foreach (['{syntax double}', 'Cena {syntax off}', '{SYNTAX double}', "{syntax\tdouble}"] as $subject) {
    $rejected = $form(['id' => 1, 'subject' => $subject, 'body' => '<img src=x on{{* c *}}error=alert(1)>']);
    $save(new App\Forms\Helpdesk\EditMailTemplateControl($db), 'editFormSucceeded', $rejected);
    Assert::true($rejected->hasErrors(), $subject);
    Assert::contains('syntaxi Latte', $rejected->getErrors()[0], $subject);
    Assert::same('Subject', $db->table('helpdesk')->get(1)->subject, $subject);
    Assert::same($bodyBefore, $db->table('helpdesk')->get(1)->body, $subject);
}
// Negative control: the body alone passes, which is why the subject has to be refused.
Assert::null(Helpdesk::activeContent('<img src=x on{{* c *}}error=alert(1)>'));
// The seeded subjects, and text that only mentions the word, still save.
foreach (['Poptávka', 'Poptávka od návštěvníka', 'Vytvoření nového hesla', 'Nový e-mail', 'Informace o novém hesle',
        'Poptávka od {$name}', 'Latte syntax', 'Cena { syntax }'] as $subject) {
    $accepted = $form(['id' => 1, 'subject' => $subject, 'body' => '<p>x</p>']);
    $save(new App\Forms\Helpdesk\EditMailTemplateControl($db), 'editFormSucceeded', $accepted);
    Assert::false($accepted->hasErrors(), $subject);
    Assert::same($subject, $db->table('helpdesk')->get(1)->subject);
}
$db->table('helpdesk')->get(1)->update(['subject' => 'Subject', 'body' => $bodyBefore]);

// Previews of stored HTML: the real chain from each template, rendered by Latte. A
// numeric reference and &amp; show as the character, and a decoded &lt;script&gt; stays text.
$preview = static function (string $file, string $document, int $length = 350): string {
    $source = file_get_contents(__DIR__ . '/../../' . $file);
    Assert::same(1, preg_match('~\{\$(?:item|page)->document\|[^}]*truncate:[^}]*\}~', $source, $chain), $file);
    $latte = new Latte\Engine;
    $latte->setLoader(new Latte\Loaders\StringLoader);
    return $latte->renderToString($chain[0], [
        'item' => (object) ['document' => $document], 'page' => (object) ['document' => $document],
        'settings' => ['blog:preview:length' => $length],
    ]);
};
$stored = '<p>Čáp &#128512; a &amp; b &lt;script&gt;x&lt;/script&gt;</p>';
foreach (['app/components/Blog/BlogListControl.latte', 'app/components/Blog/BlogPreviewControl.latte'] as $file) {
    Assert::same("Čáp \u{1F600} a &amp; b &lt;script&gt;x&lt;/script&gt;", $preview($file, $stored), $file);
    // Truncated after decoding, so no half of a reference; 349 characters and the ellipsis.
    Assert::same(str_repeat("\u{1F600}", 349) . '…', $preview($file, '<p>' . str_repeat('&#128512;', 400) . '</p>'), $file);
}
// RSS readers treat a description as HTML, so after the XML layer it must still be
// escaped HTML: the reader shows the character, and <script> never reaches it as a tag.
$feed = $preview('app/ApiModule/templates/Feed/default.latte', $stored);
Assert::same("Čáp \u{1F600} a &amp;amp; b &amp;lt;script&amp;gt;x&amp;lt;/script&amp;gt;", $feed);
Assert::same("<d>Čáp \u{1F600} a &amp; b &lt;script&gt;x&lt;/script&gt;</d>",
    '<d>' . (string) simplexml_load_string('<d>' . $feed . '</d>') . '</d>');
// Negative control: the old chain escapes the reference a second time.
$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
Assert::contains('&amp;#128512;', $latte->renderToString('{$d|striptags|truncate:350}', ['d' => $stored]));

$mailer = new class implements Nette\Mail\Mailer {
    public ?Nette\Mail\Message $sent = null;
    public function send(Nette\Mail\Message $mail): void { $this->sent = $mail; }
};
$helpdesk = new App\Model\Helpdesk($db, $mailer);
$helpdesk->setId(1);
$helpdesk->setSettings(['contacts:email:hq' => 'sender@example.invalid']);
$helpdesk->setEmail('synthetic@example.invalid');
$helpdesk->setParams(['name' => 'R&D <reader>', 'extra' => $dirty]);
Assert::true($helpdesk->send());
Assert::same('Subject', $mailer->sent->getSubject());
Assert::contains('R&amp;D &lt;reader&gt;', $mailer->sent->getHtmlBody());
// The archive is purified after trusted Latte placeholders have expanded,
// including an explicit noescape placeholder. The fake mailer sends nothing.
$safe($db->table('helpdesk_messages')->fetch()->message);

// Latte logic and href placeholders survive a subject-only save (the browser
// submits the raw textarea) and a Summernote-escaped body edit, and still send.
$template = '<p>{if $phone && $email}Both given{/if}</p><p n:if="strlen($message) > 3"><a href="{$url}">{$url}</a></p>';
$db->table('helpdesk')->insert(['id' => 2, 'subject' => 'Old', 'body' => $template, 'helpdesk_templates_id' => 1, 'log' => 0]);
$save(new App\Forms\Helpdesk\EditMailTemplateControl($db), 'editFormSucceeded', $form(['id' => 2, 'subject' => 'Tom & Jerry <3', 'body' => $template]));
Assert::same('Tom & Jerry <3', $db->table('helpdesk')->get(2)->subject);
Assert::same($template, $db->table('helpdesk')->get(2)->body);
$escaped = '<p>{if $phone &amp;&amp; $email}Both given{/if}</p><p n:if="strlen($message) &gt; 3"><a href="{$url}">{$url}</a></p>';
$save(new App\Forms\Helpdesk\EditMailTemplateControl($db), 'editFormSucceeded', $form(['id' => 2, 'subject' => 'Tom & Jerry <3', 'body' => $escaped]));
Assert::same(Helpdesk::normalizeTemplateSource($escaped), $db->table('helpdesk')->get(2)->body);
Assert::contains('{if $phone && $email}', $db->table('helpdesk')->get(2)->body);
$helpdesk = new App\Model\Helpdesk($db, $mailer);
$helpdesk->setId(2);
$helpdesk->setSettings(['contacts:email:hq' => 'sender@example.invalid']);
$helpdesk->setEmail('synthetic@example.invalid');
$helpdesk->setParams(['phone' => '1', 'email' => 'e@example.invalid', 'message' => 'Long enough', 'url' => 'https://example.invalid/x?a=1&b=2']);
Assert::true($helpdesk->send());
Assert::same('Tom & Jerry <3', $mailer->sent->getSubject());
Assert::contains('Both given', $mailer->sent->getHtmlBody());
Assert::contains('href="https://example.invalid/x?a=1&amp;b=2"', $mailer->sent->getHtmlBody());

$links = new class($db, $mailer) extends App\AdminModule\Presenters\LinksPresenter {
    public function redirect(string $destination, $args = []): void { throw new AbortException; }
};
$filesBefore = $_FILES;
foreach ([$dirty, $linked, $media] as $description) {
    $_FILES['the_file'] = ['size' => 0];
    try {
        $links->editFormSucceeded($form(['id' => 1, 'title' => 'Label', 'url' => '/safe', 'category' => 1, 'description' => $description]));
    } catch (AbortException $expected) {
    } finally { $_FILES = $filesBefore; }
    if ($description === $dirty) {
        $safe($db->table('links')->get(1)->description);
    } else {
        Assert::same($description, $db->table('links')->get(1)->description);
    }
}
// links.description is latin1, so 'č' is a numeric reference there; 'á' fits.
$_FILES['the_file'] = ['size' => 0];
try {
    $links->editFormSucceeded($form(['id' => 1, 'title' => 'Label', 'url' => '/safe', 'category' => 1, 'description' => "<p>Čáp \u{1F600}</p>"]));
} catch (AbortException $expected) {
} finally { $_FILES = $filesBefore; }
Assert::same('<p>&#268;áp &#128512;</p>', $db->table('links')->get(1)->description);
$_FILES['the_file'] = ['size' => 0];
try {
    $links->editFormSucceeded($form(['id' => 1, 'title' => 'Label', 'url' => '/safe', 'category' => 1, 'description' => $media]));
} catch (AbortException $expected) {
} finally { $_FILES = $filesBefore; }
$tooBig = $form(['id' => 1, 'title' => 'Changed', 'url' => '/changed', 'category' => 1, 'description' => $oversized]);
$links->editFormSucceeded($tooBig);
Assert::count(1, $tooBig->getErrors());
Assert::contains('Nic nebylo uloženo.', $tooBig->getErrors()[0]);
$link = $db->table('links')->get(1);
Assert::same(['Label', '/safe', $media], [$link->title, $link->url, $link->description]);
