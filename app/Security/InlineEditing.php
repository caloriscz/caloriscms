<?php
declare(strict_types=1);

namespace App\Security;

use Nette\Application\BadRequestException;
use Nette\Database\Explorer;
use Nette\Utils\Strings;

final class InlineEditing
{
    public static function allows(array $member, array $role): bool
    {
        return (int) ($member['state'] ?? 0) === 1
            && AdminPermissions::allows($role, 'pages');
    }

    /** IDs and content come only from POST, never query-string fallbacks. */
    public static function save(Explorer $database, string $kind, $id, $text, string $locale, string $defaultLocale): string
    {
        if (!is_string($id) || !preg_match('/^[1-9][0-9]{0,9}$/D', $id)
            || !is_string($text) || !Strings::checkEncoding($text)) {
            throw new BadRequestException('Invalid inline edit.', 400);
        }
        $table = $kind === 'title' ? 'pages' : 'snippets';
        $field = $kind === 'title' ? 'title' : 'content';
        if ($locale !== $defaultLocale) {
            if (!preg_match('/^[a-z]{2}$/D', $locale)
                || !$database->table('languages')->where('code', $locale)->where('used', 1)->fetch()) {
                throw new BadRequestException('Unsupported language.', 400);
            }
            $field .= '_' . $locale;
        }
        $row = $database->table($table)->get((int) $id);
        if (!$row) {
            throw new BadRequestException('Content not found.', 404);
        }
        if (!array_key_exists($field, $row->toArray())) {
            throw new BadRequestException('Translation is not installed.', 400);
        }
        if ($kind === 'title') {
            if ((int) $row->editable !== 1) {
                throw new \Nette\Application\ForbiddenRequestException('Page is not editable.');
            }
            $text = trim($text);
            if ($text === '' || Strings::length($text) > 250 || preg_match('/[<>\x00-\x1f\x7f]/u', $text)) {
                throw new BadRequestException('Title must be plain text, 1–250 characters.', 400);
            }
        } else {
            if (strlen($text) > 60000) {
                throw new BadRequestException('Snippet is too long.', 400);
            }
            // Same HTML policy as EditorControl and ContentApiService.
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('HTML.AllowedAttributes', 'img.src,*.style,*.class');
            $config->set('Attr.AllowedClasses', 'col-*,container,text-right, text-left, btn, btn-lg');
            $config->set('HTML.ForbiddenElements', ['font']);
            $config->set('AutoFormat.RemoveEmpty', true);
            $text = (new \HTMLPurifier($config))->purify($text);
        }
        $row->update([$field => $text]);
        return $text;
    }
}
