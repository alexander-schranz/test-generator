<?php

declare(strict_types=1);

namespace Schranz\TestGenerator\Tests\Model\Constructor;

use Schranz\TestGenerator\Tests\AbstractTestCase;

class AddRemoveTest extends AbstractTestCase
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
    public static function provideData(): \Generator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }
}
