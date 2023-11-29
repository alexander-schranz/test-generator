<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([\sys_get_temp_dir() . '/test-generator']);

    $rectorConfig->phpstanConfigs([
        __DIR__ . '/../phpstan.neon',
    ]);

    // basic rules
    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,
    ]);
};
