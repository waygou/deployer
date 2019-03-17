<?php

namespace Waygou\Deployer\Commands;

use sixlive\DotenvEditor\DotenvEditor;
use Waygou\Deployer\Abstracts\DeployerInstallerBootstrap;

class InstallLocalCommand extends DeployerInstallerBootstrap
{
    protected $messages = [
        'client.required' => 'The --client option is required.',
        'client.integer'  => 'The --client option needs to be an integer.',
        'secret.required' => 'The --secret option is required.',
        'token.required'  => 'The --token option is required.',
    ];

    protected $signature = 'deployer:install-local
                            {--client= : Your OAuth Laravel Passport Client id}
                            {--secret= : Your OAuth Secret}
                            {--token= : The Remote server token, must be the same}';

    protected $description = 'Installs Deployer in your local environment';

    public function handle()
    {
        parent::handle();

        $this->steps = 6;

        $fields = $this->validateOptions();
        if (! $fields->ok) {
            $this->error($fields->message);

            return;
        }

        $bar = $this->output->createProgressBar($this->steps);
        $bar->start();

        // In case of a re-installation, delete all the .env deployer data.
        $this->bulkInfo(2, 'Cleaning old .env deployer keys (if they exist)...', 1);
        $this->unsetEnvData();
        $bar->advance();

        $this->info('');
        $this->url = $this->askAndValidate(
            'What is your remote server url (E.g.: https://www.johnsmith.com) ?',
            'required|url'
        );

        $this->setEnvData();
        $bar->advance();

        $this->publishDeployerResources();
        $bar->advance();

        $this->clearConfigurationCache();
        $bar->finish();

        $this->showLastResumedInformation();
    }

    protected function setEnvData()
    {
        $this->bulkInfo(0, 'Setting .env variables...', 1);

        $env = new DotenvEditor;
        $env->load(base_path('.env'));
        $env->set('DEPLOYER_TYPE', 'local');
        $env->set('DEPLOYER_TOKEN', $this->option('token'));
        $env->set('DEPLOYER_OAUTH_CLIENT', $this->option('client'));
        $env->set('DEPLOYER_OAUTH_SECRET', $this->option('secret'));
        $env->set('DEPLOYER_REMOTE_URL', $this->url);
        $env->save();
        unset($env);
    }

    protected function showLastResumedInformation()
    {
        $this->bulkInfo(2, 'All good! Now you can deploy your codebase to your remote server!', 1);
        $this->info("Don't forget to update your deployer.php configuration file for the correct codebase files and directories that you want to upload.");
    }
}
