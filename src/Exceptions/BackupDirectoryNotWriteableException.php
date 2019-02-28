<?php

namespace Waygou\Deployer\Exceptions;

use Exception;

class BackupDirectoryNotWriteableException extends Exception
{
    public function errorMessage()
    {
        return __('deployer::exceptions.backup_directory_not_writeable');
    }
}
