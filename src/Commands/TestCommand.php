<?php

namespace Waygou\Deployer\Commands;

use Waygou\Deployer\Support\TestClass;
use Illuminate\Support\Facades\Storage;
use Waygou\Deployer\Concerns\CanRunProcesses;
use Waygou\Deployer\Abstracts\DeployerInstallerBootstrap;

final class TestCommand extends DeployerInstallerBootstrap
{
    use CanRunProcesses;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploys your codebase content to your remote environment';

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
        parent::handle();

        //$mixed = 'route:list';
        //$mixed = TestClass::class; -- DONE
        $mixed = 'Waygou\Deployer\Support\TestClass@closeDown';
        //$mixed = 'TestClass@closeUp';

        //dd(app()->call($mixed));

        // Invokable class.
        if (class_exists($mixed)) {
            $result = (new $mixed)();
        }

        // Custom method.
        if (strpos($mixed, '@')) {
            $result = app()->call($mixed);
            dd($result);
        }

        // Artisan command
        // ...

        dd(class_exists($mixed));

        if (class_exists($mixed)) {
            /*
            $class = new $mixed;
            $result = $class();
            */
        }

        //dd(run_custom_script($mixed));

        /*
        if (Storage::disk('deployer')->exists('20190321-013043-TBUOU/runbook.json')) {
            $resource = json_decode(Storage::disk('deployer')->get('20190321-013043-TBUOU/runbook.json'));

            collect(data_get($resource, 'before_deployment'))->each(function ($item) {
                $this->runProcess($item);
            });
        }
        */

        $this->bulkInfo(2, '*** Good damn test! ***', 1);
    }
}
