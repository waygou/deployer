<?php

namespace Waygou\Deployer\Commands;

use Waygou\Deployer\Local;
use Illuminate\Console\Command;
use Waygou\Deployer\Concerns\SimplifiesConsoleOutput;

class PingCommand extends Command
{
    use SimplifiesConsoleOutput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deployer:ping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pings your remote server to check connectivity status.';

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
        $this->bulkInfo(2, 'Checking remote server url connectivity...', 1);

        $response = Local::getAccessToken()
             ->ping();

        if ($response) {
            $this->info('All good! Deployer was able to reach your remote server and successfully test an OAuth connection!');
        } else {
            $this->error('Ups! Looks like Deployer is unable to reach your server and/or to create an OAuth connection.');
        }
    }
}
