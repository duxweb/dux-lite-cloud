<?php

namespace Core\Cloud\Package;

use Core\Cloud\Service\ConfigService;
use Exception;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Composer
{
    public static function run(array $composerCommand, OutputInterface $output): void
    {
        $workingDirectory = base_path();
        $localComposerPath = $workingDirectory . '/vendor/bin/composer';

        if (!file_exists($localComposerPath)) {
            throw new \Exception('Local composer not found in vendor/bin/composer. Please ensure composer dependencies are installed.');
        }

        if (!is_executable($localComposerPath)) {
            throw new \Exception('Local composer file is not executable: ' . $localComposerPath);
        }

        // 构建完整的命令
        $command = array_merge([$localComposerPath], is_array($composerCommand) ? $composerCommand : [$composerCommand]);

        // 创建进程并执行
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