<?php

namespace Waygou\Deployer;

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
            mkdir($backupPath, 0755, true);
        }

        return is_writable($storagePath) ?
            true : function () {
                throw new RemoteException('Local storage directory not writeable');
            };
    }
}
