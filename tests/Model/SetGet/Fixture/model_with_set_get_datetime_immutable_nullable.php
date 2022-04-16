<?php

namespace App;

class ModelWithSetGetBirthday
{
    private ?\DateTimeImmutable $birthday;

    public function setBirthday(?\DateTimeImmutable $birthday): void
    {
        $this->birthday = $birthday;
    }

    public function getBirthday(): ?\DateTimeImmutable
    {
        return $this->birthday;
    }
}

?>
---
<?php

namespace App\Tests\Unit;

use App\ModelWithSetGetBirthday;
use DateTimeImmutable;
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
        $birthday = new DateTimeImmutable('2022-01-01');
        $model->setBirthday($birthday);
        $this->assertSame($birthday, $model->getBirthday());
        $model->setBirthday(null);
        $this->assertNull($model->getBirthday());
        $this->markAsIncomplete('This was generated with "schranz/test-generator" and should be adjusted.');
    }

    public function createInstance(): ModelWithSetGetBirthday
    {
        return new ModelWithSetGetBirthday();
    }
}
