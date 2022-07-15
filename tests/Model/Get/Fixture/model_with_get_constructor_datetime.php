<?php

namespace App;

use DateTime;

class ModelWithGetConstructor
{
    private DateTime $dateTime;

    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }
}

?>
---
<?php

namespace App\Tests\Unit;

use App\ModelWithGetConstructor;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\ModelWithGetConstructor
 */
class ModelWithGetConstructorTest extends TestCase
{
    public function testGetDateTime(): void
    {
        $dateTime = new DateTime('2022-01-01');
        $model = $this->createInstance(['dateTime' => $dateTime]);
        $this->assertSame($dateTime, $model->getDateTime());
        $this->markTestIncomplete('This was generated with "schranz/test-generator" and should be adjusted.');
    }

    public function createInstance($data = []): ModelWithGetConstructor
    {
        return new ModelWithGetConstructor($data['dateTime'] ?? new DateTime('2022-01-01'));
    }
}
