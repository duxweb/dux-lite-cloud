<?php

declare(strict_types=1);

namespace Core\Cloud;

use Core\Bootstrap;
use Core\Cloud\Command\ComposerCommand;
use Core\Cloud\Command\InstallCommand;
use Core\Cloud\Command\PackageInstallCommand;
use Core\Cloud\Command\PackageUninstallCommand;
use Core\Cloud\Command\PackageUpdateCommand;
use Core\Cloud\Command\PushCommand;
use Core\Cloud\Command\UninstallCommand;
use Core\Cloud\Command\UpdateCommand;
use Core\Plugin\PluginProvider;

class CloudServiceProvider implements PluginProvider
{
    public static function getCommands(): array
    {
        return [
            ComposerCommand::class,
            InstallCommand::class,
            PackageInstallCommand::class,
            PackageUninstallCommand::class,
            PackageUpdateCommand::class,
            PushCommand::class,
            UninstallCommand::class,
            UpdateCommand::class,
        ];
    }

    public static function register(Bootstrap $bootstrap): void
    {
        $bootstrap->command->addCommands(self::getCommands());
    }

    public static function boot(Bootstrap $bootstrap): void
    {
    }

    public static function apps(): array
    {
        return [];
    }

}