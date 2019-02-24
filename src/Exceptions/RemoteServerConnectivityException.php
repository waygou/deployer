<?php

namespace Waygou\Deployer\Exceptions;

use Exception;

class RemoteServerConnectivityException extends Exception
{
    public function errorMessage()
    {
        return $this->getMessage();
    }
}
