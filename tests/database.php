<?php
declare(strict_types=1);

use Nette\Database\Connection;
use Nette\Database\Explorer;
use Nette\Database\Structure;

// Deliberately hardcoded in-memory DSN: never load application configuration.
function createTestDatabase(): Explorer
{
    if (!extension_loaded('pdo_sqlite')) {
        throw new RuntimeException('Tests require pdo_sqlite. Run composer test in the app container.');
    }

    $connection = new Connection('sqlite::memory:');
    $connection->getPdo()->exec('PRAGMA foreign_keys = ON');
    $connection->getPdo()->exec('
        CREATE TABLE users (id INTEGER PRIMARY KEY, state INTEGER NOT NULL);
        CREATE TABLE api_tokens (
            id INTEGER PRIMARY KEY, users_id INTEGER REFERENCES users(id),
            token_hash TEXT NOT NULL UNIQUE, scopes TEXT NOT NULL,
            expires_at TEXT, revoked_at TEXT, last_used_at TEXT
        );
        CREATE TABLE pages_types (id INTEGER PRIMARY KEY);
        CREATE TABLE pages_templates (id INTEGER PRIMARY KEY);
        CREATE TABLE pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT, slug TEXT NOT NULL, title TEXT NOT NULL,
            document TEXT, preview TEXT, pages_id INTEGER REFERENCES pages(id),
            users_id INTEGER REFERENCES users(id), public INTEGER DEFAULT 0,
            metadesc TEXT, metakeys TEXT, date_created TEXT, date_published TEXT,
            pages_types_id INTEGER REFERENCES pages_types(id),
            pages_templates_id INTEGER REFERENCES pages_templates(id),
            sorted INTEGER DEFAULT 0, editable INTEGER DEFAULT 1, sitemap INTEGER DEFAULT 1
        );
        INSERT INTO users (id, state) VALUES (1, 1), (2, 0);
        INSERT INTO pages_types (id) VALUES (1);
        INSERT INTO pages_templates (id) VALUES (1);
    ');

    return new Explorer($connection, new Structure($connection, new Nette\Caching\Storages\MemoryStorage()));
}
