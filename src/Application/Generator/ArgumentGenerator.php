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

use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use function Symfony\Component\String\u;

/**
 * @internal
 */
class ArgumentGenerator
{
    /**
     * @return mixed[]
     */
    public function generateArguments(array $methodAttributes, string $behaviour = 'minimal'): array
    {
        $attributes = [];
        foreach ($methodAttributes['params'] as $attributeName => $attributeConfig) {
            $attribute = 'TODO';
            $typeName = null;

            if ($attributeConfig instanceof NullableType && 'minimal' === $behaviour) {
                $attributes[] = null;

                continue;
            } elseif ($attributeConfig instanceof NullableType) {
                $typeName = $attributeConfig->type->name;
            } elseif ($attributeConfig instanceof Identifier) {
                $typeName = $attributeConfig->name;
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
            }

            $attributes[] = $attribute;
        }

        return $attributes;
    }
}
