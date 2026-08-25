<?php
declare(strict_types=1);

namespace App\Console\Api;

use Nette\Database\Explorer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApiTokenRevokeCommand extends Command
{
    protected static $defaultName = 'api:token:revoke';

    private Explorer $database;

    public function __construct(Explorer $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    protected function configure(): void
    {
        $this->setName('api:token:revoke')
            ->setDescription('Revoke a personal API bearer token')
            ->addArgument('token-id', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $token = $this->database->table('api_tokens')->get((int) $input->getArgument('token-id'));

        if (!$token) {
            $output->writeln('<error>Token not found</error>');
            return 1;
        }

        $token->update(['revoked_at' => date('Y-m-d H:i:s')]);
        $output->writeln('<comment>API token revoked</comment>');

        return 0;
    }
}
