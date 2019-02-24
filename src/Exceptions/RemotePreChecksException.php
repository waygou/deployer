<?php

namespace Waygou\Deployer\Exceptions;

use Exception;

class RemotePreChecksException extends Exception
{
    public function errorMessage()
    {
        return $this->getMessage();
    }
}
