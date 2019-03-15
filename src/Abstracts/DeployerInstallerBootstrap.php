<?php

namespace Waygou\Deployer\Abstracts;

use Illuminate\Console\Command;
use Waygou\Deployer\Concerns\CanRunProcesses;
use Waygou\Deployer\Concerns\SharedInstallerActions;
use Waygou\Deployer\Concerns\SimplifiesConsoleOutput;
use Waygou\Deployer\Concerns\ValidatesConsoleArguments;

abstract class DeployerInstallerBootstrap extends Command
{
    use CanRunProcesses;
    use SharedInstallerActions;
    use SimplifiesConsoleOutput;
    use ValidatesConsoleArguments;

    protected $steps;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Quick way to clear the screen :)
        print("\033[2J\033[;H");

        $this->info(ascii_title());
    }
}
