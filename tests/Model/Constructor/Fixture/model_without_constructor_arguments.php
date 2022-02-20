<?php

declare(strict_types=1);

namespace App;

class ModelWithoutConstructorArguments
{

}

?>
---
<?php

namespace App\Tests\Unit;

use App\ModelWithoutConstructorArguments;
use PHPUnit\Framework\TestCase;

class ModelWithoutConstructorArgumentsTest extends TestCase
{
    public function createInstance(): ModelWithoutConstructorArguments
    {
        return new ModelWithoutConstructorArguments();
    }
}
