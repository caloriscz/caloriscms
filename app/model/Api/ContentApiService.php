<?php
declare(strict_types=1);

namespace App\Model\Api;

use InvalidArgumentException;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\Strings;

class ContentApiService
{
    private const PAGE_WRITE_FIELDS = [
        'slug',
        'title',
        'document',
        'preview',
        'pages_id',
        'metadesc',
        'metakeys',
        'date_published',
        'pages_types_id',
        'pages_templates_id',
        'sitemap',
    ];

    private const PAGE_TEXT_LENGTHS = [
        'title' => 250,
        'slug' => 250,
        'metadesc' => 200,
        'metakeys' => 150,
    ];

    private const RESERVED_SLUGS = [
        'blog',
        'cart',
        'catalogue',
        'contacts',
        'document',
        'documents',
        'error',
        'events',
        'gallery',
        'helpdesk',
        'homepage',
        'links',
        'order',
        'orders',
        'pricelist',
        'product',
        'profile',
        'services',
        'sign',
    ];

    private Explorer $database;
    private \HTMLPurifier $htmlPurifier;

    public function __construct(Explorer $database)
    {
        $this->database = $database;

        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.AllowedAttributes', 'img.src,*.style,*.class');
        $config->set('Attr.AllowedClasses', 'col-*,container,text-right, text-left, btn, btn-lg');
        $config->set('HTML.ForbiddenElements', ['font']);
        $config->set('AutoFormat.RemoveEmpty', true);

        $this->htmlPurifier = new \HTMLPurifier($config);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listPages(array $filters): array
    {
        $selection = $this->database->table('pages')->order('sorted ASC, id ASC');

        foreach (['public', 'pages_id', 'pages_types_id'] as $field) {
            if (array_key_exists($field, $filters) && $filters[$field] !== null && $filters[$field] !== '') {
                $selection->where($field, (int) $filters[$field]);
            }
        }

        if (!empty($filters['q'])) {
            $selection->where('title LIKE ? OR slug LIKE ?', '%' . $filters['q'] . '%', '%' . $filters['q'] . '%');
        }

        $limit = isset($filters['limit']) ? max(1, min(100, (int) $filters['limit'])) : 50;
        $offset = isset($filters['offset']) ? max(0, (int) $filters['offset']) : 0;
        $selection->limit($limit, $offset);

        $pages = [];
        foreach ($selection as $page) {
            $pages[] = $this->formatPage($page);
        }

        return $pages;
    }

    public function getPage(int $id): ?array
    {
        $page = $this->database->table('pages')->get($id);
        return $page ? $this->formatPage($page) : null;
    }

    public function createPage(array $data, int $userId): array
    {
        $payload = $this->preparePageData($data, null, true);
        $payload['users_id'] = $userId;
        $payload += [
            'public' => 0,
            'date_created' => date('Y-m-d H:i:s'),
        ];

        $page = $this->database->table('pages')->insert($payload);
        return $this->formatPage($page);
    }

    public function updatePage(int $id, array $data): ?array
    {
        $page = $this->database->table('pages')->get($id);

        if (!$page) {
            return null;
        }

        $payload = $this->preparePageData($data, $id, false);

        if ($payload !== []) {
            $page->update($payload);
            $page = $this->database->table('pages')->get($id);
        }

        return $this->formatPage($page);
    }

    public function unpublishPage(int $id): bool
    {
        $page = $this->database->table('pages')->get($id);

        if (!$page) {
            return false;
        }

        $page->update(['public' => 0]);
        return true;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listMedia(array $filters): array
    {
        $selection = $this->database->table('media')->order('sorted ASC, id ASC');

        if (!empty($filters['pages_id'])) {
            $selection->where('pages_id', (int) $filters['pages_id']);
        }

        $items = [];
        foreach ($selection as $media) {
            $items[] = $this->formatMedia($media);
        }

        return $items;
    }

    public function findUnknownPageField(array $data): ?string
    {
        foreach (array_keys($data) as $field) {
            if (!in_array($field, self::PAGE_WRITE_FIELDS, true)) {
                return $field;
            }
        }

        return null;
    }

    private function preparePageData(array $data, ?int $currentPageId, bool $isCreate): array
    {
        $payload = [];

        foreach ($data as $field => $value) {
            switch ($field) {
                case 'title':
                    $payload['title'] = $this->requireText($field, $value, self::PAGE_TEXT_LENGTHS[$field]);
                    break;
                case 'slug':
                    $payload['slug'] = $this->normalizeSlug($value, $currentPageId, true);
                    break;
                case 'document':
                    $payload['document'] = $this->purifyHtml($this->requireString($field, $value));
                    break;
                case 'preview':
                    $payload['preview'] = $this->purifyHtml($this->requireString($field, $value));
                    break;
                case 'metadesc':
                case 'metakeys':
                    $payload[$field] = $this->nullableText($field, $value, self::PAGE_TEXT_LENGTHS[$field]);
                    break;
                case 'date_published':
                    $payload[$field] = $this->normalizeDate($field, $value);
                    break;
                case 'pages_id':
                    $payload[$field] = $this->normalizePageParent($value, $currentPageId);
                    break;
                case 'pages_types_id':
                    $payload[$field] = $this->requireExistingId('pages_types', $field, $value);
                    break;
                case 'pages_templates_id':
                    $payload[$field] = $this->normalizeTemplate($value);
                    break;
                case 'sitemap':
                    $payload[$field] = $this->normalizeBoolean($field, $value);
                    break;
            }
        }

        if ($isCreate && !isset($payload['title'])) {
            throw new InvalidArgumentException('Field title is required');
        }

        if ($isCreate && !isset($payload['slug'])) {
            $payload['slug'] = $this->normalizeSlug($payload['title'], null, false);
        }

        return $payload;
    }

    private function requireString(string $field, $value): string
    {
        if (!is_string($value) && !is_numeric($value)) {
            throw new InvalidArgumentException('Field ' . $field . ' must be a string');
        }

        return trim((string) $value);
    }

    private function requireText(string $field, $value, int $maxLength): string
    {
        $text = $this->requireString($field, $value);

        if ($text === '') {
            throw new InvalidArgumentException('Field ' . $field . ' cannot be empty');
        }

        if (Strings::length($text) > $maxLength) {
            throw new InvalidArgumentException('Field ' . $field . ' is too long');
        }

        return $text;
    }

    private function nullableText(string $field, $value, int $maxLength): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->requireText($field, $value, $maxLength);
    }

    private function purifyHtml(string $html): string
    {
        return $this->htmlPurifier->purify($html);
    }

    private function normalizeSlug($value, ?int $currentPageId, bool $rejectDuplicate): string
    {
        $source = $this->requireText('slug', $value, self::PAGE_TEXT_LENGTHS['slug']);
        $slug = Strings::webalize($source);

        if ($slug === '') {
            throw new InvalidArgumentException('Field slug cannot be empty after normalization');
        }

        if (in_array($slug, self::RESERVED_SLUGS, true)) {
            $slug .= '-name';
        }

        if (Strings::length($slug) > self::PAGE_TEXT_LENGTHS['slug']) {
            throw new InvalidArgumentException('Field slug is too long after normalization');
        }

        if ($rejectDuplicate && $this->slugExists($slug, $currentPageId)) {
            throw new InvalidArgumentException('Field slug must be unique');
        }

        if (!$rejectDuplicate) {
            return $this->generateUniqueSlug($slug);
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $currentPageId): bool
    {
        $selection = $this->database->table('pages')->where('slug', $slug);

        if ($currentPageId !== null) {
            $selection->where('NOT id', $currentPageId);
        }

        return $selection->count() > 0;
    }

    private function generateUniqueSlug(string $slug): string
    {
        if (!$this->slugExists($slug, null)) {
            return $slug;
        }

        $i = 1;
        do {
            $candidate = $i . '-' . $slug;
            $i++;
        } while ($this->slugExists($candidate, null));

        return $candidate;
    }

    private function normalizeDate(string $field, $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value) && !is_numeric($value)) {
            throw new InvalidArgumentException('Field ' . $field . ' must be a date string');
        }

        $timestamp = strtotime((string) $value);

        if ($timestamp === false) {
            throw new InvalidArgumentException('Field ' . $field . ' must be a valid date');
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function normalizePageParent($value, ?int $currentPageId): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $id = $this->requireExistingId('pages', 'pages_id', $value);

        if ($currentPageId !== null && $id === $currentPageId) {
            throw new InvalidArgumentException('Field pages_id cannot reference the same page');
        }

        return $id;
    }

    private function normalizeTemplate($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->requireExistingId('pages_templates', 'pages_templates_id', $value);
    }

    private function requireExistingId(string $table, string $field, $value): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new InvalidArgumentException('Field ' . $field . ' must be an integer');
        }

