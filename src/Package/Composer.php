<?php

namespace Core\Cloud\Package;

use Core\Cloud\Service\ConfigService;
use Core\Cloud\Service\RuntimeService;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Composer
{
    public static function run(array|string $composerCommand, OutputInterface $output): void
    {
        $workingDirectory = base_path();
        $localComposerScript = RuntimeService::composerScript($workingDirectory);
        $phpBinary = RuntimeService::phpBinary();
        $command = array_merge([$phpBinary, $localComposerScript], is_array($composerCommand) ? $composerCommand : [$composerCommand]);

        $process = new Process($command, $workingDirectory);
        $process->setTimeout(ConfigService::getCommandTimeout());

        $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
