<?php

declare(strict_types=1);

namespace Rector\Strict\Rector;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector\BooleanInBooleanNotRuleFixerRectorTest
 */
abstract class AbstractFalsyScalarRuleFixerRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    final public const TREAT_AS_NON_EMPTY = 'treat_as_non_empty';

    protected bool $treatAsNonEmpty = false;

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $treatAsNonEmpty = $configuration[self::TREAT_AS_NON_EMPTY] ?? (bool) current($configuration);
        Assert::boolean($treatAsNonEmpty);

        $this->treatAsNonEmpty = $treatAsNonEmpty;
    }
}
