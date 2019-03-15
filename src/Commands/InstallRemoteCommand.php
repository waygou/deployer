<?php

namespace Waygou\Deployer\Commands;

use Laravel\Passport\Client;
use Illuminate\Support\Facades\DB;
use Waygou\Deployer\Abstracts\DeployerInstallerBootstrap;

class InstallRemoteCommand extends DeployerInstallerBootstrap
{
    private $client;
    private $secret;

    protected $signature = 'deployer:install-remote
                            {--skippassport : Skips Laravel Passport installation}';

    protected $description = 'Installs Deployer on your Remote Server.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->steps = 5;

        // In case of a re-installation, delete all the .env deployer data.
        $this->unsetEnvData();

        if (!is_dir(base_path('vendor/laravel/passport'))) {
            $this->installLaravelPassport();
            $this->steps++;
        };

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

        $this->clearConfigurationCache();
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
        $client = DB::table('oauth_clients')->latest()->first();
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

    protected function installLaravelPassport()
    {
        $this->bulkInfo(2, 'Installing Laravel Passport...', 1);
        $this->runProcess('composer require laravel/passport');
        $this->runProcess('php artisan vendor:publish --provider="Laravel\Passport\PassportServiceProvider" --quiet');
        $this->runProcess('php artisan migrate --quiet');
        $this->runProcess('php artisan passport:install --quiet');
        $this->runProcess('composer dumpautoload');
    }

    protected function registerRemoteType()
    {
        $this->bulkInfo(2, 'Registering Deployer Remote Type in your .env file...', 1);
        $this->env->set('DEPLOYER_TYPE', 'remote');
        $this->save();
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
