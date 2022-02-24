<?php

declare(strict_types=1);

/**
 * This file is part of the test generator project.
 *
 * (c) Alexander Schranz (https://github.com/alexander-schranz/test-generator)
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Schranz\TestGenerator\Application\Generator;

use PhpParser\BuilderFactory;
use PhpParser\Node\NullableType;
use function Symfony\Component\String\u;

/**
 * @internal
 */
class ArgumentGenerator
{
    private DateTimeGenerator $dateTimeGenerator;

    public function __construct(?DateTimeGenerator $dateTimeGenerator = null)
    {
        $this->dateTimeGenerator = $dateTimeGenerator ?: new DateTimeGenerator();
    }

    /**
     * @return mixed[]
     */
    public function generateArguments(array $methodAttributes, array &$additionalUsages, string $behaviour = 'minimal'): array
    {
        $factory = new BuilderFactory();

        $attributes = [];
        foreach ($methodAttributes['params'] as $attributeName => $attributeConfig) {
            $attribute = 'TODO';
            $type = null;

            if ($attributeConfig instanceof NullableType && 'minimal' === $behaviour) {
                $attributes[] = null;

                continue;
            }

            $type = $attributeConfig;
            if ($type instanceof NullableType) {
                $type = $attributeConfig->type;
            }

            $typeName = $type->toString();

            if ('string' === $typeName) {
                $attributes[] = u($attributeName)->title()->toString();
                continue;
            } elseif ('int' === $typeName) {
                static $intAttributes = [];
                if (!isset($intAttributes[$attributeName])) {
                    $intAttributes[$attributeName] = \count($intAttributes) + 1;
                }

                $attribute = (int) $intAttributes[$attributeName];
            } elseif ('float' === $typeName) {
                static $floatAttributes = [];
                if (!isset($floatAttributes[$attributeName])) {
                    $float = (\count($floatAttributes) + 1 + ((\count($floatAttributes) + 1) / 100));
                    $floatAttributes[$attributeName] = $float;
                }

                $attribute = $floatAttributes[$attributeName];
            } elseif ('DateTimeImmutable' === $typeName) {
                static $dateTimeImmutableAttributes = [];
                if (!isset($dateTimeImmutableAttributes[$attributeName])) {
                    $value = $this->dateTimeGenerator->generate();
                    $dateTimeImmutableAttributes[$attributeName] = $factory->new(
                        \DateTimeImmutable::class,
                        [$value->format('Y-m-d H:i:s')]
                    );
                }

                $attribute = $dateTimeImmutableAttributes[$attributeName];
                $additionalUsages[\DateTimeImmutable::class] = $factory->use(\DateTimeImmutable::class)->getNode();
            } elseif ('Datetime' === $typeName) {
                static $dateTimeAttributes = [];
                if (!isset($dateTimeAttributes[$attributeName])) {
                    $value = $this->dateTimeGenerator->generate();
                    $dateTimeAttributes[$attributeName] = $factory->new(
                        \DateTime::class,
                        [$value->format('Y-m-d H:i:s')]
                    );
                }

                $attribute = $dateTimeAttributes[$attributeName];
                $additionalUsages[\DateTime::class] = $factory->use(\DateTime::class)->getNode();
            }

            $attributes[] = $attribute;
        }

        return $attributes;
    }
}
