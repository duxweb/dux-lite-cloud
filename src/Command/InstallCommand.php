<?php
declare(strict_types=1);

namespace Core\Cloud\Command;

use Core\Cloud\Package\Add;
use Core\Cloud\Package\Package;
use Core\Cloud\Service\ConfigService;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{

    protected function configure(): void
    {
        $this
            ->setName('add')
            ->setDescription('Add cloud package')
        ->addArgument(
            'name',
            InputArgument::REQUIRED,
            'please enter the package name'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        try {
            [$name, $ver] = explode(':', $name);
            Add::main($output, [$name => $ver ?: 'latest']);
        } finally {
            FileSystem::delete(ConfigService::getTempDir());
        }

        $application = $this->getApplication();
        Package::installOther($application, $output);

        return Command::SUCCESS;
    }

}