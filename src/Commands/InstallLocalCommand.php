<?php

namespace Waygou\Deployer\Commands;

use Waygou\Deployer\Abstracts\DeployerInstallerBootstrap;

class InstallLocalCommand extends DeployerInstallerBootstrap
{
    const ERROR_WRITE_PERMISSION = '.env file without writing permissions. Please check your .env file writing permissions. Aborting.';

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

    protected $description = 'Installs Deployer in your local development environment.';

    public function handle()
    {
        $this->showHero();
        $this->steps = 6;

        $fields = $this->validateOptions();
        if (! $fields->ok) {
            $this->error($fields->message);

            return;
        }

        $bar = $this->output->createProgressBar($this->steps);
        $bar->start();

        $this->token = $this->option('token');
        $this->registerToken();
        $bar->advance();

        $this->client = $this->option('client');
        $this->registerClient();
        $bar->advance();

        $this->secret = $this->option('secret');
        $this->registerSecret();
        $bar->advance();

        $this->publishDeployerResources();
        $bar->advance();

        $this->info('');
        $this->url = $this->askAndValidate(
            'What is your remote server url (E.g.: https://www.johnsmith.com) ?',
            'required|url'
        );
        $this->registerRemoteUrl();
        $bar->advance();

        $this->registerLocalType();
        $bar->advance();

        $this->clearConfigurationCache();
        $bar->finish();

        $this->showLastResumedInformation();
    }

    protected function showLastResumedInformation()
    {
        $this->bulkInfo(2, 'All good! Now you can deploy your codebase to your remote server!', 1);
        $this->info("Don't forget to update your deployer.php configuration file for the correct codebase files and directories that you want to upload.");
    }

    protected function registerRemoteUrl()
    {
        $this->bulkInfo(1, 'Registering Remote URL in your .env file...', 1);
        append_line_to_env('DEPLOYER_REMOTE_URL', $this->url) ?:
            $this->error(self::ERROR_WRITE_PERMISSION);
    }

    protected function registerLocalType()
    {
        $this->bulkInfo(2, 'Registering Deployer Local Type in your .env file...', 1);
        append_line_to_env('DEPLOYER_TYPE', 'local') ?:
            $this->error(self::ERROR_WRITE_PERMISSION);
    }

    protected function registerToken()
    {
        $this->bulkInfo(2, 'Registering remote token to your .env file...', 1);
        append_line_to_env('DEPLOYER_TOKEN', $this->token) ?:
            $this->error(self::ERROR_WRITE_PERMISSION);
    }

    protected function registerSecret()
    {
        $this->bulkInfo(2, 'Registering OAuth secret to your .env file...', 1);
        append_line_to_env('DEPLOYER_OAUTH_SECRET', $this->secret) ?:
            $this->error(self::ERROR_WRITE_PERMISSION);
    }

    protected function registerClient()
    {
        $this->bulkInfo(2, 'Registering Client id to your .env file...', 1);
        append_line_to_env('DEPLOYER_OAUTH_CLIENT', $this->client) ?:
            $this->error(self::ERROR_WRITE_PERMISSION);
    }
}
