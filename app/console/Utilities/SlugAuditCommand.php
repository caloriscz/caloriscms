<?php
declare(strict_types=1);
namespace App\Console;

use Nette\Database\Explorer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SlugAuditCommand extends Command
{
    protected static $defaultName = 'pages:audit-slugs';
    private Explorer $database;
    public function __construct(Explorer $database) { $this->database = $database; parent::__construct(); }
    protected function configure(): void { $this->setDescription('Read-only duplicate slug counts per language column; no content or repairs.'); }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $report = [];
        foreach ($this->database->getStructure()->getColumns('pages') as $column) {
            $name = $column['name'];
            if (!preg_match('/^slug(?:_[a-z]{2})?$/D', $name)) { continue; }
            // Validated identifiers; grouping uses the actual database collation.
            $report[$name] = (int) $this->database->query('SELECT COUNT(*) FROM
                (SELECT `' . $name . '` FROM pages WHERE `' . $name . '` IS NOT NULL AND `' . $name . '` <> \'\'
                 GROUP BY `' . $name . '` HAVING COUNT(*) > 1) AS duplicates')->fetchField();
        }
        $output->writeln(json_encode($report, JSON_PRETTY_PRINT), OutputInterface::OUTPUT_RAW);
        return 0;
    }
}
