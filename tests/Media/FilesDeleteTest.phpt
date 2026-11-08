<?php
declare(strict_types=1);

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Utils\FileSystem;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../database.php';

// FilesPresenter::handleDelete with the real AssetStorage on a temporary
// www root. On PHP 7.4 in the C locale basename('Žluť.png') is 'luť.png' and
// basename('Čáp.jpg') is 'p.jpg', so the check must not rely on basename().
$root = sys_get_temp_dir() . '/files-delete-' . bin2hex(random_bytes(8));
define('APP_DIR', $root);
register_shutdown_function(static function () use ($root): void { FileSystem::delete($root); });
$mailer = new class implements Nette\Mail\Mailer {
    public function send(Nette\Mail\Message $mail): void {}
};
$presenter = new class(createTestDatabase(), $mailer) extends App\AdminModule\Presenters\FilesPresenter {
    public function setPath(string $path): void { $this->params = ['path' => $path]; }
    protected function requireCsrfToken(): void {}
    public function redirect(string $destination, $args = []): void { throw new AbortException; }
};
$delete = static function (string $path) use ($presenter): void {
    $presenter->setPath($path);
    try { $presenter->handleDelete(); } catch (AbortException $redirected) {}
};
foreach (['Žluť.png', 'Čáp.jpg', 'p.jpg', 'luť.png', 'menu/1.png', '.htaccess'] as $name) {
    FileSystem::write($root . '/images/' . $name, $name);
}
FileSystem::write($root . '/outside.png', 'outside');

$delete('Žluť.png');
Assert::false(is_file($root . '/images/Žluť.png'));
Assert::true(is_file($root . '/images/luť.png'));
$delete('Čáp.jpg');
Assert::false(is_file($root . '/images/Čáp.jpg'));
Assert::same('p.jpg', file_get_contents($root . '/images/p.jpg'));

foreach (['menu/1.png', 'menu\\1.png', '.htaccess', '../outside.png', '..\\outside.png', '..', ''] as $rejected) {
    $error = Assert::exception(static function () use ($delete, $rejected): void { $delete($rejected); }, BadRequestException::class);
    Assert::same(400, $error->getHttpCode(), $rejected);
}
Assert::same('menu/1.png', file_get_contents($root . '/images/menu/1.png'));
Assert::same('.htaccess', file_get_contents($root . '/images/.htaccess'));
Assert::same('outside', file_get_contents($root . '/outside.png'));
