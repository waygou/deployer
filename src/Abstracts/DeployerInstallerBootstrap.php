<?php

namespace Waygou\Deployer\Abstracts;

use Illuminate\Console\Command;
use Waygou\Deployer\Concerns\CanRunProcesses;
use Waygou\Deployer\Concerns\SimplifiesConsoleOutput;
use Waygou\Deployer\Concerns\ValidatesConsoleArguments;

abstract class DeployerInstallerBootstrap extends Command
{
    use CanRunProcesses;
    use SimplifiesConsoleOutput;
    use ValidatesConsoleArguments;

    protected $steps;
}
