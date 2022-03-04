<?php

declare(strict_types=1);

namespace Schranz\TestGenerator\Domain\Model;

class Config
{
    public string $srcDirectory = 'src';
    public string $unitTestDirectory = 'tests/Unit';

    /**
     * @var array<string, string>
     */
    public array $namespaceReplaces = ['App' => 'App\Tests\Unit'];

    public string $classPostfix = 'Test';

    /**
     * @var class-string
     */
    public string $testExtendedClass = 'PHPUnit\Framework\TestCase';

    /**
     * @var string[]
     */
    public array $hooks = [
        // 'vendor/bin/rector process %s'
        // 'vendor/bin/php-cs-fixer fix %s'
    ];

    public function getTestExtendedClassName(): string
    {
        return \strrev(\explode('\\', \strrev($this->testExtendedClass))[0]);
    }

    public function getUnitTestFile(string $file): string
    {
        return \str_replace([$this->srcDirectory, '.php'], [$this->unitTestDirectory, 'Test.php'], $file);
    }
}
