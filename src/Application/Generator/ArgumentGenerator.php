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
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use function Symfony\Component\String\u;

/**
 * @internal
 */
class ArgumentGenerator
{
    /**
     * @param array{
     *     params: array<string, null|Identifier|Name|ComplexType>,
     *     returnType: array<string, null|Identifier|Name|ComplexType>,
     * } $methodAttributes
     *
     * @return mixed[]
     */
    public function generateArguments(array $methodAttributes, string $behaviour = 'minimal'): array
    {
        $factory = new BuilderFactory();

        $attributes = [];
        foreach ($methodAttributes['params'] as $attributeName => $attributeConfig) {
            $attribute = 'TODO';
            $typeName = null;

            if ($attributeConfig instanceof NullableType && 'minimal' === $behaviour) {
                $attributes[] = null;

                continue;
            } elseif ($attributeConfig instanceof NullableType) {
                $typeName = $attributeConfig->type->toString();
            } elseif ($attributeConfig instanceof Identifier) {
                $typeName = $attributeConfig->name;
            } elseif ($attributeConfig instanceof FullyQualified) {
                $typeName = $attributeConfig->toString();
            }

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
                    $value = $dateTimeImmutableAttributes[\count($dateTimeImmutableAttributes) - 1] ?? new \DateTime('2021-12-31');
                    $value->add(\DateInterval::createFromDateString('1 day'));
                    $dateTimeImmutableAttributes[$attributeName] = $factory->new(
                        '\\' . \DateTimeImmutable::class,
                        [$value->format('Y-m-d')]
                    );
                }

                $attribute = $dateTimeImmutableAttributes[$attributeName];
            } elseif ('Datetime' === $typeName) {
                static $dateTimeAttributes = [];
                if (!isset($dateTimeAttributes[$attributeName])) {
                    $value = $dateTimeImmutableAttributes[\count($dateTimeAttributes) - 1] ?? new \DateTime('2021-12-31');
                    $value->add(\DateInterval::createFromDateString('1 day'));
                    $dateTimeAttributes[$attributeName] = $factory->new(
                        '\\' . \DateTime::class,
                        [$value->format('Y-m-d')]
                    );
                }

                $attribute = $dateTimeAttributes[$attributeName];
            }

            $attributes[$attributeName] = $attribute;
        }

        return $attributes;
    }
}
