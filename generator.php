<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use PhpParser\BuilderFactory;
use PhpParser\Lexer\Emulative;
use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Filesystem\Filesystem;
use function Symfony\Component\String\u;

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

$readVisitor = new ReadVisitor();

// config
$srcDirectory = \dirname(__DIR__) . '/src';
$unitTestDirectory = \dirname(__DIR__) . '/tests/Unit';
$namespaceReplace = ['App' => 'App\Tests\Unit'];
$classPostfix = 'Test';
$testExtendClass = 'PHPUnit\Framework\TestCase';
$testExtendClassName = \strrev(\explode('\\', \strrev($testExtendClass))[0]);

// test file
$filePath = $_SERVER['argv'][1];
if (!$filePath || !file_exists($filePath)) {
    throw new \RuntimeException('Missing .php to generate tests for.');
}

$filePath = \realpath($_SERVER['argv'][1]);

// services
$filesystem = new Filesystem();
$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
$nodes = $parser->parse(\file_get_contents($filePath));
$nodeTraverser = new PhpParser\NodeTraverser();
$nodeTraverser->addVisitor($readVisitor);

// run
$traversedNodes = $nodeTraverser->traverse($nodes);

$class = $readVisitor->getClass();
$unitTestFile = \str_replace([$srcDirectory, '.php'], [$unitTestDirectory, 'Test.php'], $filePath);
$testNamespace = $readVisitor->getNamespace();
foreach ($namespaceReplace as $old => $new) {
    $testNamespace = \str_replace($old, $new, $testNamespace);
}
$testClassName = $readVisitor->getClassName() . $classPostfix;

\unlink($unitTestFile); // TODO remove

if (!$filesystem->exists($unitTestFile)) {
    if (!$filesystem->exists(\dirname($unitTestFile))) {
        $filesystem->mkdir(\dirname($unitTestFile));
    }

    $filesystem->dumpFile($unitTestFile, <<<EOT
<?php

namespace $testNamespace;

use $testExtendClass;
use $class;

class $testClassName extends $testExtendClassName
{

}
EOT);
}

final class WriteVisitor extends NodeVisitorAbstract
{
    public function __construct(private ReadVisitor $readVisitor)
    {
    }

    public function leaveNode(Node $node)
    {
        if (!$node instanceof Class_) {
            return null;
        }

        $testFactoryMethod = 'createInstance';
        $factory = new BuilderFactory();

        $testMethodConfigs = $this->groupMethods($this->readVisitor->getMethods());

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
                $setterArgumentsList[] = $this->generateArguments($testMethodConfig['options']['setMethodAttributes'], 'minimal');
                if (($setterArgumentsList[0][0] ?? null) === null) {
                    $setterArgumentsList[] = $this->generateArguments($testMethodConfig['options']['setMethodAttributes'], 'full');
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

    private function groupMethods(array $methods)
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

    /**
     * @return mixed[]
     */
    private function generateArguments(array $methodAttributes, string $behaviour = 'minimal'): array
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
                $attributes[] = $attributeName;
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

$writeVisitor = new WriteVisitor($readVisitor);

$lexer = new Emulative([
    'usedAttributes' => [
        'comments',
        'startLine', 'endLine',
        'startTokenPos', 'endTokenPos',
    ],
]);

$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);
$nodes = $parser->parse(\file_get_contents($unitTestFile));
$nodeTraverser = new PhpParser\NodeTraverser();
$nodeTraverser->addVisitor($writeVisitor);

$printer = new Standard();

$oldTokens = $lexer->getTokens();

// run
$traversedNodes = $nodeTraverser->traverse($nodes);

$newCode = $printer->printFormatPreserving($traversedNodes, $nodes, $oldTokens);

\file_put_contents($unitTestFile, $newCode);

\shell_exec(\PHP_BINARY . ' ' . __DIR__ . '/../vendor/bin/php-cs-fixer fix "' . $unitTestFile . '"');
