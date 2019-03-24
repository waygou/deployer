<?php

namespace Waygou\Deployer\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Waygou\Deployer\Concerns\CanRunProcesses;
use Waygou\Deployer\Exceptions\RemoteException;

class Remote
{
    public static function __callStatic($method, $args)
    {
        return RemoteOperation::new()->{$method}(...$args);
    }
}

class RemoteOperation
{
    use CanRunProcesses;

    public static function new(...$args)
    {
        return new self(...$args);
    }

    public function runPostScripts(string $transaction)
    {
        if (Storage::disk('deployer')->exists("{$transaction}/runbook.json")) {
            $resource = json_decode(Storage::disk('deployer')->get("{$transaction}/runbook.json"));

            collect(data_get($resource, 'after_deployment'))->each(function ($item) use ($transaction) {
                $output = $this->runScript($item);

                if ($output !== null) {
                    Storage::disk('deployer')->append("{$transaction}/output_after.json", "Command: {$item}");
                    Storage::disk('deployer')->append("{$transaction}/output_after", 'Output:');
                    Storage::disk('deployer')->append("{$transaction}/output_after", "{$output}");
                }
            });
        }
    }

    public function runPreScripts(string $transaction)
    {
        if (Storage::disk('deployer')->exists("{$transaction}/runbook.json")) {
            $resource = json_decode(Storage::disk('deployer')->get("{$transaction}/runbook.json"));

            collect(data_get($resource, 'before_deployment'))->each(function ($item) use ($transaction) {
                $output = $this->runScript($item);

                if ($output !== null) {
                    Storage::disk('deployer')->append("{$transaction}/output_before.json", "Command: {$item}");
                    Storage::disk('deployer')->append("{$transaction}/output_before.json", 'Output:');
                    Storage::disk('deployer')->append("{$transaction}/output_before.json", "{$output}");
                }
            });
        }
    }

    public function preChecks()
    {
        $storagePath = app('config')->get('deployer.storage.path');
        if (! is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        return is_writable($storagePath) ?
            true : function () {
                throw new RemoteException('Local storage directory not writeable');
            };
    }

    public function storeRepository(CodebaseRepository $repository)
    {
        // rescue() used than try-catch statement just to use the exception->report() from Laravel!
        // https://laravel.com/docs/5.8/helpers#method-rescue
        rescue(function () use ($repository) {
            // Create a new transaction folder inside the deployer storage.
            Storage::disk('deployer')->makeDirectory($repository->transaction());

            // Store the runbook, and the zip codebase file.
            Storage::disk('deployer')->put("{$repository->transaction()}/codebase.zip", $repository->codebaseStream());
            Storage::disk('deployer')->put("{$repository->transaction()}/runbook.json", $repository->runbook());
        }, function () {
            throw new RemoteException('An  error occured whle trying to store your codebase in the remote environment');
        });
    }

    private function runScript($mixed)
    {
        // Invokable class.
        if (class_exists($mixed)) {
            return (new $mixed)();
        }

        // Custom method.
        if (strpos($mixed, '@')) {
            return app()->call($mixed);
        }

        // Artisan command.
        $error = Artisan::call($mixed);
        if ($error != 0) {
            throw new RemoteException('There was an error on your Artisan command (pre-script):'.Artisan::output());
        }

        return Artisan::output();
    }
}
