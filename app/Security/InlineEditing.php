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
        if ($kind === 'title' && (int) $row->editable !== 1) {
            throw new \Nette\Application\ForbiddenRequestException('Page is not editable.');
        }
        if ($kind === 'title') {
            // The schema is utf8mb3: a 4-byte character (emoji) would fail in SQL
            // with a 500. A title is plain text, so it can't be a numeric reference.
            if (preg_match('/[\x{10000}-\x{10FFFF}]/u', $text)) {
                throw new BadRequestException('Characters such as emoji are not supported.', 400);
            }
            // contenteditable turns a typed space into NBSP; trim() misses it, as
            // well as zero-width and bidi format characters that look blank.
            $text = preg_replace('/^[\s\p{Z}]+|[\s\p{Z}]+$/u', '', $text);
            if (preg_replace('/[\s\p{Z}\p{Cf}]+/u', '', $text) === '' || Strings::length($text) > 250
                || preg_match('/[<>\x00-\x1f\x7f\x{2028}\x{2029}]/u', $text)) {
                throw new BadRequestException('Title must be plain text, 1–250 characters.', 400);
            }
        } else {
            if (strlen($text) > 60000) {
                throw new BadRequestException('Snippet is too long.', 400);
            }
            $text = HtmlContent::purify($text);
            if (HtmlContent::textSizeError($text) !== null) {
                throw new BadRequestException('Snippet is too long.', 400);
            }
        }
        $row->update([$field => $text]);
        return $text;
    }
}
