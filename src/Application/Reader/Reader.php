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

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

/**
 * @internal
 */
final class Reader
{
    public function read(string $content): ReadVisitor
    {
        $readVisitor = new ReadVisitor();

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $nodes = $parser->parse($content);
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($readVisitor);

        $nodeTraverser->traverse($nodes);

        return $readVisitor;
    }
}
