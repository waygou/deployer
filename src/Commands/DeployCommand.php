<?php

namespace Waygou\Deployer\Commands;

use Waygou\Deployer\Local;
use Illuminate\Console\Command;
use Waygou\Deployer\Concerns\SimplifiesConsoleOutput;

final class DeployCommand extends Command
{
    use SimplifiesConsoleOutput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy';

    private $zipFilename = null;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploys your codebase content to a remote server, hopefully in a flash :)';

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
        $this->showHero();

        $bar = $this->output->createProgressBar(11);
        $bar->start();

        $this->preChecks();
        $bar->advance();

        $this->pingRemote();
        $bar->advance();

        $this->askRemoteForPreChecks();
        $bar->advance();

        $this->createZip();
        $bar->advance();

        $this->uploadZip();
        $bar->advance();

        dd('-- on the zip --');

        $this->bulkInfo(2, '*** Package Upload ***', 1);
        Local::getAccessToken()
             ->upload();
        $bar->advance();

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

    protected function uploadZip()
    {
        $this->bulkInfo(2, '*** Uploading Zip to remote server ***', 1);

        Local::getAccessToken()
             ->uploadZip($this->zipFilename);

        rescue(function () {
            Local::getAccessToken()
                 ->uploadZip($this->zipFilename);
        }, function () {
            $this->gracefullyExit();
        });
    }

    protected function createZip()
    {
        $this->bulkInfo(2, '*** Local codebase package creation (Zip) ***', 1);

        rescue(function () {
            $this->zipFilename = Local::CreateCodebaseZip();
        }, function () {
            $this->gracefullyExit();
        });
    }

    protected function askRemoteForPreChecks()
    {
        $this->bulkInfo(2, '*** Remote server pre-deployment checks ***', 1);

        rescue(function () {
            Local::getAccessToken()
                 ->askRemoteForPreChecks();
        }, function () {
            $this->gracefullyExit();
        });
    }

    protected function pingRemote()
    {
        $this->bulkInfo(2, '*** OAuth & Remote Server connectivity test ***', 1);

        rescue(function () {
            Local::getAccessToken()
                 ->ping();
        }, function () {
            $this->gracefullyExit();
        });
    }

    protected function preChecks()
    {
        $this->bulkInfo(2, '*** Local environment pre-deployment checks ***', 1);
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
