<?php

namespace Core\Cloud\Package;

use Symfony\Component\Console\Output\OutputInterface;

class Uninstall
{
    public static function main(OutputInterface $output, string $app): void
    {
        $info = Package::app($app);
        $packages = $info['packages'];
        Del::main($output, $packages);

        $configFile = base_path('app.json');
        $appJson = [];
        if (is_file($configFile)) {
            $appJson = Package::getJson($configFile);
        }
        $apps = $appJson['apps'] ?: [];
        unset($apps[$app]);
        $appJson['apps'] = $apps;
        Package::saveJson($configFile, $appJson);
    }

}