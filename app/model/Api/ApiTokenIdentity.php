<?php
declare(strict_types=1);

namespace App\Model\Api;

use Nette\Database\Table\ActiveRow;

class ApiTokenIdentity
{
    public ActiveRow $user;
    public ActiveRow $token;

    /** @var string[] */
    private array $scopes;

    /**
     * @param string[] $scopes
     */
    public function __construct(ActiveRow $user, ActiveRow $token, array $scopes)
    {
        $this->user = $user;
        $this->token = $token;
        $this->scopes = $scopes;
    }

    public function hasScope(string $scope): bool
    {
        return in_array('*', $this->scopes, true) || in_array($scope, $this->scopes, true);
    }

    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }
}
