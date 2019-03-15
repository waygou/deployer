<?php

namespace Waygou\Deployer\Concerns;

trait SharedInstallerActions
{
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

    protected function clearConfigurationCache()
    {
        $this->bulkInfo(2, 'Cleaning Configuration cache...', 1);
        $this->runProcess('php artisan configuration:clear --quiet', getcwd());
    }

    protected function unsetEnvData()
    {
        $env = $this->dotEnvInstance();
        $env->unset('DEPLOYER_TYPE');
        $env->unset('DEPLOYER_TOKEN');
        $env->unset('DEPLOYER_REMOTE_URL');
        $env->unset('DEPLOYER_OAUTH_CLIENT');
        $env->unset('DEPLOYER_OAUTH_SECRET');
        $env->save();
    }
}
