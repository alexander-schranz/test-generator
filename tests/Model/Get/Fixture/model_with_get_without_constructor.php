<?php

namespace App;

class ModelWithGetWithoutConstructor
{
    private int $id;

    public function __construct()
    {}

    public function getId(): int
    {
        return $this->id;
    }
}

?>
---
<?php

namespace App\Tests\Unit;

use App\ModelWithGetWithoutConstructor;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\ModelWithGetWithoutConstructor
 */
class ModelWithGetWithoutConstructorTest extends TestCase
{
    public function testGetId(): void
    {
        $model = $this->createInstance();
        $this->assertSame('TODO', $model->getId());
        $this->markAsIncomplete('This this was generated with "schranz/test-generator" and should be adjusted.');
    }

    public function createInstance(): ModelWithGetWithoutConstructor
    {
        return new ModelWithGetWithoutConstructor();
    }
}
