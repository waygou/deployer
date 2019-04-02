<?php

namespace Waygou\Deployer\Concerns;

use sixlive\DotenvEditor\DotenvEditor;

trait SharedInstallerActions
{
    protected function publishDeployerResources()
    {
        $this->bulkInfo(2, 'Publishing Deployer resources...', 1);
        $this->runProcess('php artisan vendor:publish --provider="Waygou\Deployer\DeployerServiceProvider" --force --quiet');
    }

    protected function clearConfigurationCache()
    {
        $this->bulkInfo(2, 'Cleaning Configuration cache...', 1);
        $this->runProcess('php artisan config:clear --quiet', getcwd());
    }

    protected function unsetEnvData()
    {
        $env = new DotenvEditor;
        $env->load(base_path('.env'));
        $env->unset('DEPLOYER_TYPE');
        $env->unset('DEPLOYER_TOKEN');
        $env->unset('DEPLOYER_REMOTE_URL');
        $env->unset('DEPLOYER_OAUTH_CLIENT');
        $env->unset('DEPLOYER_OAUTH_SECRET');
        $env->save();
        unset($env);
    }

    protected function gracefullyExit()
    {
        $message = $this->exception->getMessage() ?? 'Ups. Looks like this step failed. Please check your Laravel logs for more information';

        $this->error("An error occurred! => $message");
        exit();
    }
}
