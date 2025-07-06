<?php
declare(strict_types=1);

namespace Core\Cloud\Command;

use Core\Cloud\Package\Package;
use Core\Cloud\Package\Update;
use Core\Cloud\Service\ConfigService;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppUpdateCommand extends Command
{

    protected function configure(): void
    {
        $this
            ->setName('app:update')
            ->setDescription('Update the application')
        ->addArgument(
            'name',
            InputArgument::OPTIONAL,
            'please enter the app name'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $app = $input->getArgument('name');

        try {
            Update::main($output, $app);
        } finally {
            FileSystem::delete(ConfigService::getTempDir());
        }

        $application = $this->getApplication();
        Package::installOther($application, $output);

        return Command::SUCCESS;
    }

}