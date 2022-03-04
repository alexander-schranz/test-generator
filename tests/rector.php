<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\LevelSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $services = $containerConfigurator->services();

    $parameters->set(Option::PATHS, [\sys_get_temp_dir() . '/test-generator']);
    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, __DIR__ . '/../phpstan.neon');

    // basic rules
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
    $containerConfigurator->import(LevelSetList::UP_TO_PHP_80);
};
