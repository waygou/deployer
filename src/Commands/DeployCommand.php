<?php

namespace Waygou\Deployer\Commands;

use Waygou\Deployer\Abstracts\DeployerInstallerBootstrap;
use Waygou\Deployer\Concerns\SimplifiesConsoleOutput;
use Waygou\Deployer\Support\CodebaseRepository;
use Waygou\Deployer\Support\Local;

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

        $this->steps = 8;

        $bar = $this->output->createProgressBar($this->steps);
        $bar->start();

        $this->runPreChecks();
        $bar->advance();

        $this->pingRemote();
        $bar->advance();

        $this->askRemoteForPreChecks();
        $bar->advance();

        // The repository code generated in this moment is the PK for all the next transactions.
        $this->transaction = generate_transaction_code();

        $this->createLocalRepository();
        $bar->advance();

        $this->uploadCodebase();
        $bar->advance();

        $this->runPreScripts();
        $bar->advance();

        $this->deploy();
        $bar->advance();

        $this->runPostScripts();
        $bar->finish();

        $this->bulkInfo(2, '*** All good! Package deployed! ***', 1);
    }

    protected function createLocalRepository()
    {
        $this->bulkInfo(2, 'Creating local environment repository (runbook and codebase zip)...', 1);

        rescue(function () {
            Local::createRepository($this->transaction);
        }, function () {
            $this->gracefullyExit();
        });
    }

    protected function runPostScripts()
    {
        $this->bulkInfo(2, 'Running your post-scripts after unpacking your codebase (if they exist)...', 1);

        rescue(function () {
            Local::getAccessToken()
                 ->runPostScripts($this->transaction);
        }, function () {
            $this->gracefullyExit();
        });
    }

    protected function deploy()
    {
        $this->bulkInfo(2, 'Unpacking your codebase on your remote server...', 1);

        rescue(function () {
            Local::getAccessToken()
                 ->deploy($this->transaction);
        }, function () {
            $this->gracefullyExit();
        });
    }

    protected function runPreScripts()
    {
        $this->bulkInfo(2, 'Running your pre-scripts after unpacking your codebase (if they exist)...', 1);

        rescue(function () {
            Local::getAccessToken()
                 ->runPreScripts($this->transaction);
        }, function () {
            $this->gracefullyExit();
        });
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
