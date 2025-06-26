<?php
declare(strict_types=1);

namespace Core\Cloud\Command;

use Core\Cloud\Package\Del;
use Core\Cloud\Package\Package;
use Core\Cloud\Service\ConfigService;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class PackageUninstallCommand extends Command
{

    protected static $defaultName = 'package:uninstall';
    protected static $defaultDescription = 'Uninstall package';

    protected function configure(): void
    {
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'please enter the package name'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        try {
            Del::main($output, [$name => 'latest']);
        } finally {
            FileSystem::delete(ConfigService::getTempDir());
        }

        $application = $this->getApplication();
        Package::installOther($application, $output);

        return Command::SUCCESS;
    }

}