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

        // 获取更新日志
        $io->writeln('Please enter the changelog for this version (press Enter twice to finish, or leave empty for default):');
        $changelogLines = [];
        $emptyLineCount = 0;

        while (true) {
            $line = $io->ask('> ');

            if (empty($line)) {
                $emptyLineCount++;
                if ($emptyLineCount >= 2 || (empty($changelogLines) && $emptyLineCount >= 1)) {
                    break;
                }
                $changelogLines[] = '';
            } else {
                $emptyLineCount = 0;
                $changelogLines[] = $line;
            }
        }

        // 处理输入的更新日志
        $changelog = trim(implode("\n", $changelogLines));

        // 如果没有输入更新日志，使用默认值
        if (empty($changelog)) {
            $changelog = '- Update';
        }

        // 生成或更新 CHANGELOG.md
        $this->updateChangelog($app, $version, $changelog);
        $io->success('Changelog updated successfully');

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

    /**
     * 更新或创建 CHANGELOG.md 文件
     */
    private function updateChangelog(string $app, string $version, string $changelog): void
    {
        $appDir = app_path(ucfirst($app));
        $changelogPath = $appDir . '/CHANGELOG.md';
        $date = date('Y-m-d');

        // 新的更新日志条目
        $newEntry = "## [{$version}] - {$date}\n\n{$changelog}\n\n";

        if (file_exists($changelogPath)) {
            // 如果文件存在，读取现有内容
            $existingContent = file_get_contents($changelogPath);

            // 检查是否已有标题
            if (strpos($existingContent, '# Changelog') === false) {
                // 如果没有标题，添加标题
                $content = "# Changelog\n\nAll notable changes to this project will be documented in this file.\n\n" . $newEntry . $existingContent;
            } else {
                // 在第一个 ## 之前插入新条目
                $headerEnd = strpos($existingContent, "\n## ");
                if ($headerEnd !== false) {
                    $content = substr($existingContent, 0, $headerEnd + 1) . "\n" . $newEntry . substr($existingContent, $headerEnd + 1);
                } else {
                    // 如果没有找到其他版本，直接追加
                    $content = $existingContent . "\n" . $newEntry;
                }
            }
        } else {
            // 如果文件不存在，创建新文件
            $content = "# Changelog\n\nAll notable changes to this project will be documented in this file.\n\n" . $newEntry;
        }

        // 写入文件
        file_put_contents($changelogPath, $content);
    }
}