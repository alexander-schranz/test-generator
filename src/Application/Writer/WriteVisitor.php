<?php

declare(strict_types=1);

namespace Schranz\TestGenerator\Application\Writer;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitorAbstract;
use Schranz\TestGenerator\Application\Generator\ArgumentGenerator;
use Schranz\TestGenerator\Application\Grouper\MethodGrouper;
use Schranz\TestGenerator\Application\Reader\ReadVisitor;
use function Symfony\Component\String\u;

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

        $constructParams = [];
        foreach ($testMethodConfigs as $testMethodName => $testMethodConfig) {
            if ('construct' === $testMethodConfig['type']) {
                $constructParams = $testMethodConfig['options']['attributes']['params'];

                continue;
            }

            $method = $factory->method($testMethodName)
                ->makePublic()
                ->setReturnType('void');

            $method->addStmt(
                new Node\Expr\Assign(
                    $factory->var('model'),
                    $factory->methodCall($factory->var('this'), $testFactoryMethod)
                )
            );

            if ('get_set' === $testMethodConfig['type']) {
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
                if (($setterArgumentsList[0][0] ?? null) === null) {
                    $setterArgumentsList[] = $this->argumentGenerator->generateArguments($testMethodConfig['options']['setMethodAttributes'], 'full');
                    $setterArgumentsList = \array_reverse($setterArgumentsList);
                }

                foreach ($setterArgumentsList as $setterArguments) {
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
                $method->addStmt(
                    $factory->methodCall(
                        $factory->var('this'),
                        'assertSame',
                        [
                            'TODO',
                            $factory->methodCall($factory->var('model'), $testMethodConfig['options']['method']),
                        ]
                    )
                );
            } elseif ('set' === $testMethodConfig['type']) {
            } else {
                $method->addStmt(
                    $factory->methodCall($factory->var('model'), $testMethodConfig['options']['method'])
                );
            }

            $method->addStmt(
                $factory->methodCall(
                    $factory->var('this'),
                    'markAsRisky'
                )
            );

            $node->stmts[] = $method->getNode();
        }

        $args = [];
        /**
         * @var string $key
         * @var Name $type
         */
        foreach ($constructParams as $key => $type) {
            $defaultValue = '"TODO"';
            if ($type instanceof Identifier) {
                if ('string' === $type->name) {
                    $defaultValue = '"' . u($key)->title() . '"';
                }
            }

            $args[] = $factory->var('data["' . $key . '"] ?? ' . $defaultValue);
        }

        $return = new Return_(
            $factory->new(
                $this->readVisitor->getClassName(),
                $args
            )
        );

        $builder = $factory->method($testFactoryMethod)
            ->addStmt(
                $return
            )
            ->setReturnType($this->readVisitor->getClassName())
            ->makePublic();

        // TODO add phpdoc
        if (\count($constructParams)) {
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
