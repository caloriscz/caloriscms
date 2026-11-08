<?php

/*
 * Caloris Category
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL3
 */

namespace App\Model;

use Latte\Engine;
use Latte\Loaders\StringLoader;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\IRow;
use Nette\Mail\Mailer;
use Nette\Mail\Message;

/**
 * Get category name
 * @author Petr Karásek <caloris@caloris.cz>
 */
class Helpdesk
{
    /** Stand-ins for Latte tags in templateMarkup(). Latte refuses these characters in a template. */
    private const CONTROL_TAG = "\x01"; // {if}, {/if}, {else}…: the text up to the next one may not render.
    private const PRINT_TAG = "\x02"; // {$x}, {_'x'}: may print nothing.

    /** What can separate two characters that still render side by side: tags and the text they may drop. */
    private const TAG_GAP = '\x02*(?:\x01(?:[^\x01]*\x01)*?\x02*)?';

    /** helpdesk.subject is varchar(250). */
    public const SUBJECT_LENGTH = 250;

    private Explorer $database;

    private $mailer;

    public $id;
    public $settings;
    public $params;
    public $email;

    public function __construct(Explorer $database, Mailer $mailer)
    {
        $this->database = $database;
        $this->mailer = $mailer;
    }

    /**
     * Sets identificator for the helpdesk table
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets customer e-mail. Other e-mails are set in database
     * @param $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }


    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Sets parameters for e-mail template
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        $params = $this->params;

        $params['ipaddress'] = getenv('REMOTE_ADDR');
        $params['time'] = date('Y-m-d H:i');
        $params['settings'] = $this->getSettings();
        $params['email'] = $this->getEmail();

        return $params;
    }

    /**
     * Get information about helpdesk
     * @return bool|mixed|ActiveRow|IRow
     */
    public function getInfo()
    {
        return $this->database->table('helpdesk')->get($this->id);
    }

    /**
     * Send information to all e-mails
     */
    public function send()
    {
        try {
            $info = $this->getInfo();
            if (!$info) {
                $this->reportFailure('configuration', 'missing_helpdesk');
                return false;
            }
            $templateId = 1;
            $email = $this->getEmail();

            if ($info->email !== null) {
                $email = $info->email;
            }

            if ($info->helpdesk_templates_id) {
                $templateId = $info->helpdesk_templates_id;
            }

            $send = $this->fillEmail($email, $info->subject, $info->body, $templateId, $this->getParams(), $info->log);

            return $send;
        } catch (\Throwable $error) {
            $this->reportFailure('configuration', 'unavailable');
            return false;
        }
    }

    /**
     * Sending the mail
     * @param $email
     * @param $subject
     * @param $body
     * @param $templateId
     * @param $params
     * @param $log
     * @return bool
     */
    private function fillEmail($email, $subject, $body, $templateId, $params, $log): bool
    {
        $emailMessage = null;
        $operation = 'render';
        try {
            $latte = new Engine();
            $latte->setLoader(new StringLoader());
            $emailMessage = $latte->renderToString($this->renderBody($subject, $body, $templateId), $params);
            $operation = 'message';
            $sender = $this->getSettings()['contacts:email:hq'] ?? null;
            if (!is_string($sender) || $sender === '') { throw new \RuntimeException('Mail sender unavailable.'); }
            $mail = new Message();
            $mail->setFrom($sender);
            $mail->addTo($email);
            $mail->setHtmlBody($emailMessage);
            $operation = 'transport';
            $this->mailer->send($mail);
            $status = 1;
            $send = true;
        } catch (\Throwable $e) {
            $status = 2;
            $send = false;
            $this->reportFailure($operation, 'failed', (int) $templateId);
        }

        if ($log && $emailMessage !== null) {
            try { $this->log($email, $emailMessage, $status); }
            catch (\Throwable $error) {
                // Delivery already has an outcome. An archive failure must not
                // turn a successful send into a retry and duplicate delivery.
                $this->reportFailure('archive', 'failed', (int) $templateId);
            }
        }

        return $send;
    }

