<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

final class DemoFile
{
    public function run()
    {
        return 6;

        // we never get here
        return 10;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

// what is expected code?
// should remain the same? delete part bellow ----- (included)

?>
