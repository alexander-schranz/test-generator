# PHP Test Generator

This project make usages of [PHPStan](https://github.com/phpstan/phpstan) and [PHPParser](https://github.com/nikic/PHP-Parser)
to generate test cases for a given PHP File.

## Why?

With static code analyzer it is possible to generate tests which where mostly forgotten. The target of the project
is not to generate a whole test cases instead it should generate the most boilerplate code of the test case and tell
which method for the class methods should be implemented.

So example if we have a method like the following:

```php
public function setTitle(?string $title): void
{
    $this->title = $title;
}

public function getTitle(string $title): void
{
    $this->title = $title;
}
```

If you are using code coverage you will get 100% when you are testing:

```php
public function testSetTitle(): void
{
    $model = $this->createInstance();
    $model->setTitle('Test');
    $this->assertSame('Test', $model->getTitle());
}
```

But as `?string` can be seen as an union type of `string|null` the testcase for `null` is missing:

```php
public function testSetTitleNull(): void
{
    $model = $this->createInstance();
    $model->setTitle(null);
    $this->assertNull($model->getTitle());
}
```

The project target is to already generate also the boilderplate for that test case.

## Installation

```bash
composer require --dev schranz/test-generator
```

## Config

Create a new `tests/generator-config.php` file:

```php
<?php

use Schranz\TestGenerator\Domain\Model\Config;

$config = new Config();
$config->hooks[] = 'vendor/bin/php-cs-fixer fix %s';

return $config;
```

See [Config.php](src/Domain/Model/Config.php)  for all options.

## Usage

```bash
vendor/bin/test-generator src/YourNameSpace/YourFile.php
```
