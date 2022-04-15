<?php

declare(strict_types=1);

namespace Schranz\TestGenerator\Application\Writer;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitorAbstract;
use Schranz\TestGenerator\Application\Generator\ArgumentGenerator;
use Schranz\TestGenerator\Application\Grouper\MethodGrouper;
use Schranz\TestGenerator\Application\Reader\ReadVisitor;

/**
 * @internal
 */
final class WriteVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private ReadVisitor $readVisitor,
        private ArgumentGenerator $argumentGenerator,
        private MethodGrouper $methodGrouper
    ) {
    }

    public function leaveNode(Node $node)
    {
        if (!$node instanceof Class_) {
            return null;
        }

        $testFactoryMethod = 'createInstance';
        $factory = new BuilderFactory();

        $testMethodConfigs = $this->methodGrouper->groupMethods($this->readVisitor->getMethods());

        $constructAttributes = ['params' => []];
        $constructArguments = [];
        foreach ($testMethodConfigs as $testMethodName => $testMethodConfig) {
            if ('construct' === $testMethodConfig['type']) {
                $constructAttributes = $testMethodConfig['options']['attributes'];
                $constructArguments = $this->argumentGenerator->generateArguments($constructAttributes, 'minimal');

                continue;
            }

            $method = $factory->method($testMethodName)
                ->makePublic()
                ->setReturnType('void');

            if ('get_set' === $testMethodConfig['type']) {
                $method->addStmt(
                    new Node\Expr\Assign(
                        $factory->var('model'),
                        $factory->methodCall($factory->var('this'), $testFactoryMethod)
                    )
                );

                $method->addStmt(
                    $factory->methodCall(
                        $factory->var('this'),
                        'assertSame',
                        [
                            $factory->val(null),
                            $factory->methodCall(
                                $factory->var('model'),
                                $testMethodConfig['options']['getMethod']
                            ),
                        ]
                    )
                );

                $setterArgumentsList = [];
                $setterArgumentsList[] = $this->argumentGenerator->generateArguments($testMethodConfig['options']['setMethodAttributes'], 'minimal');
                if ($setterArgumentsList[0][\array_key_first($setterArgumentsList[0])] instanceof Node\Expr\ConstFetch) {
                    $setterArgumentsList[] = $this->argumentGenerator->generateArguments($testMethodConfig['options']['setMethodAttributes'], 'full');
                    $setterArgumentsList = \array_reverse($setterArgumentsList);
                }

                foreach ($setterArgumentsList as $setterArguments) {
                    foreach ($setterArguments as $attributeName => $setterArgument) {
                        if ($setterArgument instanceof Node\Expr\New_) {
                            $method->addStmt(
                                new Node\Expr\Assign(
                                    $factory->var($attributeName),
                                    $setterArgument
                                )
                            );

                            $setterArguments[$attributeName] = $factory->var($attributeName);
                        }
                    }

                    $setterArguments = \array_values($setterArguments);

                    $setterMethod = $factory->methodCall(
                        $factory->var('model'),
                        $testMethodConfig['options']['setMethod'],
                        $setterArguments
                    );

                    $setReturnType = $testMethodConfig['options']['setMethodAttributes']['returnType'];

                    if ($setReturnType instanceof Identifier && 'void' === $setReturnType->name) {
                        $method->addStmt(
                            $setterMethod
                        );
                    } else {
                        $method->addStmt(
                            $factory->methodCall(
                                $factory->var('this'),
                                'assertSame',
                                [
                                    $factory->var('model'),
                                    $setterMethod,
                                ]
                            )
                        );
                    }

                    $method->addStmt(
                        $factory->methodCall(
                            $factory->var('this'),
                            'assertSame',
                            [
                                $setterArguments[0] ?? null,
                                $factory->methodCall(
                                    $factory->var('model'),
                                    $testMethodConfig['options']['getMethod']
                                ),
                            ]
                        )
                    );
                }
            } elseif ('get' === $testMethodConfig['type']) {
                $methodAttributeName = \lcfirst(\str_replace('get', '', $testMethodConfig['options']['method']));
                $generatedConstructParams = $this->argumentGenerator->generateArguments($constructAttributes);
                $constructorArguments = [];

                if (isset($generatedConstructParams[$methodAttributeName])) {
                    if ($generatedConstructParams[$methodAttributeName] instanceof Node\Expr\New_) {
                        $method->addStmt(
                            new Node\Expr\Assign(
                                $factory->var($methodAttributeName),
                                $generatedConstructParams[$methodAttributeName]
                            )
                        );

                        $generatedConstructParams[$methodAttributeName] = $factory->var($methodAttributeName);
                    }

                    $constructorArguments[] = [$methodAttributeName => $generatedConstructParams[$methodAttributeName]];
                }

                $method->addStmt(
                    new Node\Expr\Assign(
                        $factory->var('model'),
                        $factory->methodCall(
                            $factory->var('this'),
                            $testFactoryMethod,
                            $constructorArguments
                        )
                    )
                );

                $method->addStmt(
                    $factory->methodCall(
                        $factory->var('this'),
                        'assertSame',
                        [
                            $generatedConstructParams[$methodAttributeName] ?? 'TODO',
                            $factory->methodCall($factory->var('model'), $testMethodConfig['options']['method']),
                        ]
                    )
                );
            } elseif ('set' === $testMethodConfig['type']) {
                $method->addStmt(
                    new Node\Expr\Assign(
                        $factory->var('model'),
                        $factory->methodCall($factory->var('this'), $testFactoryMethod)
                    )
                );
            } else {
                $method->addStmt(
                    new Node\Expr\Assign(
                        $factory->var('model'),
                        $factory->methodCall($factory->var('this'), $testFactoryMethod)
                    )
                );

                $method->addStmt(
                    $factory->methodCall($factory->var('model'), $testMethodConfig['options']['method'])
                );
            }

            $method->addStmt(
                $factory->methodCall(
                    $factory->var('this'),
                    'markAsIncomplete',
                    ['This this was generated with "schranz/test-generator" and should be adjusted.']
                )
            );

            $node->stmts[] = $method->getNode();
        }

        $args = [];
        foreach (\array_keys($constructAttributes['params']) as $key) {
            $args[] = new Coalesce(
                $factory->var('data["' . $key . '"]'),
                $constructArguments[$key] instanceof Node\Expr ? $constructArguments[$key] : $factory->val($constructArguments[$key])
            );
        }

        $return = new Return_(
            $factory->new(
                '\\' . $this->readVisitor->getClass(),
                $args
            )
        );

        $builder = $factory->method($testFactoryMethod)
            ->addStmt(
                $return
            )
            ->setReturnType('\\' . $this->readVisitor->getClass())
            ->makePublic();

        // TODO add phpdoc
        if (\count($constructAttributes['params'])) {
            $builder
                ->addParam(
                    $factory->param('data')
                        ->setDefault([])
                );
        }

        $node->stmts[] = $builder->getNode();

        return $node;
    }
}
