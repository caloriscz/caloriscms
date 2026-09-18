<?php
declare(strict_types=1);

use App\FrontModule\Presenters\ErrorPresenter;
use App\Security\ErrorContext;
use Nette\Application\Request;
use Nette\Application\BadRequestException;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$original = new Request('Admin:Pages', 'POST', ['action' => 'detail', 'id' => '42', 'token' => 'secret'],
    ['_do' => 'pageList-delete', 'password' => 'secret', 'document' => '<script>secret</script>']);
$context = ErrorContext::describe(403, $original);
Assert::contains('"signal":"pageList-delete"', $context);
Assert::contains('"id":"42"', $context);
Assert::notContains('secret', $context);
Assert::notContains('script', $context);
Assert::notContains('injected', ErrorContext::describe(404,
    new Request("Invalid\ninjected", 'GET', ['action' => ['injected'], 'id' => 'injected'])));

$factory = new class implements Nette\Application\UI\TemplateFactory {
    public function createTemplate(?Nette\Application\UI\Control $control = null): Nette\Application\UI\Template {
        $latte = new Latte\Engine();
        $latte->onCompile[] = static function ($latte): void {
            Nette\Bridges\ApplicationLatte\UIMacros::install($latte->getCompiler());
        };
        $latte->addProvider('uiControl', $control);
        $latte->addProvider('uiPresenter', $control);
        $template = new Nette\Bridges\ApplicationLatte\Template($latte);
        $template->basePath = '/subdir';
        return $template;
    }
};

foreach ([400, 403, 404, 405, 410, 500] as $status) {
    foreach ([false, true] as $ajax) {
        $logger = new class implements Tracy\ILogger {
            public array $entries = [];
            public function log($message, $level = self::INFO) { $this->entries[] = [$message, $level]; }
        };
        $httpRequest = new Nette\Http\Request(new Nette\Http\UrlScript('http://localhost/subdir/missing'), [], [], [],
            $ajax ? ['x-requested-with' => 'XMLHttpRequest'] : [], $status === 405 ? 'OPTIONS' : 'GET');
        $httpResponse = new Nette\Http\Response();
        $presenter = new ErrorPresenter($logger);
        $presenter->injectPrimary(null, null, null, $httpRequest, $httpResponse, null, null, $factory);
        $exception = $status === 500 ? new RuntimeException('private failure detail') : new BadRequestException('private detail', $status);
        $response = $presenter->run(new Request('Front:Error', Request::FORWARD,
            ['exception' => $exception, 'request' => $original]));
        ob_start();
        $response->send($httpRequest, $httpResponse);
        $body = ob_get_clean();
        Assert::same($status, $httpResponse->getCode());
        // CLI does not retain headers_list(); no-store is checked by HTTP smoke.
        Assert::notContains('private', $body);
        if ($ajax) {
            Assert::same(['error' => true, 'code' => $status], json_decode($body, true));
        } else {
            Assert::contains('error ' . $status, $body);
            if ($status !== 500) { Assert::contains('/subdir/', $body); }
        }
        Assert::contains('"id":"42"', $logger->entries[0][0]);
        Assert::same($status === 500 ? 2 : 1, count($logger->entries));
    }
}
