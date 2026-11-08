<?php
declare(strict_types=1);

use App\Model\IO;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

foreach (['shell.php', 'shell.PHp', 'image.jpg.php', 'shell.phtml', 'shell.phar',
          '.htaccess', '.htpasswd', 'page.html', 'script.js', 'image.svg',
          'script.sh', 'program.exe', '', '.', '..'] as $unsafe) {
    Assert::null(IO::sanitizeUploadFileName($unsafe));
    Assert::false(IO::isAllowedUploadFileName($unsafe));
}

foreach ([
    '../photo.JPG' => 'photo.jpg',
    'C:\\fakepath\\report.PDF' => 'report.pdf',
    'report final (2).pdf' => 'report-final-2.pdf',
    "photo\x00.jpg" => 'photo.jpg',
    'document.pdf' => 'document.pdf',
] as $input => $expected) {
    Assert::same($expected, IO::sanitizeUploadFileName($input));
}

// A local file must not become an accepted HTTP upload merely by naming it .png.
Assert::false(IO::isAllowedImageUpload('image.png', __FILE__));
