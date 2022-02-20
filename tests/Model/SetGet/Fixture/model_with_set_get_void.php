<?php

namespace App;

class ModelWithSetGetVoid
{
    private string $title = '';

    public function setTitle(string $title): void
    {
        $this->title = $title;
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

use App\ModelWithSetGetVoid;
use PHPUnit\Framework\TestCase;

class ModelWithSetGetVoidTest extends TestCase
{
    public function testSetGetTitle(): void
    {
        $model = $this->createInstance();
        $this->assertNull($model->getTitle());
        $model->setTitle('Title');
        $this->assertSame('Title', $model->getTitle());
        $this->markAsRisky();
    }

    public function createInstance(): ModelWithSetGetVoid
    {
        return new ModelWithSetGetVoid();
    }
}
