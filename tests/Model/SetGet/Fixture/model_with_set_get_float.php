<?php

namespace App;

class ModelWithSetGetFloat
{
    private float $number = 0.0;

    public function setNumber(float $number): void
    {
        $this->number = $number;
    }

    public function getNumber(): float
    {
        return $this->number;
    }
}

?>
---
<?php

namespace App\Tests\Unit;

use App\ModelWithSetGetFloat;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\ModelWithSetGetFloat
 */
class ModelWithSetGetFloatTest extends TestCase
{
    public function testSetGetNumber(): void
    {
        $model = $this->createInstance();
        $this->assertNull($model->getNumber());
        $model->setNumber(1.01);
        $this->assertSame(1.01, $model->getNumber());
        $this->markAsRisky();
    }

    public function createInstance(): ModelWithSetGetFloat
    {
        return new ModelWithSetGetFloat();
    }
}
