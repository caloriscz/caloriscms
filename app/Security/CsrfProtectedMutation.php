<?php
declare(strict_types=1);

namespace App\Security;

use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Presenter;

trait CsrfProtectedMutation
{
    protected function getCsrfToken(): string
    {
        $section = $this->getCsrfPresenter()->getSession('Caloris.Admin.Csrf');
        $token = $section->mutationToken;

        if (!is_string($token) || strlen($token) !== 64) {
            $token = CsrfToken::generate();
            $section->mutationToken = $token;
        }

        return $token;
    }

    protected function requireCsrfToken(): void
    {
        $request = $this->getCsrfPresenter()->getHttpRequest();
        $submittedToken = $request->getPost('_csrf') ?? $request->getHeader('X-CSRF-Token');

        if (!CsrfToken::validatesRequest($request->getMethod(), $submittedToken, $this->getCsrfToken())) {
            throw new ForbiddenRequestException('Invalid CSRF token.');
        }
    }

    private function getCsrfPresenter(): Presenter
    {
        return $this instanceof Presenter ? $this : $this->getPresenter();
    }
}
