<?php

declare(strict_types=1);

namespace Schranz\TestGenerator\Tests;

use PHPUnit\Framework\TestCase;
use Schranz\TestGenerator\Application\Generator\ArgumentGenerator;
use Schranz\TestGenerator\Application\Generator\TestFileGenerator;
use Schranz\TestGenerator\Application\Grouper\MethodGrouper;
use Schranz\TestGenerator\Application\Reader\Reader;
use Schranz\TestGenerator\Application\Writer\Writer;
use Schranz\TestGenerator\Application\Writer\WriteVisitor;
use Schranz\TestGenerator\Domain\Model\Config;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class AbstractTestCase extends TestCase
{
    /**
     * @return \Generator<\SplFileInfo>
     */
    protected function yieldFilesFromDirectory(string $directory): \Generator
    {
        $finder = new Finder();
        $finder->in($directory)
            ->ignoreVCS(true)
            ->files()
            ->name('*.php');

        foreach ($finder as $file) {
            if ($file instanceof \SplFileInfo) {
                yield \str_replace(\getcwd() . '/', '', $file->getPathname()) => [$file];
            }
        }
    }

    protected function doTestFileInfo(\SplFileInfo $fileInfo, ?Config $config = null): void
    {
        $content = \file_get_contents($fileInfo->getPathname());

        [$input, $output] = \explode("?>\n---\n", $content);

        // read input
        $reader = new Reader();
        $readVisitor = $reader->read($input);

        // generate test file
        $testFileGenerator = new TestFileGenerator();
        $unitTestFileContent = $testFileGenerator->generateTestFile($readVisitor->getClass(), $config ?? new Config());

        // Write File
        $writeVisitor = new WriteVisitor(
            $readVisitor,
            new ArgumentGenerator(),
            new MethodGrouper()
        );

        $writer = new Writer();
        $result = $writer->write($unitTestFileContent, $writeVisitor);

        if (!\file_exists(\sys_get_temp_dir() . '/test-generator')) {
            \mkdir(\sys_get_temp_dir() . '/test-generator');
        }

        $testFile = \tempnam(\sys_get_temp_dir() . '/test-generator', 'TestGenerator_');
        \rename($testFile, $testFile . '.php');
        $testFile .= '.php';

        try {
            \file_put_contents($testFile, $result);

            $process = new Process(['vendor/bin/rector', 'process', $testFile, '--config', __DIR__ . '/rector.php'], \dirname(__DIR__));
            if ($process->run()) {
                throw new \RuntimeException('Rector did fail to fix the file.');
            }

            $process = new Process(['vendor/bin/php-cs-fixer', 'fix', $testFile, '--config', __DIR__ . '/.php-cs-fixer.dist.php'], \dirname(__DIR__));
            if ($process->run()) {
                throw new \RuntimeException('PHP CS Fixer did fail to fix the file.');
            }

            $result = \file_get_contents($testFile);
        } finally {
            @\unlink($testFile);
        }

        $this->assertSame(\trim($output), \trim($result));
    }
}
