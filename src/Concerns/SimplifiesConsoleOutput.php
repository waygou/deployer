<?php

namespace Waygou\Deployer\Concerns;

trait SimplifiesConsoleOutput
{
    protected function showHero()
    {
        $this->bulkInfo(1, ascii_title(), 1);
    }

    protected function bulkInfo(int $crBefore, string $message, int $crAfter = 0)
    {
        while ($crBefore > 0) {
            $this->info('');
            $crBefore--;
        }

        $this->info($message);

        while ($crAfter > 0) {
            $this->info('');
            $crAfter--;
        }
    }
}