        $id = (int) $value;

        if ($id < 1 || !$this->database->table($table)->get($id)) {
            throw new InvalidArgumentException('Field ' . $field . ' references a missing record');
        }

        return $id;
    }

    private function normalizeBoolean(string $field, $value): int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if ($value === 0 || $value === 1 || $value === '0' || $value === '1') {
            return (int) $value;
        }

        throw new InvalidArgumentException('Field ' . $field . ' must be boolean');
    }

    private function formatPage(ActiveRow $page): array
    {
        return [
            'id' => (int) $page->id,
            'slug' => $page->slug,
            'title' => $page->title,
            'document' => $page->document,
            'preview' => $page->preview,
            'pages_id' => $page->pages_id !== null ? (int) $page->pages_id : null,
            'users_id' => $page->users_id !== null ? (int) $page->users_id : null,
            'public' => (int) $page->public,
            'metadesc' => $page->metadesc,
            'metakeys' => $page->metakeys,
            'date_created' => $page->date_created ? (string) $page->date_created : null,
            'date_published' => $page->date_published ? (string) $page->date_published : null,
            'pages_types_id' => $page->pages_types_id !== null ? (int) $page->pages_types_id : null,
            'pages_templates_id' => $page->pages_templates_id !== null ? (int) $page->pages_templates_id : null,
            'sorted' => (int) $page->sorted,
            'editable' => (int) $page->editable,
            'sitemap' => (int) $page->sitemap,
        ];
    }

    private function formatMedia(ActiveRow $media): array
    {
        return [
            'id' => (int) $media->id,
            'name' => $media->name,
            'file_type' => (int) $media->file_type,
            'filesize' => (int) $media->filesize,
            'pages_id' => $media->pages_id !== null ? (int) $media->pages_id : null,
            'title' => $media->title,
            'description' => $media->description,
            'date_created' => $media->date_created ? (string) $media->date_created : null,
            'detail_view' => (int) $media->detail_view,
            'sorted' => (int) $media->sorted,
            'main_file' => (int) $media->main_file,
        ];
    }
}
