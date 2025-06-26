<?php

namespace Core\Cloud\Package;

use Core\Handlers\Exception;
use Symfony\Component\Console\Output\OutputInterface;

class Update
{
    public static function main(OutputInterface $output, ?string $app): void
    {
        $configFile = base_path('app.json');
        if (!is_file($configFile)) {
            throw new Exception('The app.json file does not exist');
        }
        $appJson = Package::getJson($configFile);
        $apps = array_keys($appJson['apps']);
        $info = Package::app($app ?: implode(',', $apps));
        $packages = $info['packages'];
        if (!$packages) {
            $output->writeln('<info>No updated applications</info>');
            return;
        }
        Add::main($output, $packages, true);
        Package::updateAppVersion($info);
    }

}