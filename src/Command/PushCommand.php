<?php
declare(strict_types=1);

namespace Core\Cloud\Command;

use Core\Cloud\Package\Package;
use Core\Cloud\Service\ConfigService;
use GuzzleHttp\TransferStats;
use Nette\Utils\FileSystem;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use ZipStream\ZipStream;

class PushCommand extends Command
{

    protected function configure(): void
    {
        $this
            ->setName('push')
            ->setDescription('Release the module version')
        ->addArgument(
            'app',
            InputArgument::REQUIRED,
            'please enter the app name'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);
        $app = $input->getArgument('app');
        $configPath = app_path(ucfirst($app) . '/app.json');
        if (!is_file($configPath)) {
            $io->error('Configuration file does not exist');
            return Command::FAILURE;
        }
        $config = Package::getJson($configPath);
        if (!$config) {
            $io->error('Configuration does not exist');
            return Command::FAILURE;
        }
        if (!$config['name'] || !$config['version']) {
            $io->error('Configuration field "name" or "version" does not exist');
            return Command::FAILURE;
        }

        $output->writeln('current version number: ' . $config['version']);
        $version = $io->ask('new version number: ');
        if (!$version) {
            $io->error('Version number not entered');
            return Command::FAILURE;
        }

        $config['version'] = $version;
        Package::saveJson($configPath, $config);

        $tmpDir = ConfigService::getUploadTempDir() . '/app/' . $app;
        $tmpZip = ConfigService::getUploadTempDir() . '/app/' . $app . '.zip';
        if (is_dir($tmpDir)) {
            FileSystem::delete($tmpDir);
        }
        if (is_file($tmpZip)) {
            FileSystem::delete($tmpZip);
        }

        $appDir = $tmpDir . '/php';
        $jsDir = $tmpDir . '/js';
        $configDir = $tmpDir . '/config.toml';
        mkdir($appDir, 0777, true);
        mkdir($jsDir, 0777, true);

        $appSourceDir = app_path(ucfirst($app));
        $jsSourceDir = base_path('web/app/' . lcfirst($app));
        $configSourceFile = config_path(lcfirst($app) . '.toml');

        FileSystem::copy($appSourceDir, $appDir);
        if (is_dir($jsSourceDir)) {
            FileSystem::copy($jsSourceDir, $jsDir);
        }
        if (is_file($configSourceFile)) {
            FileSystem::copy($configSourceFile, $configDir);
        }

        $zipFile = ConfigService::getUploadTempDir() . '/app/' . $app . '.zip';
        $fileStream = fopen($zipFile, 'w+b');

        $zip = new ZipStream(
            outputStream: $fileStream,
            defaultEnableZeroHeader: false
        );
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tmpDir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($tmpDir) + 1);
                $zip->addFileFromPath($relativePath, $filePath);
            }
        }
        $zip->finish();
        fclose($fileStream);

        $fileSize = filesize($zipFile);
        $progressBar = new ProgressBar($output, $fileSize);
        $progressBar->setFormat('Upload: %current%/%max% [%bar%] %percent:3s%%');
        $progressBar->start();

        try {
            Package::request('post', '/v/package/version/push', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => ConfigService::getKey()
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($zipFile, 'r')
                    ],
                    [
                        'name' => 'type',
                        'contents' => ConfigService::getPackageType()
                    ],
                    [
                        'name' => 'md5',
                        'contents' => md5_file($zipFile)
                    ],
                    [
                        'name' => 'name',
                        'contents' => $config['name']
                    ],
                    [
                        'name' => 'app',
                        'contents' => ucfirst($app)
                    ],
                ],
                'on_stats' => function (TransferStats $stats) use ($progressBar) {
                    $uploadedBytes = $stats->getHandlerStats()['uploaded_bytes'] ?? 0;
                    $progressBar->setProgress($uploadedBytes);
                }
            ]);
        } finally {
            $progressBar->finish();
            if (is_dir($tmpDir)) {
                FileSystem::delete($tmpDir);
            }
            if (is_file($tmpZip)) {
                FileSystem::delete($tmpZip);
            }
        }

        $io->newLine();
        $io->success('Publish Application Success');
        return Command::SUCCESS;
    }
}