<?php

namespace App;

class ModelWithSetGetStatic
{
    private string $title = '';

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}

?>
---
<?php

namespace App\Tests\Unit;

use App\ModelWithSetGetStatic;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\ModelWithSetGetStatic
 */
class ModelWithSetGetStaticTest extends TestCase
{
    public function testSetGetTitle(): void
    {
        $model = $this->createInstance();
        $this->assertNull($model->getTitle());
        $this->assertSame($model, $model->setTitle('Title'));
        $this->assertSame('Title', $model->getTitle());
        $this->markAsIncomplete('This was generated with "schranz/test-generator" and should be adjusted.');
    }

    public function createInstance(): ModelWithSetGetStatic
    {
        return new ModelWithSetGetStatic();
    }
}
