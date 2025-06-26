<?php
declare(strict_types=1);

namespace Core\Cloud\Command;

use Core\Cloud\Service\ConfigService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ComposerCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('package:composer')
            ->setDescription('Execute execute commands via PHP.')
            ->addArgument('cmd', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'The execute command to run.')
            ->setHelp('This command allows you to run yarn commands...');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerCommand = $input->getArgument('cmd');

        $executableFinder = new \Symfony\Component\Process\ExecutableFinder();
        $composerPath = $executableFinder->find('composer');

        if (!$composerPath) {
            throw new \Exception('Path to composer not found');
        }


        $composerPathFinder = Process::fromShellCommandline('/usr/bin/which composer');
        $composerPathFinder->run();

        if (!$composerPathFinder->isSuccessful()) {
            throw new ProcessFailedException($composerPathFinder);
        }
        $composerPath = trim($composerPathFinder->getOutput());


        $command = array_merge([$composerPath], is_array($composerCommand) ? $composerCommand : [$composerCommand]);
        $workingDirectory = base_path();
        $process = new Process($command, $workingDirectory);
        $process->setTimeout(ConfigService::getCommandTimeout());

        $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return Command::SUCCESS;
    }

}