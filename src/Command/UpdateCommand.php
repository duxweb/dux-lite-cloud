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

class UpdateCommand extends Command
{

    protected function configure(): void
    {
        $this
            ->setName('update')
            ->setDescription('updated package')
        ->addArgument(
            'name',
            InputArgument::OPTIONAL,
            'please enter the package name'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        try {
            if ($name) {
                [$name, $ver] = explode(':', $name);
                Add::main($output, [$name => $ver ?: 'latest'], true);
            } else {
                Add::main($output, [], true);
            }
        } finally {
            FileSystem::delete(ConfigService::getTempDir());
        }

        $application = $this->getApplication();
        Package::installOther($application, $output);

        return Command::SUCCESS;
    }

}