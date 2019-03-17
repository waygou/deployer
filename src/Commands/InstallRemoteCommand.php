<?php

namespace Waygou\Deployer\Commands;

use Laravel\Passport\Client;
use Illuminate\Support\Facades\DB;
use sixlive\DotenvEditor\DotenvEditor;
use Waygou\Deployer\Abstracts\DeployerInstallerBootstrap;

class InstallRemoteCommand extends DeployerInstallerBootstrap
{
    private $client;

    private $secret;

    protected $signature = 'deployer:install-remote';

    protected $description = 'Installs Deployer on your remote Environment';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        parent::handle();

        $this->steps = 5;

        // Laravel Passport installed?
        if (! is_dir(base_path('vendor/laravel/passport'))) {
            $this->steps++;
        }

        $this->bulkInfo(2, 'Installing Deployer on a REMOTE environment...', 1);
        $bar = $this->output->createProgressBar($this->steps);
        $bar->start();

        // In case of a re-installation, delete all the .env deployer data.
        $this->bulkInfo(2, 'Cleaning old .env deployer keys (if they exist)...', 1);
        $this->unsetEnvData();
        $bar->advance();

        if (! is_dir(base_path('vendor/laravel/passport'))) {
            $this->installLaravelPassport();
        }

        $this->publishDeployerResources();
        $bar->advance();

        $this->installClientCredentialsGrant();
        $this->getClientCredentialsGrant();
        $bar->advance();

        $this->registerEnvKeys();
        $bar->advance();

        $this->clearConfigurationCache();
        $bar->finish();

        $this->showLocalInstallInformation();
    }

    protected function registerEnvKeys()
    {
        $this->bulkInfo(2, 'Registering .env keys...', 1);

        $editor = new DotenvEditor;
        $editor->load(base_path('.env'));
        $editor->set('DEPLOYER_TYPE', 'remote');
        $editor->set('DEPLOYER_OAUTH_CLIENT', $this->client);
        $editor->set('DEPLOYER_OAUTH_SECRET', $this->secret);

        $this->token = strtoupper(str_random(10));

        $editor->set('DEPLOYER_TOKEN', $this->token);
        $editor->save();
    }

    protected function showLocalInstallInformation()
    {
        $this->bulkInfo(2, 'ALL DONE!', 0);
        $this->bulkInfo(1, 'Please install Deployer on your local Laravel app and run the following artisan command:', 1);
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
        $this->bulkInfo(2, 'Installing Laravel Password client credentials grant...', 1);
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
}
