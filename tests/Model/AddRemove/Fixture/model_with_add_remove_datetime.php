<?php

namespace App;

class ModelWithAddRemoveDateTime
{
    /**
     * @var iterable<\DateTime>
     */
    private iterable $datetimes = [];

    public function getDateTimes(): iterable
    {
        return $this->datetimes;
    }

    public function addDateTime(\DateTime $datetime): void
    {
        $this->datetimes[] = $datetime;
    }

    public function removeDateTime(\DateTime $datetime): void
    {
        foreach ($this->datetimes as $key => $currentDatetime) {
            if ($datetime === $currentDatetime) {
                unset($this->datetimes[$key]);
            }
        }
    }
}

?>
---
<?php

namespace App\Tests\Unit;

use App\ModelWithAddRemoveDateTime;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\ModelWithAddRemoveDateTime
 */
class ModelWithAddRemoveDateTimeTest extends TestCase
{
    public function testGetDateTimes(): void
    {
        $model = $this->createInstance();
        $this->assertSame('TODO', $model->getDateTimes());
        $this->markTestIncomplete('This was generated with "schranz/test-generator" and should be adjusted.');
    }

    public function testAddRemoveDateTime(): void
    {
        $model = $this->createInstance();
        $datetime = new DateTime('2022-01-01');
        $model->addDateTime($datetime);
        $this->assertContains($datetime, $model->getDateTimes());
        $model->removeDateTime($datetime);
        $this->assertNotContains($datetime, $model->getDateTimes());
        $this->markTestIncomplete('This was generated with "schranz/test-generator" and should be adjusted.');
    }

    public function createInstance(): ModelWithAddRemoveDateTime
    {
        return new ModelWithAddRemoveDateTime();
    }
}
