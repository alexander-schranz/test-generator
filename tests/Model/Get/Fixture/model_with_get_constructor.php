<?php

namespace App;

class ModelWithGetConstructor
{
    private string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public function getText(): string
    {
        return $this->text;
    }
}

?>
---
<?php

namespace App\Tests\Unit;

use App\ModelWithGetConstructor;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\ModelWithGetConstructor
 */
class ModelWithGetConstructorTest extends TestCase
{
    public function testGetText(): void
    {
        $model = $this->createInstance(['text' => 'Text']);
        $this->assertSame('Text', $model->getText());
        $this->markAsIncomplete('This was generated with "schranz/test-generator" and should be adjusted.');
    }

    public function createInstance($data = []): ModelWithGetConstructor
    {
        return new ModelWithGetConstructor($data['text'] ?? 'Text');
    }
}