    /**
     *
     * @param $subjectContent
     * @param $bodyContent
     * @param $templateId
     * @return mixed
     */
    public function renderBody($subjectContent, $bodyContent, $templateId)
    {
        $template = $this->database->table('helpdesk_templates')->get($templateId);
        if (!$template) { throw new \RuntimeException('Mail template unavailable.'); }
        return self::composeDocument($template->document, (string) $subjectContent, (string) $bodyContent);
    }

    /**
     * The subject is typed text: escape it into the frame so '&' and '<' reach
     * the mail header unchanged (Latte tags in it still run). The body is
     * trusted Latte source.
     */
    public static function composeDocument(string $frame, string $subject, string $body): string
    {
        $subject = htmlspecialchars($subject, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
        return str_replace(['%TITLE%', '%CONTENT%'], [$subject, $body], $frame);
    }

    /**
     * WYSIWYG editors (Summernote) and HTML normalizers escape text inside
     * Latte tags: '&&' becomes '&amp;&amp;' and '>' '&gt;', which no longer
     * compiles. Decode entities inside Latte tags only.
     */
    public static function normalizeTemplateSource(string $source): string
    {
        $source = preg_replace_callback('~\{(?![\s}])[^{}]*\}~', static function (array $tag): string {
            return str_replace("\u{A0}", ' ', html_entity_decode($tag[0], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }, $source);
        // n: attribute values are PHP too; keep their quote delimiters intact.
        return preg_replace_callback('~(\bn:[\w-]+\s*=\s*)(["\'])(.*?)\2~s', static function (array $attr): string {
            $value = str_replace(['&amp;', '&lt;', '&gt;', '&nbsp;', "\u{A0}"], ['&', '<', '>', ' ', ' '], $attr[3]);
            return $attr[1] . $attr[2] . $value . $attr[2];
        }, $source);
    }

    /**
     * Reason a template body cannot be saved, or null. Templates are trusted
     * Latte source and are not HTML-purified (that would break Latte logic and
     * strip href="{$url}" links), but active content is rejected and the whole
     * composed document must compile to valid PHP before it replaces a template.
     */
    public static function templateError(string $body, string $document): ?string
    {
        try {
            $active = self::activeContent($body);
            if ($active !== null) {
                return sprintf('Šablona obsahuje aktivní obsah („%s“): skripty, vložené objekty, formuláře, značky meta/link/svg/math,'
                    . ' obsluhu událostí (on…), adresy javascript:/vbscript:/data:, n:attr s počítaným názvem atributu,'
                    . ' n:tag nebo změnu syntaxe Latte. Odstraňte ho a uložte znovu.', $active);
            }
            $latte = new Engine();
            $latte->setLoader(new StringLoader());
            token_get_all($latte->compile($document), TOKEN_PARSE);
        } catch (\Latte\CompileException | \Latte\RegexpException | \ParseError $e) {
            return 'Šablonu nelze zpracovat (chyba v Latte značkách, např. {if}…{/if}). Nic nebylo uloženo.';
        }
        return null;
    }

    /**
     * The subject is compiled with the body, so another tag syntax in it would
     * change what activeContent() saw there ('{syntax double}' with 'on{{* c *}}error=').
     * helpdesk.subject is varchar(250); strict MariaDB refuses a longer one with a 500.
     */
    public static function subjectError(string $subject): ?string
    {
        if (mb_strlen($subject, 'UTF-8') > self::SUBJECT_LENGTH) {
            return sprintf('Předmět může mít nejvýše %d znaků (má %d). Nic nebylo uloženo.',
                self::SUBJECT_LENGTH, mb_strlen($subject, 'UTF-8'));
        }

        return stripos($subject, '{syntax') !== false
            ? 'Předmět nesmí měnit syntaxi Latte ({syntax …}). Odstraňte to a uložte znovu.'
            : null;
    }

    /**
     * First fragment of a template body that looks like active markup, or null.
     * The rules are context-free: they also match in text, comments and
     * attribute values, so they can over-reject, but no HTML structure (a '>'
     * inside a quoted value, a comment, a raw-text element) can hide a match.
     * They run on templateMarkup(), and in each rule '@' stands for TAG_GAP, so
     * a word still matches when Latte tags split it: 'on{* c *}error=',
     * 'on{if 0}-{/if}error=' and '{if 0}-{/if}onerror=' all render 'onerror='.
     * This is a best-effort guard for direct markup under the default syntax, not a
     * sandbox: docs/ADMIN_HTML_POLICY.md lists what it doesn't see.
     *
     * @throws \Latte\CompileException if the body is not valid Latte
     */
    public static function activeContent(string $body): ?string
    {
        // Another tag syntax would make the tokenizer see other tags, and n:tag
        // names the element at render time ('<div n:tag="\'iframe\'" src=…>').
        // n:tag-if only keeps or removes the element.
        if (preg_match('~\{\s*syntax\b|\bn:syntax\b|\bn:tag(?![\w-])~i', $body, $match)) {
            return self::fragment($match[0]);
        }
        $markup = self::templateMarkup($body);
        // Browsers also decode numeric references without ';' and drop tab,
        // CR and LF anywhere in a URL ('java&#9script:' is 'javascript:').
        $decoded = preg_replace_callback('~&#(?:x([0-9a-f]+)|([0-9]+));?~i', static function (array $ref): string {
            $digits = ltrim($ref[1] !== '' ? $ref[1] : $ref[2], '0');
            $code = strlen($digits) > 7 ? 0 : (int) ($ref[1] !== '' ? hexdec($digits ?: '0') : $digits);
            return $code > 0 && $code <= 0x10FFFF ? (string) mb_chr($code, 'UTF-8') : '';
        }, $markup);
        $decoded = str_replace(["\t", "\r", "\n"], '', html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $words = static function (array $words): string {
            return implode('|', array_map(static function (string $word): string {
                return implode('@', str_split($word));
            }, $words));
        };
        $elements = $words(['script', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet', 'base', 'form', 'meta', 'link', 'svg', 'math']);
        $schemes = $words(['javascript', 'vbscript', 'data']);
        $rules = [
            '<@\s*@/?@\s*@(?:' . $elements . ')\b' => $markup,
            // An attribute name starts after whitespace, '/' or a closing quote
            // ('<svg/onload=', '<img src="x"onerror='). '[', '(', ',' cover
            // n:attr keys, a tag in front of it any Latte tag inside an element
            // ('{if $x}onclick='). The rest of the name may come from a tag ('on{$x}=').
            '[\s/"\'\[(,}\x01\x02]@o@n(?=[a-z\x01\x02])@(?:[a-z]@)*["\']?@\s*@=' => $markup,
            // Any attribute or n:attr value ('=' or '=>') whose URL has such a scheme.
            '=>?@(?:[\x00\x03-\x20"\'`]+@)*(?:' . $schemes . ')@:' => $decoded,
            // A tag inside a character reference ('java&#11{if 0}-{/if}5;cript:')
            // would hide the reference from the decoding above.
            '&(?:#[0-9a-z]*[\x01\x02]|[a-z0-9]*[\x01\x02][^\s<>;&]*;)' => $markup,
        ];
        foreach ($rules as $rule => $subject) {
            $found = preg_match('~' . str_replace('@', self::TAG_GAP, $rule) . '~i', $subject, $match);
            if ($found !== 0) {
                // false: too many tags to check within PCRE's limits, so refuse.
                return self::fragment($found === false ? $body : $match[0]);
            }
        }
        return self::computedAttrKey(str_replace([self::CONTROL_TAG, self::PRINT_TAG], '', $markup));
    }

    /**
     * The body as Latte's own tokenizer reads it, so strings and nested braces
     * can't pass for comments or tags: comments are gone, {l} and {r} are the
     * braces they print, and other tags are replaced by CONTROL_TAG or PRINT_TAG.
     */
    private static function templateMarkup(string $body): string
    {
        $markup = '';
        foreach ((new \Latte\Parser)->parse($body) as $token) {
            if ($token->type === \Latte\Token::MACRO_TAG) {
                $markup .= ['l' => '{', 'r' => '}', '=' => self::PRINT_TAG, '_' => self::PRINT_TAG][$token->name] ?? self::CONTROL_TAG;
            } elseif ($token->type !== \Latte\Token::COMMENT) {
                $markup .= $token->text;
            }
        }
        return $markup;
    }

    /** A matched fragment for the error message, with tags shown as {…}. */
    private static function fragment(string $text): string
    {
        return mb_substr(trim(str_replace([self::CONTROL_TAG, self::PRINT_TAG], '{…}', $text)), 0, 40, 'UTF-8');
    }

    /**
     * n:attr that is not a plain list of 'literal name => value', or null.
     * Literal names ('title => …', "'title' => …") are checked by the on… rule;
     * a computed name ("'on'.'click' => …"), an array variable, a spread
     * ('...$a', '(expand) $a') or an array operator ('$a + […]') is not visible
     * to it. No seeded template uses n:attr.
     */
    private static function computedAttrKey(string $source): ?string
    {
        preg_match_all('~\bn:attr\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]*))~i', $source, $found, PREG_SET_ORDER);
        foreach ($found as $match) {
            $list = trim(html_entity_decode(implode('', array_slice($match, 1)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if (!self::literalAttrList($list)) {
                return self::fragment($match[0]);
            }
        }
        return null;
    }

    private static function literalAttrList(string $list): bool
    {
        if (($list[0] ?? '') === '[' && substr($list, -1) === ']') {
            $list = substr($list, 1, -1); // '[a => 1] + [b => 2]' then fails on its depth.
        }
        preg_match_all('~\'(?:[^\'\\\\]|\\\\.)*+\'|"(?:[^"\\\\]|\\\\.)*+"|.~s', $list, $tokens);
        $items = [''];
        $depth = 0;
        foreach ($tokens[0] as $token) {
            $depth += ['[' => 1, '(' => 1, '{' => 1, ']' => -1, ')' => -1, '}' => -1][$token] ?? 0;
            if ($depth < 0) {
                return false;
            }
            if ($token === ',' && $depth === 0) {
                $items[] = '';
            } else {
                $items[count($items) - 1] .= $token;
            }
        }
        if (trim(end($items)) === '' && count($items) > 1) {
            array_pop($items); // A trailing comma.
        }
        foreach ($items as $item) {
            if (!preg_match('~^\s*(?:[a-z_][\w:-]*|\'[^\'\\\\]*\'|"[^"\\\\]*")\s*=>~i', $item)) {
                return false;
            }
        }
        return $depth === 0;
    }

    /**
     * Loging information
     * @param $email
     * @param $emailMessage
     */
    public function log($email, $emailMessage, $status)
    {
        $this->database->table('helpdesk_messages')->insert([
            // Templates may expand variables/noescape after the editor save.
            // Sanitize the rendered archive at its HTML persistence boundary.
            'message' => \App\Security\HtmlContent::purify($emailMessage),
            'helpdesk_id' => $this->getId(),
            'email' => $email,
            'ipaddress' => $this->getParams()['ipaddress'],
            'date_created' => $this->getParams()['time'],
            'status' => $status,
        ]);
    }

    /** Diagnostic log is independent of opt-in message-body retention. */
    public function reportFailure(string $operation, string $category, ?int $templateId = null): void
    {
        $operation = in_array($operation, ['configuration', 'render', 'message', 'transport', 'archive', 'return_page', 'validation'], true) ? $operation : 'unknown';
        $category = in_array($category, ['missing_helpdesk', 'unavailable', 'failed', 'missing_page'], true) ? $category : 'failed';
        $id = is_scalar($this->id) && preg_match('/^[1-9][0-9]*$/D', (string) $this->id) ? (int) $this->id : null;
        \Tracy\Debugger::log(json_encode(['event' => 'helpdesk.failure', 'operation' => $operation,
            'helpdesk_id' => $id, 'template_id' => $templateId && $templateId > 0 ? $templateId : null,
            'category' => $category]), \Tracy\Debugger::ERROR);
    }

    public function getReturnPath(): string
    {
        try {
            $info = $this->getInfo();
            $page = $info && $info->pages_id ? $this->database->table('pages')->get($info->pages_id) : null;
            if ($page && $page->slug !== '') {
                // Browsers drop control characters and treat '\' as '/', so remove
                // them all before ensuring a single leading slash (no '//host').
                return '/' . ltrim(preg_replace('~[\x00-\x20\x7F\\\\]~', '', (string) $page->slug), '/');
            }
            $this->reportFailure('return_page', 'missing_page');
        } catch (\Throwable $error) { $this->reportFailure('return_page', 'unavailable'); }
        return '/';
    }
}
