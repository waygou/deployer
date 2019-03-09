<?php

namespace Waygou\Deployer\Abstracts;

use Illuminate\Console\Command;
use Waygou\Deployer\Concerns\CanRunProcesses;
use Waygou\Deployer\Concerns\SimplifiesConsoleOutput;
use Waygou\Deployer\Concerns\ValidatesConsoleArguments;

abstract class DeployerInstaller extends Command
{
    use CanRunProcesses;
    use SimplifiesConsoleOutput;
    use ValidatesConsoleArguments;

    protected $steps;
    protected $token = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
    }

    protected function artisanMigrate()
    {
        $this->bulkInfo(2, 'Running Artisan migrate...', 1);
        $this->runProcess('php artisan migrate --quiet');
    }

    protected function publishDeployerResources()
    {
        $this->bulkInfo(2, 'Publishing Deployer configuration ...', 1);
        $this->runProcess('php artisan vendor:publish --provider="Waygou\Deployer\DeployerServiceProvider" --force --quiet');
    }

    protected function installLaravelPassport()
    {
        $this->bulkInfo(2, 'Installing Laravel Passport...', 1);
        $this->runProcess('composer require laravel/passport');
        $this->runProcess('php artisan vendor:publish --provider="Laravel\Passport\PassportServiceProvider" --quiet');
        $this->runProcess('php artisan migrate --quiet');
        $this->runProcess('php artisan passport:install --quiet');
        $this->runProcess('composer dumpautoload');
    }

    protected function clearConfigurationCache()
    {
        $this->bulkInfo(2, 'Cleaning Configuration cache...', 1);
        $this->runProcess('php artisan configuration:clear --quiet', getcwd());
    }
}
