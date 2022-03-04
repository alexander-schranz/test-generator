<?php

declare(strict_types=1);

namespace Schranz\TestGenerator\Application\Writer;

use PhpParser\Lexer\Emulative;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

/**
 * @internal
 */
class Writer
{
    public function write(string $existContent, WriteVisitor $writeVisitor): string
    {
        $lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);
        $nodes = $parser->parse($existContent);
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($writeVisitor);
        $printer = new Standard();
        $oldTokens = $lexer->getTokens();

        $traversedNodes = $nodeTraverser->traverse($nodes);

        return $printer->printFormatPreserving($traversedNodes, $nodes, $oldTokens);
    }
}
