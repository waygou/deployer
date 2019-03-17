<?php

namespace Waygou\Deployer\Commands;

use Waygou\Deployer\Support\Local;
use Waygou\Deployer\Concerns\SimplifiesConsoleOutput;
use Waygou\Deployer\Abstracts\DeployerInstallerBootstrap;

final class DeployCommand extends DeployerInstallerBootstrap
{
    use SimplifiesConsoleOutput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploys your codebase content to your remote environment';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

        $this->steps = 10;

        $bar = $this->output->createProgressBar($this->steps);
        $bar->start();

        $this->runPreChecks();
        $bar->advance();

        $this->pingRemote();
        $bar->advance();

        $this->askRemoteForPreChecks();
        $bar->advance();

        $this->createZip();
        $bar->advance();

        $this->uploadCodebase();

        dd('-- *** --');

        $this->bulkInfo(2, '*** Package Upload consistency check ***', 1);
        Local::getAccessToken()
             ->askRemoteForConsistencyCheck();
        $bar->advance();

        $this->bulkInfo(2, '*** Remote Server codebase backup ***', 1);
        Local::getAccessToken()
             ->askRemoteForBackup();
        $bar->advance();

        $this->bulkInfo(2, '*** Remote Server pre-commands run (if any) ***', 1);
        Local::getAccessToken()
             ->askRemoteToRunPreCommands();
        $bar->advance();

        $this->bulkInfo(2, '*** Remote Server codebase package deployment ***', 1);
        Local::getAccessToken()
             ->askRemoteForCodebaseDeployment();
        $bar->advance();

        $this->bulkInfo(2, '*** Remote Server post-commands run (if any) ***', 1);
        Local::getAccessToken()
             ->askRemoteToRunPostCommands();
        $bar->finish();

        $this->bulkInfo(2, '*** All good! ***', 1);
    }

    protected function uploadCodebase()
    {
        $this->bulkInfo(2, 'Uploading package to remote environment...', 1);

        rescue(function () {
            Local::getAccessToken()
                 ->uploadCodebase($this->transaction);
        }, function () {
            $this->gracefullyExit();
        });
    }

    protected function createZip()
    {
        $this->bulkInfo(2, 'Creating your codebase file package...', 1);

        // Also the zip filename.
        $this->transaction = date('Ymd-His');

        rescue(function () {
            Local::CreateCodebaseZip($this->transaction);
        }, function () {
            $this->gracefullyExit();
        });
    }

    protected function askRemoteForPreChecks()
    {
        $this->bulkInfo(2, 'Asking remote server to make its pre-checks...', 1);

        rescue(function () {
            Local::getAccessToken()
                 ->askRemoteForPreChecks();
        }, function () {
            $this->gracefullyExit();
        });
    }

    protected function pingRemote()
    {
        $this->bulkInfo(2, 'Checking OAuth & remote environment connectivity...', 1);

        Local::getAccessToken();

        dd('flap');

        rescue(function () {
            Local::getAccessToken()
                 ->ping();
        }, function () {
            $this->gracefullyExit();
        });
    }

    protected function runPreChecks()
    {
        $this->bulkInfo(2, 'Checking local environment storage availability...', 1);
        rescue(function () {
            Local::preChecks();
        }, function () {
            $this->gracefullyExit();
        });
    }

    protected function executeOrFail(callable $process, $errorKey)
    {
        rescue($process, function () use ($errorKey) {
            $this->error(__("deployer::exceptions.{$errorKey}"));
            exit();
        });
    }

    protected function gracefullyExit($message = null)
    {
        $this->error($message ?? 'Ups. Looks like this step failed. Please check your Laravel logs for more information');
        exit();
    }
}
