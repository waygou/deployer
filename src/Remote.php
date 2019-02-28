<?php

namespace Waygou\Deployer;

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
        $backupPath = app('config')->get('deployer.codebase.backup_path');
        if (filled($backupPath)) {
            @mkdir($backupPath, 0755, true);

            return is_writable($backupPath) ?
            true : function () {
                throw new BackupDirectoryNotWriteable;
            };
        }
    }

    public static function new(...$args)
    {
        return new self(...$args);
    }
}
