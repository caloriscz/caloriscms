<?php
declare(strict_types=1);

// Isolated php -S fixture only: no application config, database or SMTP.
if (PHP_SAPI !== 'cli-server' || !getenv('CALORIS_ERROR_HTTP_FIXTURE_DIR')) {
    exit(1);
}
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../app/Security/ErrorContext.php';
require __DIR__ . '/../../app/FrontModule/presenters/ErrorPresenter.php';

if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/health') {
    echo 'ready';
    return;
}

$status = (int) ($_GET['status'] ?? 500);
$late = ($_GET['late'] ?? '') === '1';
if (!in_array($status, [400, 403, 404, 405, 410, 500], true)) {
    http_response_code(400);
    return;
}
$request = (new Nette\Http\RequestFactory())->fromGlobals();
$httpResponse = new Nette\Http\Response();
$logger = new class implements Tracy\ILogger {
    public array $entries = [];
    public function log($message, $level = self::INFO) {
        $this->entries[] = [$message instanceof Throwable ? get_class($message) . ': ' . $message->getMessage() : $message, $level];
    }
};
$templates = new class implements Nette\Application\UI\TemplateFactory {
    public function createTemplate(?Nette\Application\UI\Control $control = null): Nette\Application\UI\Template {
        $latte = new Latte\Engine();
        $latte->onCompile[] = static function ($latte): void {
            Nette\Bridges\ApplicationLatte\UIMacros::install($latte->getCompiler());
        };
        $latte->addProvider('uiControl', $control);
        $latte->addProvider('uiPresenter', $control);
        $template = new Nette\Bridges\ApplicationLatte\DefaultTemplate($latte);
        $template->basePath = '';
        return $template;
    }
};
$factory = new class($request, $httpResponse, $logger, $templates, $status, $late) implements Nette\Application\IPresenterFactory {
    private array $args;
    public function __construct(...$args) { $this->args = $args; }
    public function getPresenterClass(string &$name): string {
        return App\FrontModule\Presenters\ErrorPresenter::class;
    }
    public function createPresenter(string $name): Nette\Application\IPresenter {
        [$request, $response, $logger, $templates, $status, $late] = $this->args;
        if ($name === 'Front:Error') {
            $presenter = new App\FrontModule\Presenters\ErrorPresenter($logger);
            $presenter->injectPrimary(null, null, null, $request, $response, null, null, $templates);
            return $presenter;
        }
        return new class($status, $late, $response) implements Nette\Application\IPresenter {
            private int $status;
            private bool $late;
            private Nette\Http\IResponse $response;
            public function __construct(int $status, bool $late, Nette\Http\IResponse $response) {
                $this->status = $status;
                $this->late = $late;
                $this->response = $response;
            }
            public function run(Nette\Application\Request $request): Nette\Application\Response {
                if ($this->late) {
                    $this->response->setCode(202);
                    $this->response->setContentType('text/plain', 'utf-8');
                    $this->response->setHeader('Cache-Control', 'private');
                    echo 'already-sent-prefix';
                    flush();
                    if (!$this->response->isSent()) {
                        throw new LogicException('Fixture did not flush HTTP headers');
                    }
                }
                throw $this->status === 500
                    ? new RuntimeException('private-original-failure')
                    : new Nette\Application\BadRequestException('private-request-detail', $this->status);
            }
        };
    }
};
$router = new class implements Nette\Routing\Router {
    public function match(Nette\Http\IRequest $request): ?array {
        return ['presenter' => 'Admin:Pages', 'action' => 'detail', 'id' => '42', 'do' => 'pageList-delete', 'token' => 'private-token'];
    }
    public function constructUrl(array $params, Nette\Http\UrlScript $refUrl): ?string { return null; }
};
$app = new Nette\Application\Application($factory, $router, $request, $httpResponse);
$app->catchExceptions = true;
$app->errorPresenter = 'Front:Error';
$errors = [];
$app->onError[] = static function ($app, Throwable $error) use (&$errors): void {
    $errors[] = get_class($error);
};
try {
    $app->run();
} catch (Throwable $error) {
    echo 'SECONDARY-ERROR:' . get_class($error);
} finally {
    file_put_contents(getenv('CALORIS_ERROR_HTTP_FIXTURE_DIR') . '/result.json', json_encode([
        'errors' => $errors, 'logs' => $logger->entries, 'sent' => $httpResponse->isSent(),
    ]));
}
