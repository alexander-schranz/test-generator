<?php

declare(strict_types=1);

namespace Schranz\TestGenerator\Application\Writer;

use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\ParserAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

/**
 * @internal
 */
class Writer
{
    public function write(string $existContent, WriteVisitor $writeVisitor): string
    {
        $lexer = null;
        $parser =
            \method_exists(ParserAbstract::class, 'getTokens')
                ? (new ParserFactory())->createForNewestSupportedVersion()
                : (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer = new Emulative([
                    'usedAttributes' => [
                        'comments',
                        'startLine', 'endLine',
                        'startTokenPos', 'endTokenPos',
                    ],
                ]));

        $nodes = $parser->parse($existContent);
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($writeVisitor);
        $printer = new Standard();

        $oldTokens = $lexer instanceof Emulative ? $lexer->getTokens() : $parser->getTokens();

        $traversedNodes = $nodeTraverser->traverse($nodes);

        return $printer->printFormatPreserving($traversedNodes, $nodes, $oldTokens);
    }
}
