<?php
declare(strict_types=1);

namespace Core\Cloud\Command;

use Core\Cloud\Package\Composer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComposerCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('composer')
            ->setDescription('Execute composer commands via local vendor installation.')
            ->addArgument('cmd', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'The composer command to run.')
            ->setHelp('This command allows you to run composer commands using the local vendor installation...');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerCommand = $input->getArgument('cmd');

        Composer::run($composerCommand, $output);

        return Command::SUCCESS;
    }
}