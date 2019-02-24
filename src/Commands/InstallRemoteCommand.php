<?php

namespace Waygou\Deployer\Commands;

use Laravel\Passport\Client;
use Waygou\Deployer\Abstracts\DeployerInstaller;

class InstallRemoteCommand extends DeployerInstaller
{
    private $client;
    private $secret;

    protected $signature = 'deployer:install-remote
                            {--skippassport : Skips Laravel Passport installation}';

    protected $description = 'Installs Deployer on your Remote Server.';

    public function handle()
    {
        parent::handle();
        $this->steps = 4;

        if (! $this->option('skippassport')) {
            $this->installLaravelPassport();
        }

        $this->publishDeployerResources();
        $bar = $this->output->createProgressBar($this->steps);
        $bar->start();

        if (! $this->checkEnv()) {
            return;
        }

        $this->artisanMigrate();
        $bar->advance();

        $this->registerRemoteType();
        $bar->advance();

        $this->installClientCredentialsGrant();
        $this->getClientCredentialsGrant();
        $bar->advance();

        $this->registerRemoteToken();
        $bar->advance();

        $bar->finish();
        $this->showLocalInstallInformation();
    }

    protected function showLocalInstallInformation()
    {
        $this->bulkInfo(2, 'Please install Deployer on your local Laravel app and run the following artisan command:', 1);
        $this->info("php artisan deployer:install-local --client={$this->client} --secret={$this->secret} --token={$this->token}");
    }

    protected function getClientCredentialsGrant()
    {
        $client = Client::latest()->first();
        $this->client = $client->id;
        $this->secret = $client->secret;
    }

    protected function installClientCredentialsGrant()
    {
        $this->bulkInfo(2, 'Installing client credentials grant...', 1);
        $appName = 'Laravel Deployer Grant Client';
        $this->runProcess("php artisan passport:client --client
                                                       --name=\"{$appName}\"
                                                       --quiet", getcwd());
    }

    protected function registerRemoteType()
    {
        $this->bulkInfo(2, 'Registering Deployer Remote Type in your .env file...', 1);
        append_line_to_env('DEPLOYER_TYPE', 'remote') ?:
        $this->error(self::ERROR_WRITE_PERMISSION);
    }

    protected function registerRemoteToken()
    {
        /*
         * Register the remote<->local token, used on all REST transactions.
         * This is an extra security layer between your local and remote environments.
         */
        $this->bulkInfo(2, 'Registering Deployer token and adding it to your .env file...', 1);
        $this->token = $this->token ?? str_random(10);
        file_put_contents(base_path('.env'), PHP_EOL."DEPLOYER_TOKEN={$this->token}", FILE_APPEND) ?: $this->error('.env file without writing permissions. Please check your .env file writing permissions. Aborting.');
    }

    protected function checkEnv()
    {
        if (app('config')->get('app.debug') == false) {
            // Security check.
            $this->info('');

            return $this->confirm("Looks like your remote server doesn't have debug activated. Do you wish to continue?");
        }

        return true;
    }
}
