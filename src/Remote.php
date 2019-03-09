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
    public function preChecks()
    {
        throw new RemoteException('Backup directory not writeable');

        $backupPath = app('config')->get('deployer.codebase.backup_path');
        if (filled($backupPath)) {
            @mkdir($backupPath, 0755, true);

            return is_writable($backupPath) ?
            true : function () {
                throw new RemoteException('Backup directory not writeable');
            };
        }
    }

    public static function new(...$args)
    {
        return new self(...$args);
    }
}
