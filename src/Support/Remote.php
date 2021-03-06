<?php

namespace Waygou\Deployer\Support;

use Chumper\Zipper\Facades\Zipper;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Waygou\Deployer\Concerns\CanRunProcesses;
use Waygou\Deployer\Exceptions\RemoteException;

final class Remote
{
    public static function __callStatic($method, $args)
    {
        return RemoteOperation::new()->{$method}(...$args);
    }
}

final class RemoteOperation
{
    use CanRunProcesses;

    const PREDEPLOYMENT = 'before';
    const POSTDEPLOYMENT = 'after';

    public static function new(...$args)
    {
        return new self(...$args);
    }

    public function unzipCodebase(string $transaction) : void
    {
        if (Storage::disk('deployer')->exists("{$transaction}/codebase.zip")) {
            Zipper::make(deployer_storage_path("{$transaction}/codebase.zip"))->extractTo(base_path(), app('config')->get('deployer.codebase.blacklist'), 2);
        }
    }

    private function runScripts(string $type, string $transaction) : void
    {
        if (Storage::disk('deployer')->exists("{$transaction}/runbook.json")) {
            $resource = json_decode(Storage::disk('deployer')->get("{$transaction}/runbook.json"));

            collect(data_get($resource, "{$type}_deployment"))->each(function ($item) use ($transaction, $type) {
                $output = $this->runScript($item);

                if ($output !== null) {
                    Storage::disk('deployer')->append("{$transaction}/output_{$type}.json", "Command: {$item}");
                    Storage::disk('deployer')->append("{$transaction}/output_{$type}.json", 'Output:');
                    Storage::disk('deployer')->append("{$transaction}/output_{$type}.json", "{$output}");
                }
            });
        }
    }

    public function runPostScripts(string $transaction) : void
    {
        $this->runScripts(self::POSTDEPLOYMENT, $transaction);
    }

    public function runPreScripts(string $transaction) : void
    {
        $this->runScripts(self::PREDEPLOYMENT, $transaction);
    }

    public function preChecks() : void
    {
        $storagePath = app('config')->get('deployer.storage.path');
        if (! is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        if (! is_writable($storagePath)) {
            throw new RemoteException('Local storage directory not writeable');
        }
    }

    public function storeRepository(CodebaseRepository $repository) : void
    {
        deployer_rescue(function () use ($repository) {
            // Create a new transaction folder inside the deployer storage.
            Storage::disk('deployer')->makeDirectory($repository->transaction());

            // Store the runbook, and the zip codebase file.
            Storage::disk('deployer')->put("{$repository->transaction()}/codebase.zip", $repository->codebaseStream());
            Storage::disk('deployer')->put("{$repository->transaction()}/runbook.json", $repository->runbook());
        }, function ($exception) {
            throw new RemoteException($exception->getMessage());
        });
    }

    private function runScript($mixed) : string
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
