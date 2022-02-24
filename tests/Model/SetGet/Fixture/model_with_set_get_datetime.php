<?php

namespace App;

class ModelWithSetGetBirthday
{
    private \DateTime $birthday;

    public function setBirthday(\Datetime $birthday): void
    {
        $this->birthday = $birthday;
    }

    public function getBirthday(): \Datetime
    {
        return $this->birthday;
    }
}

?>
---
<?php

namespace App\Tests\Unit;

use App\ModelWithSetGetBirthday;
use DateTime;
use PHPUnit\Framework\TestCase;

class ModelWithSetGetBirthdayTest extends TestCase
{
    public function testSetGetBirthday(): void
    {
        $model = $this->createInstance();
        $this->assertNull($model->getBirthday());
        $model->setBirthday(new DateTime('2022-02-24 12:00:00'));
        $this->assertSame(new DateTime('2022-02-24 12:00:00'), $model->getBirthday());
        $this->markAsRisky();
    }

    public function createInstance(): ModelWithSetGetBirthday
    {
        return new ModelWithSetGetBirthday();
    }
}
