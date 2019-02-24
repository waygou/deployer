<?php

namespace Waygou\Deployer\Concerns;

use Symfony\Component\Process\Process;

trait CanRunProcesses
{
    protected function runProcess($command, $path = null)
    {
        $path = $path ?? getcwd();

        $process = (new Process($command, $path))->setTimeout(null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            $process->setTty(true);
        }

        $process->run(function ($type, $line) {
            $this->output->write($line);
        });
    }
}
