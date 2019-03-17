<?php

namespace Waygou\Deployer\Support;

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

    /**
     * Saves a codebase package into the deployer storage folder.
     * For each new codebase upload, if.
     * @param  string $codebase Codebase package, base64 encoded.
     * @return void
     */
    public function saveCodebase($codebase)
    {
        $stream = base64_decode($codebase);
    }
}
