<?php
declare(strict_types=1);

namespace App\Security;

/** Shared rich-text policy of the page editor, content API, inline editing and admin HTML writers. */
final class HtmlContent
{
    /** menu.title and its title_xx columns are varchar(80). */
    public const MENU_LABEL_LENGTH = 80;

    /** snippets.content, links.description and pages.document/preview are TEXT. */
    public const TEXT_COLUMN_BYTES = 65535;

    public static function createPurifier(): \HTMLPurifier
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.AllowedAttributes', 'img.src,img.alt,img.width,img.height,a.href,*.style,*.class,'
            . 'iframe.src,iframe.width,iframe.height,iframe.frameborder');
        // Links and images may be relative or use these schemes only; any other
        // (javascript:, vbscript:, ftp:) drops the attribute. data: is a picture
        // inserted from a file: the filter below keeps it in img src only, and
        // HTMLPurifier keeps it only if the decoded bytes are PNG, JPEG or GIF.
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true, 'data' => true]);
        // YouTube videos as Summernote inserts them; any other iframe loses its
        // src and RemoveEmpty then drops it.
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(?:https?:)?//www\.youtube(?:-nocookie)?\.com/embed/[\w-]+(?:\?[\w=&.-]*)?$%');
        $config->set('Attr.AllowedClasses', 'col-*,container,text-right, text-left, btn, btn-lg');
        $config->set('HTML.ForbiddenElements', ['font']);
        $config->set('AutoFormat.RemoveEmpty', true);
        // HTMLPurifier's default alt is the end of the src, which for a picture
        // from a file is kilobytes of base64.
        $config->set('Attr.DefaultImageAlt', '');
        // Purifying only happens on writes, where rebuilding the definition (about
        // 10 ms) is nothing, and its default cache lives in vendor/, where the web
        // user usually can't write: 4–9 warnings on every purifying request.
        $config->set('Cache.DefinitionImpl', null);
        $purifier = new \HTMLPurifier($config);
        // Added to the loaded definition, so it is never written to the cache.
        $config->getDefinition('URI')->addFilter(new class extends \HTMLPurifier_URIFilter {
            public $name = 'CalorisDataImage';

            public function filter(&$uri, $config, $context)
            {
                if ($uri->scheme !== 'data') {
                    return true;
                }
                $token = $context->get('CurrentToken', true);
                return $token !== null && $token->name === 'img' && $context->get('CurrentAttr', true) === 'src';
            }
        }, $config);
        return $purifier;
    }

    public static function purify(string $html): string
    {
        static $purifier;
        $purifier = $purifier ?? self::createPurifier();
        // The schema is utf8mb3: a 4-byte character (emoji) typed or sent as an
        // entity fails in SQL with a 500, in strict mode, or cuts the text otherwise.
        return self::characterReferences($purifier->purify($html), '/[\x{10000}-\x{10FFFF}]/u');
    }

    /** Purified HTML for a latin1 column (links.description), where 'č' is the same failure. */
    public static function latin1Html(string $html): string
    {
        return self::characterReferences($html, '/[^\x{00}-\x{7F}\x{A0}-\x{FF}]/u');
    }

    /**
     * In HTML output a numeric reference shows the character, and Summernote loads
     * it back as one. Text output doesn't: a preview has to decode it (|stripHtml,
     * not |striptags|truncate), and the maintenance message's plain input shows it
     * as stored.
     */
    private static function characterReferences(string $html, string $pattern): string
    {
        return preg_replace_callback($pattern, static function (array $character): string {
            return '&#' . mb_ord($character[0], 'UTF-8') . ';';
        }, $html);
    }

    /**
     * Stored form of a typed menu label. Most menu views escape labels as text,
     * so plain labels ('A&B', 'I <3 CMS', 'x<y z') are stored as typed. Only a
     * label with a complete tag is markup and goes through the shared purifier.
     */
    public static function menuLabel(string $label): string
    {
        return self::hasMarkup($label) ? self::purify($label) : $label;
    }

    /**
     * Reason a stored label (the result of menuLabel) does not fit its column,
     * or null. Purifying markup turns '&' into '&amp;', so a typed label that
     * fits can still overflow; strict mode would make that a 500.
     */
    public static function menuLabelError(string $stored): ?string
    {
        $length = mb_strlen($stored, 'UTF-8');
        if ($length <= self::MENU_LABEL_LENGTH) {
            return null;
        }
        return sprintf('Název má %d znaků (včetně HTML úprav), povoleno je nejvýše %d. Nic nebylo uloženo.',
            $length, self::MENU_LABEL_LENGTH);
    }

    /**
     * Reason purified HTML does not fit its TEXT column, or null. A picture
     * inserted from a file is stored inline as base64 and easily exceeds the
     * column; strict mode would make that a 500.
     */
    public static function textSizeError(string $html): ?string
    {
        $bytes = strlen($html);
        if ($bytes <= self::TEXT_COLUMN_BYTES) {
            return null;
        }
        return sprintf('Obsah má po úpravě %s bajtů, uložit lze nejvýše %s bajtů. Obrázek vložený ze souboru'
            . ' se ukládá přímo do textu: zmenšete ho, nebo ho nahrajte do správce souborů a vložte odtud. Nic nebylo uloženo.',
            number_format($bytes, 0, ',', ' '), number_format(self::TEXT_COLUMN_BYTES, 0, ',', ' '));
    }

    /** The jsTree client sends node text as escaped HTML ('A&amp;B'). */
    public static function menuLabelFromTree(string $text): string
    {
        return self::menuLabel(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * HTML for raw (noescape) menu sinks such as the navbar. Plain labels are
     * escaped; existing entities are kept so older labels render as before.
     */
    public static function menuLabelHtml(?string $label): string
    {
        $label = (string) $label;
        return self::hasMarkup($label) ? $label : htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
    }

    private static function hasMarkup(string $label): bool
    {
        return preg_match('~<[a-z!/][^<>]*>~i', $label) === 1;
    }
}
