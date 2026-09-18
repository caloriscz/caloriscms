<?php
declare(strict_types=1);
namespace App\Model;

use Nette\Database\Explorer;
use Nette\Utils\Strings;
use RuntimeException;

/** Serialize first-party slug writers before starting their transaction/snapshot. */
final class PageWrites
{
    public static function run(Explorer $db, callable $work)
    {
        $connection = $db->getConnection();
        if ($connection->getPdo()->inTransaction()) {
            throw new \LogicException('Page writes must own their transaction.');
        }
        $mysql = $connection->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'mysql';
        $lock = null;
        if ($mysql) {
            $database = (string) $db->query('SELECT DATABASE()')->fetchField();
            $lock = 'caloris:pages:' . hash('sha1', $database); // Below MySQL's 64-byte limit.
            // MariaDB/MySQL named locks are connection-scoped, not transaction-scoped.
            $deadline = hrtime(true) + 10_000_000_000;
            do {
                $acquired = $db->query('SELECT GET_LOCK(?, 1)', $lock)->fetchField();
                if ((int) $acquired === 1) { break; }
                if ($acquired === null || hrtime(true) >= $deadline) {
                    throw new RuntimeException('Page write is busy or locking is unavailable; please retry.');
                }
                usleep(10000);
            } while (true);
        }
        try {
            return $db->transaction($work);
        } finally {
            if ($lock !== null) { $db->query('SELECT RELEASE_LOCK(?)', $lock); }
        }
    }

    public static function uniqueSlug(Explorer $db, string $source, string $column = 'slug', ?int $except = null): string
    {
        if (!preg_match('/^slug(?:_[a-z]{2})?$/D', $column)) {
            throw new \InvalidArgumentException('Invalid slug language column.');
        }
        $base = Strings::webalize($source);
        $base = $base === '' ? 'page' : substr($base, 0, 250);
        for ($index = 0; ; $index++) {
            $prefix = $index ? $index . '-' : '';
            $candidate = $prefix . substr($base, 0, 250 - strlen($prefix));
            $selection = $db->table('pages')->where($column, $candidate);
            if ($except !== null) { $selection->where('NOT id', $except); }
            if (!$selection->count('*')) { return $candidate; }
        }
    }

    /** Keep the legacy new-page-first ordering, with an explicit tie breaker. */
    public static function renumber(Explorer $db): void
    {
        if ($db->getConnection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $db->query('SET @caloris_page_order = 1');
            $db->query('UPDATE pages SET sorted = (@caloris_page_order := @caloris_page_order + 2) ORDER BY sorted ASC, id ASC');
        } else {
            $position = 1;
            foreach ($db->table('pages')->order('sorted ASC, id ASC') as $row) {
                $position += 2;
                $row->update(['sorted' => $position]);
            }
        }
    }
}
