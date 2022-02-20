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

use Schranz\TestGenerator\Domain\Model\Config;

/**
 * @internal
 */
class TestFileGenerator
{
    public function generateTestFile(string $class, Config $config): string
    {
        $classParts = \explode('\\', \strrev($class), 2);
        $className = \strrev($classParts[0]);
        $testClassName = $className . $config->classPostfix;

        $testNamespace = \strrev($classParts[1]);
        foreach ($config->namespaceReplaces as $old => $new) {
            $testNamespace = \str_replace($old, $new, $testNamespace);
        }
        $testExtendClass = $config->testExtendedClass;
        $testExtendClassName = $config->getTestExtendedClassName();

        return "<?php

namespace $testNamespace;

use $testExtendClass;
use $class;

class $testClassName extends $testExtendClassName
{

}";
    }
}
