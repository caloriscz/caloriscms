<?php
declare(strict_types=1);

namespace App\Model\Api;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

class ContentApiService
{
    private const PAGE_FIELDS = [
        'slug',
        'title',
        'document',
        'preview',
        'pages_id',
        'users_id',
        'public',
        'metadesc',
        'metakeys',
        'date_created',
        'date_published',
        'pages_types_id',
        'pages_templates_id',
        'sorted',
        'editable',
        'sitemap',
    ];

    private Explorer $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
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
        $payload = $this->filterPageData($data);
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

        $payload = $this->filterPageData($data);

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
            if (!in_array($field, self::PAGE_FIELDS, true)) {
                return $field;
            }
        }

        return null;
    }

    private function filterPageData(array $data): array
    {
        return array_intersect_key($data, array_flip(self::PAGE_FIELDS));
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
