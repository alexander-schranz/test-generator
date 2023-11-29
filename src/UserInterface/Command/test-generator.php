<?php

declare(strict_types=1);

/**
 * This file is part of the test generator project.
 *
 * (c) Alexander Schranz (https://github.com/alexander-schranz/test-generator)
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Schranz\TestGenerator\UserInterface\Command;

$autoloadFiles = [
    \dirname(__DIR__, 5) . '/autoload.php',
    \dirname(__DIR__, 3) . '/vendor/autoload.php',
];
foreach ($autoloadFiles as $autoloadFile) {
    if (\file_exists($autoloadFile)) {
        include_once $autoloadFile;
    }
}

use Schranz\TestGenerator\Application\Generator\ArgumentGenerator;
use Schranz\TestGenerator\Application\Generator\TestFileGenerator;
use Schranz\TestGenerator\Application\Grouper\MethodGrouper;
use Schranz\TestGenerator\Application\Reader\Reader;
use Schranz\TestGenerator\Application\Writer\Writer;
use Schranz\TestGenerator\Application\Writer\WriteVisitor;
use Schranz\TestGenerator\Domain\Model\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

(new SingleCommandApplication())
    ->setVersion('1.0.0')
    ->setName('Test Generator')
    ->addArgument('file')
    ->addOption('config', null, InputOption::VALUE_REQUIRED, 'Path to config file.', \getcwd() . '/tests/generator-config.php')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $ui = new SymfonyStyle($input, $output);
        $filesystem = new Filesystem();

        $file = $input->getArgument('file');
        $configFile = $input->getOption('config');

        $ui->title('Generate test');

        if (!$file || !\file_exists($file)) {
            $ui->error(\sprintf('Given file "%s" could not be found.', $file));

            return 1;
        }

        if (!$configFile || !\file_exists($configFile)) {
            $ui->error(\sprintf('The config file "%s" could not be found.', $configFile));

            return 2;
        }

        $config = require_once $configFile;

        if (!$config instanceof Config) {
            $ui->error(\sprintf('The given config file "%s" is invalid.', $configFile));

            return 3;
        }

        // Read File
        $reader = new Reader();
        $readVisitor = $reader->read(\file_get_contents($file));

        // Get or create test file
        $unitTestFile = $config->getUnitTestFile($file);

        $filesystem->remove($unitTestFile); // TODO remove

        if (!$filesystem->exists($unitTestFile)) {
            $testFileGenerator = new TestFileGenerator();
            $unitTestFileContent = $testFileGenerator->generateTestFile($readVisitor->getClass(), $config);
        } else {
            /** @var string $unitTestFileContent */
            $unitTestFileContent = \file_get_contents($unitTestFile);
        }

        // Write File
        $writeVisitor = new WriteVisitor(
            $readVisitor,
            new ArgumentGenerator(),
            new MethodGrouper()
        );

        $writer = new Writer();
        $result = $writer->write($unitTestFileContent, $writeVisitor);

        $filesystem->dumpFile($unitTestFile, $result);

        foreach ($config->hooks as $hook) {
            $process = Process::fromShellCommandline(\sprintf($hook, $unitTestFile));
            if (0 !== $process->run()) {
                $ui->error(\sprintf('Hook "%s" did fail.', $hook));

                return 4;
            }
        }

        $ui->success('Generated Test Cases: ' . $unitTestFile);

        return 0;
    })->run();
