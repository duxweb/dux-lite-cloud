<?php

declare(strict_types=1);

namespace Core\Cloud\Service;

use Core\App;

class ConfigService
{
    private static ?array $config = null;

    public static function init(array $config = []): void
    {
        self::$config = array_merge(self::getDefaultConfig(), $config);
    }

    public static function getApiUrl(): string
    {
        self::ensureInitialized();
        return self::$config['api']['url'];
    }

    public static function getTimeout(): int
    {
        self::ensureInitialized();
        return self::$config['api']['timeout'] ?? 30;
    }

    public static function getCommandTimeout(): int
    {
        self::ensureInitialized();
        return self::$config['command']['timeout'] ?? 3600;
    }

    public static function getRetries(): int
    {
        self::ensureInitialized();
        return self::$config['api']['retries'] ?? 3;
    }

    public static function getKey(): string
    {
        return App::config('use')->get('cloud.key', '');
    }

    public static function getTempDir(): string
    {
        self::ensureInitialized();
        return self::$config['download']['temp_dir'] ?? data_path('package');
    }

    public static function getUploadTempDir(): string
    {
        self::ensureInitialized();
        return self::$config['upload']['temp_dir'] ?? data_path('upload');
    }

    public static function shouldVerifyChecksum(): bool
    {
        self::ensureInitialized();
        return self::$config['download']['verify_checksum'] ?? true;
    }

    public static function getDocumentationUrl(): string
    {
        self::ensureInitialized();
        return self::$config['documentation']['url'];
    }

    private static function ensureInitialized(): void
    {
        if (self::$config === null) {
            self::init();
        }
    }

    public static function getPackageType(): string
    {
        self::ensureInitialized();
        return self::$config['package']['type'] ?? 'duxLiteV2';
    }

    private static function getDefaultConfig(): array
    {
        $apiUrl = trim((string)App::config('use')->get('cloud.url', ''));
        if (!$apiUrl) {
            $apiUrl = 'https://cloud.dux.plus';
        }

        return [
            'api' => [
                'url' => $apiUrl,
                'timeout' => 30,
                'retries' => 3,
            ],
            'command' => [
                'timeout' => 3600,
            ],
            'download' => [
                'temp_dir' => data_path('package'),
                'verify_checksum' => true,
            ],
            'upload' => [
                'temp_dir' => data_path('cloud'),
            ],
            'documentation' => [
                'url' => 'https://www.dux.cn',
            ],
        ];
    }
}
