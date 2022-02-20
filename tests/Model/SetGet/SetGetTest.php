<?php

declare(strict_types=1);

namespace Schranz\TestGenerator\Tests\Model\Constructor;

use Schranz\TestGenerator\Tests\AbstractTestCase;

class SetGetTest extends AbstractTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function testFixtures(\SplFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return \Generator<\SplFileInfo>
     */
    public function provideData(): \Generator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }
}
