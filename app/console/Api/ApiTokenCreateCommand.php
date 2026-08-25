<?php
declare(strict_types=1);

namespace App\Console\Api;

use App\Model\Api\ApiTokenAuthenticator;
use Nette\Database\Explorer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ApiTokenCreateCommand extends Command
{
    protected static $defaultName = 'api:token:create';

    private Explorer $database;
    private ApiTokenAuthenticator $tokenAuthenticator;

    public function __construct(Explorer $database, ApiTokenAuthenticator $tokenAuthenticator)
    {
        parent::__construct();
        $this->database = $database;
        $this->tokenAuthenticator = $tokenAuthenticator;
    }

    protected function configure(): void
    {
        $this->setName('api:token:create')
            ->setDescription('Create a personal API bearer token')
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('name', InputArgument::REQUIRED)
            ->addOption('scopes', null, InputOption::VALUE_REQUIRED, 'Comma-separated scopes', 'pages:read,pages:write,media:read')
            ->addOption('expires', null, InputOption::VALUE_REQUIRED, 'Expiration date as YYYY-MM-DD');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this->database->table('users')->where('username', $input->getArgument('username'))->fetch();

        if (!$user) {
            $output->writeln('<error>User not found</error>');
            return 1;
        }

        $rawToken = 'caloris_' . bin2hex(random_bytes(32));
        $expires = $input->getOption('expires');

        $row = $this->database->table('api_tokens')->insert([
            'users_id' => $user->id,
            'name' => $input->getArgument('name'),
            'token_hash' => $this->tokenAuthenticator->hashToken($rawToken),
            'scopes' => $input->getOption('scopes'),
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => $expires ? $expires . ' 23:59:59' : null,
        ]);

        $output->writeln('<comment>API token created. Copy it now; it will not be shown again.</comment>');
        $output->writeln('id: ' . $row->id);
        $output->writeln('token: ' . $rawToken);

        return 0;
    }
}
