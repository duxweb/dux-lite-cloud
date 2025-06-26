<?php
declare(strict_types=1);

namespace Core\Cloud\Command;

use Core\Cloud\Package\Package;
use Core\Cloud\Package\Uninstall;
use Core\Cloud\Service\ConfigService;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UninstallCommand extends Command
{

    protected function configure(): void
    {
        $this
            ->setName('app:uninstall')
            ->setDescription('Uninstall application')
        ->addArgument(
            'name',
            InputArgument::REQUIRED,
            'please enter the app name'
        )
            ->addOption('build', null, InputOption::VALUE_REQUIRED, 'whether to compile ui');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');
        $build = $input->getOption('build');

        try {
            Uninstall::main($output, $name);
        } finally {
            FileSystem::delete(ConfigService::getTempDir());
        }

        $application = $this->getApplication();
        Package::installOther($application, $output, !!$build);

        return Command::SUCCESS;
    }

}