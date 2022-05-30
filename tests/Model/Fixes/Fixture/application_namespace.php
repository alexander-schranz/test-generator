<?php

namespace App\Context\Application;

class ApplicationNamespace
{
    public function __construct(private string $title)
    {}
}

?>
---
<?php

namespace App\Tests\Unit\Context\Application;

use App\Context\Application\ApplicationNamespace;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Context\Application\ApplicationNamespace
 */
class ApplicationNamespaceTest extends TestCase
{
    public function createInstance($data = []): ApplicationNamespace
    {
        return new ApplicationNamespace($data['title'] ?? 'Title');
    }
}
