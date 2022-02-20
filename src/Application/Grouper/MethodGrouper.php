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

namespace Schranz\TestGenerator\Application\Grouper;

/**
 * @internal
 */
class MethodGrouper
{
    /**
     * @param array<string, array{
     *     params: array<string, null|Identifier|Name|ComplexType>,
     *     returnType: array<string, null|Identifier|Name|ComplexType>,
     * }> $methods
     *
     * @return array<string, array{
     *      name: string,
     *      type: string,
     *      options: mixed[]
     * }>
     */
    public function groupMethods(array $methods): array
    {
        $testMethods = [];

        foreach ($methods as $method => $methodAttributes) {
            $name = 'test' . \ucfirst($method);
            $type = 'custom';
            $options = [
                'method' => $method,
                'attributes' => $methodAttributes,
            ];

            if (\str_starts_with($method, 'get')) {
                $type = 'get';
                $setMethod = \str_replace('get', 'set', $method);
                if (!isset($methods[$setMethod])) {
                    $setMethod = \str_replace('get', 'change', $method);
                }

                if (isset($methods[$setMethod])) {
                    $type = 'get_set';
                    $name = 'testSetGet' . \substr($method, 3);

                    $options = [
                        'getMethod' => $method,
                        'getMethodAttributes' => $methodAttributes,
                        'setMethod' => $setMethod,
                        'setMethodAttributes' => $methods[$setMethod],
                    ];
                }
            } elseif (\str_starts_with($method, 'set')) {
                $type = 'set';
                $getMethod = \str_replace('set', 'get', $method);
                if (isset($methods[$getMethod])) {
                    $type = 'get_set';
                    $name = 'testSetGet' . \substr($method, 3);
                    if (\str_starts_with($method, 'change')) {
                        $name = 'testChangeGet' . \substr($method, 3);
                    }

                    $options = [
                        'getMethod' => $getMethod,
                        'getMethodAttributes' => $methods[$getMethod],
                        'setMethod' => $method,
                        'setMethodAttributes' => $methodAttributes,
                    ];
                }
            } elseif (\str_starts_with($method, 'change')) {
                $type = 'set';
                $getMethod = \str_replace('change', 'get', $method);
                if (isset($methods[$getMethod])) {
                    $type = 'get_set';
                    $name = 'testChangeGet' . \substr($method, 3);

                    $options = [
                        'getMethod' => $getMethod,
                        'getMethodAttributes' => $methods[$getMethod],
                        'setMethod' => $method,
                        'setMethodAttributes' => $methodAttributes,
                    ];
                }
            } elseif (\str_starts_with($method, 'add')) {
                $type = 'add';
                $removeMethod = \str_replace('add', 'remove', $method);
                if (isset($methods[$removeMethod])) {
                    $type = 'add_remove';
                    $name = 'testAddRemove' . \substr($method, 3);

                    $options = [
                        'addMethod' => $method,
                        'addMethodAttributes' => $methodAttributes,
                        'removeMethod' => $removeMethod,
                        'removeMethodAttributes' => $methods[$removeMethod],
                    ];
                }
            } elseif (\str_starts_with($method, 'remove')) {
                $type = 'remove';
                $addMethod = \str_replace('remove', 'add', $method);
                if (isset($methods[$addMethod])) {
                    $type = 'add_remove';
                    $name = 'testAddRemove' . \substr($method, 3);

                    $options = [
                        'addMethod' => $addMethod,
                        'addMethodAttributes' => $methods[$addMethod],
                        'removeMethod' => $method,
                        'removeMethodAttributes' => $methodAttributes,
                    ];
                }
            } elseif ('__construct' === $method) {
                $type = 'construct';
            }

            $testMethods[$name] = [
                'name' => $name,
                'type' => $type,
                'options' => $options,
            ];
        }

        return $testMethods;
    }
}
