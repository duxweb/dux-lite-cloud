<?php
declare(strict_types=1);

namespace Core\Cloud\Command;

use Core\Cloud\Package\Install;
use Core\Cloud\Package\Package;
use Core\Cloud\Service\ConfigService;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{

    protected static $defaultName = 'install';
    protected static $defaultDescription = 'Installation application';

    protected function configure(): void
    {
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'please enter the app name'
        )
            ->addOption('build', null, InputOption::VALUE_REQUIRED, 'whether to compile ui');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $build = $input->getOption('build');

        try {
            Install::main($output, $name);
        } finally {
            FileSystem::delete(ConfigService::getTempDir());
        }

        $application = $this->getApplication();
        Package::installOther($application, $output, !!$build);

        return Command::SUCCESS;
    }

}