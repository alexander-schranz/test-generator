<?php

namespace App;

class ModelWithSetGetBirthday
{
    private ?\DateTime $birthday;

    public function setBirthday(?\DateTime $birthday): void
    {
        $this->birthday = $birthday;
    }

    public function getBirthday(): ?\DateTime
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

/**
 * @covers \App\ModelWithSetGetBirthday
 */
class ModelWithSetGetBirthdayTest extends TestCase
{
    public function testSetGetBirthday(): void
    {
        $model = $this->createInstance();
        $this->assertNull($model->getBirthday());
        $birthday = new DateTime('2022-01-01');
        $model->setBirthday($birthday);
        $this->assertSame($birthday, $model->getBirthday());
        $model->setBirthday(null);
        $this->assertNull($model->getBirthday());
        $this->markAsRisky();
    }

    public function createInstance(): ModelWithSetGetBirthday
    {
        return new ModelWithSetGetBirthday();
    }
}
