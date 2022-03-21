<?php

namespace App;

class ModelWithConstructorArguments
{
    public function __construct(private string $title)
    {}
}

?>
---
<?php

namespace App\Tests\Unit;

use App\ModelWithConstructorArguments;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\ModelWithConstructorArguments
 */
class ModelWithConstructorArgumentsTest extends TestCase
{
    public function createInstance($data = []): ModelWithConstructorArguments
    {
        return new ModelWithConstructorArguments($data['title'] ?? 'Title');
    }
}
