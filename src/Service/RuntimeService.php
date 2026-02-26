<?php

declare(strict_types=1);

namespace Core\Cloud\Service;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;

class RuntimeService
{
    public static function composerScript(string $workingDirectory): string
    {
        $candidates = [
            $workingDirectory . '/vendor/composer/composer/bin/composer',
            $workingDirectory . '/vendor/bin/composer',
            $workingDirectory . '/composer.phar',
        ];

        foreach ($candidates as $file) {
            if (!is_file($file)) {
                continue;
            }
            if (str_ends_with(strtolower($file), '.bat')) {
                continue;
            }
            return $file;
        }

        throw new \Exception('Local composer script not found in vendor directory');
    }

    public static function phpBinary(): string
    {
        $finder = new PhpExecutableFinder();
        $finderBinary = trim((string)$finder->find(false));
        $envBinary = trim((string)getenv('PHP_CLI_BINARY'));
        $runtimeBinary = defined('PHP_BINARY') ? trim((string)PHP_BINARY) : '';
        $bindirBinary = defined('PHP_BINDIR')
            ? rtrim((string)PHP_BINDIR, '/\\') . DIRECTORY_SEPARATOR . (DIRECTORY_SEPARATOR === '\\' ? 'php.exe' : 'php')
            : '';
        $runtimeDirBinary = '';
        if ($runtimeBinary !== '' && str_contains($runtimeBinary, DIRECTORY_SEPARATOR)) {
            $runtimeDirBinary = dirname(dirname($runtimeBinary)) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . (DIRECTORY_SEPARATOR === '\\' ? 'php.exe' : 'php');
        }
        $pathBinary = (new ExecutableFinder())->find(DIRECTORY_SEPARATOR === '\\' ? 'php.exe' : 'php') ?: '';

        $candidates = [
            $finderBinary,
            $envBinary,
            $runtimeBinary,
            $bindirBinary,
            $runtimeDirBinary,
            $pathBinary,
        ];

        foreach ($candidates as $binary) {
            if ($binary === '') {
                continue;
            }
            if (str_contains($binary, DIRECTORY_SEPARATOR)) {
                if (!is_file($binary)) {
                    continue;
                }
                return $binary;
            }
            return $binary;
        }

        throw new \Exception('PHP CLI binary not found');
    }
}
