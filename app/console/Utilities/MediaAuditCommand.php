<?php
declare(strict_types=1);
namespace App\Console;

use App\Model\MediaAudit;
use Nette\Database\Explorer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class MediaAuditCommand extends Command
{
    protected static $defaultName = 'media:audit';
    private Explorer $database;
    public function __construct(Explorer $database)
    {
        $this->database = $database;
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->setDescription('Read-only page media consistency report; no repairs.')
            ->addOption('details', null, InputOption::VALUE_NONE, 'Include record IDs and relative paths.');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $settings = $this->database->table('settings')->fetchPairs('setkey', 'setvalue');
        $report = MediaAudit::inspect($this->database, APP_DIR, (string) ($settings['media_thumb_dir'] ?? 'tn'));
        $output->writeln(json_encode($input->getOption('details') ? $report : array_map('count', $report), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), OutputInterface::OUTPUT_RAW);
        return 0;
    }
}
