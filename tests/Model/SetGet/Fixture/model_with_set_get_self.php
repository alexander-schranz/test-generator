<?php

namespace App;

class ModelWithSetGetSelf
{
    private string $title = '';

    public function setTitle(string $title): self
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

use App\ModelWithSetGetSelf;
use PHPUnit\Framework\TestCase;

class ModelWithSetGetSelfTest extends TestCase
{
    public function testSetGetTitle(): void
    {
        $model = $this->createInstance();
        $this->assertNull($model->getTitle());
        $this->assertSame($model, $model->setTitle('Title'));
        $this->assertSame('Title', $model->getTitle());
        $this->markAsRisky();
    }

    public function createInstance(): ModelWithSetGetSelf
    {
        return new ModelWithSetGetSelf();
    }
}
