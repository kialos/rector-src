<?php

declare(strict_types=1);

namespace Rector\DowngradePhp82\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/readonly_classes
 *
 * @see \Rector\Tests\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector\DowngradeReadonlyClassRectorTest
 */
final class DowngradeReadonlyClassRector extends AbstractRector
{
    public function __construct(private readonly VisibilityManipulator $visibilityManipulator)
    {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove "readonly" class type, decorate all properties to "readonly"',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
final readonly class SomeClass
{
    public string $foo;

    public function __construct()
    {
        $this->foo = 'foo';
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final readonly class SomeClass
{
    public readonly string $foo;

    public function __construct()
    {
        $this->foo = 'foo';
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->visibilityManipulator->isReadonly($node)) {
            return null;
        }

        $this->visibilityManipulator->removeReadonly($node);
        $this->makePropertiesReadonly($node);
        $this->makePromotedPropertiesReadonly($node);

        return $node;
    }

    private function makePropertiesReadonly(Class_ $class): void
    {
        foreach ($class->getProperties() as $property) {
            if ($property->isReadonly()) {
                continue;
            }

            if ($property->type === null) {
                continue;
            }

            $this->visibilityManipulator->makeReadonly($property);
        }
    }

    private function makePromotedPropertiesReadonly(Class_ $class): void
    {
        $classMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (! $classMethod instanceof ClassMethod) {
            return;
        }

        foreach ($classMethod->getParams() as $param) {
            if ($this->visibilityManipulator->isReadonly($param)) {
                continue;
            }

            if ($param->type === null) {
                continue;
            }

            if ($param->flags === 0) {
                continue;
            }

            $this->visibilityManipulator->makeReadonly($param);
        }
    }
}
