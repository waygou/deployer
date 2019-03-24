<?php

namespace Waygou\Deployer\Concerns;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\RuntimeException;

trait CanRunProcesses
{
    /**
     * Executes a process.
     *
     * @param  string $command
     * @param  string $path
     * @return void|string
     *
     * @throws Symfony\Component\Process\Exception\RuntimeException
     */
    protected function runProcess($command, $path = null)
    {
        $path = $path ?? getcwd();

        $process = (new Process($command, $path))->setTimeout(null);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        return $process->getOutput();
    }
}
