<?php

declare(strict_types=1);

namespace Core\Cloud;

use Core\Bootstrap;
use Core\Cloud\Command\ComposerCommand;
use Core\Cloud\Command\InstallCommand;
use Core\Cloud\Command\UninstallCommand;
use Core\Cloud\Command\UpdateCommand;
use Core\Cloud\Command\PushCommand;
use Core\Cloud\Command\AppUninstallCommand;
use Core\Cloud\Command\AppInstallCommand;
use Core\Cloud\Command\AppUpdateCommand;
use Core\Plugin\PluginProvider;

class CloudServiceProvider implements PluginProvider
{
    public static function getCommands(): array
    {
        return [
            ComposerCommand::class,
            UpdateCommand::class,
            PushCommand::class,

            InstallCommand::class,
            UninstallCommand::class,

            AppInstallCommand::class,
            AppUpdateCommand::class,
            AppUninstallCommand::class,
        ];
    }

    public static function register(Bootstrap $bootstrap): void
    {
    }

    public static function boot(Bootstrap $bootstrap): void
    {
        if (isset($bootstrap->command)) {
            foreach (self::getCommands() as $command) {
                $bootstrap->command->add(new $command());
            }
        }
    }

    public static function apps(): array
    {
        return [];
    }

}