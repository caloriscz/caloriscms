<?php
namespace App\Console;

use App\Model\IO;
use Nette\Database\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheClearCommand extends Command
{
    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * php www/index.php utilities:cache-clear
     */
    protected function configure(): void
    {
        $this->setName('utilities:cache-clear')
            ->setDescription('Sends the newsletter');
    }

    public function getSettings()
    {
        return $this->database->table('settings')->fetchPairs('setkey', 'setvalue');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
           IO::removeDirectory(substr(APP_DIR, 0, -3) . 'temp\cache\latte', true);

            $output->writeln(substr(APP_DIR, 0, -3) . 'temp\cache\latte');
            $output->writeln('<comment>Cache cleared</comment>');
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}