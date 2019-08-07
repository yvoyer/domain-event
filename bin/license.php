<?php declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$license = '<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */';

$baseDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
$dirs = [
    $baseDir . 'src',
];
$factory = new SebastianBergmann\FileIterator\Facade();
$phpFiles = $factory->getFilesAsArray($dirs, '.php');
foreach ($phpFiles as $filePath) {
    $contents = \file_get_contents($filePath);
    if (! $contents) {
        throw new \RuntimeException(\sprintf('Unable to open file "%s".', $filePath));
    }

    if (false === \strpos($contents, 'declare(strict_types=1);')) {
        $contents = \str_replace('<?php', '<?php declare(strict_types=1);', $contents);
    }

    if (false === \strpos($contents, '(c) Yannick Voyer')) {
        $contents = \str_replace('<?php declare(strict_types=1);', $license, $contents);
    }

    \file_put_contents($filePath, $contents);
}
