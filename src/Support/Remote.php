<?php

namespace Waygou\Deployer\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Waygou\Deployer\Exceptions\RemoteException;
use Waygou\Deployer\Support\CodebaseRepository;

class Remote
{
    public static function __callStatic($method, $args)
    {
        return RemoteOperation::new()->{$method}(...$args);
    }
}

class RemoteOperation
{
    public static function new(...$args)
    {
        return new self(...$args);
    }

    /**
     * The pre-checks actions correspond to:
     * - Verify if the backup directory is writeable.
     * @return void
     */
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
        // Create a new transaction folder inside the deployer storage.
        Storage::disk('deployer')->makeDirectory($repository->transaction());

        // Add the runbook, and the zip codebase file.
        Storage::disk('deployer')->put("{$repository->transaction()}/codebase.zip", $repository->codebaseStream());
        Storage::disk('deployer')->put("{$repository->transaction()}/runbook.json", $repository->runbook());
    }
}
