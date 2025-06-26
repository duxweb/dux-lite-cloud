<?php

namespace Core\Cloud\Package;

use Symfony\Component\Console\Output\OutputInterface;

class Install
{
    public static function main(OutputInterface $output, string $app): void
    {
        $info = Package::app($app);
        $packages = $info['packages'];
        Add::main($output, $packages);
        Package::updateAppVersion($info);
    }

}