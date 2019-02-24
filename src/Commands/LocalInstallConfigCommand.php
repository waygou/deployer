<?php

namespace Waygou\Deployer\Commands;

use Illuminate\Console\Command;
use Waygou\Deployer\Concerns\SimplifiesConsoleOutput;

class LocalInstallConfigCommand extends Command
{
    use SimplifiesConsoleOutput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deployer:local-install-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shows the command you should run on your local environment given the remote server deployer settings.';

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
        $this->bulkInfo(2, 'Please install Deployer on your local environment using this command line:', 1);

        $client = app('config')->get('deployer.oauth.client');
        $secret = app('config')->get('deployer.oauth.secret');
        $token = app('config')->get('deployer.token');
        $this->info("php artisan deployer:install-local --client={$client} --secret={$secret} --token={$token}");
    }
}
