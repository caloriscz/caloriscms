<?php
declare(strict_types=1);

namespace App\ApiModule\Presenters;

class MePresenter extends BasePresenter
{
    public function actionDefault(): void
    {
        $this->requireScope('pages:read');
        $identity = $this->getApiIdentity();

        $this->sendApiResponse([
            'user' => [
                'id' => (int) $identity->user->id,
                'username' => $identity->user->username,
                'name' => $identity->user->name,
                'email' => $identity->user->email,
                'users_roles_id' => $identity->user->users_roles_id !== null ? (int) $identity->user->users_roles_id : null,
            ],
            'token' => [
                'id' => (int) $identity->token->id,
                'name' => $identity->token->name,
                'scopes' => $identity->getScopes(),
            ],
        ]);
    }
}
