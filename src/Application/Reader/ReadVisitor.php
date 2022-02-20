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

namespace Schranz\TestGenerator\Application\Reader;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class ReadVisitor extends NodeVisitorAbstract
{
    private string $namespace;

    private string $className;

    /**
     * @var mixed[]
     */
    private array $methods = [];

    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            $this->namespace = $node->name->toString();

            return null;
        }

        if ($node instanceof ClassLike) {
            $this->className = $node->name->toString();

            return null;
        }

        if (!$node instanceof ClassMethod) {
            return null;
        }

        if ($node->isAbstract()
            || $node->isPrivate()
            || $node->isProtected()
        ) {
            return null;
        }

        $params = [];
        foreach ($node->getParams() as $param) {
            $params[$param->var->name] = $param->type;
        }

        $this->methods[$node->name->toString()] = [
            'params' => $params,
            'returnType' => $node->returnType,
        ];

        return null;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getClass(): string
    {
        return $this->namespace . '\\' . $this->className;
    }

    /**
     * @return array<string, array{
     *     params: array<string, null|Identifier|Name|ComplexType>,
     *     returnType: array<string, null|Identifier|Name|ComplexType>,
     * }>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
}
