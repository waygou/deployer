<?php

namespace Waygou\Deployer\Exceptions;

use Exception;

class BackupDirectoryNotWriteableException extends Exception
{
    public function errorMessage()
    {
        return $this->getMessage();
    }
}
