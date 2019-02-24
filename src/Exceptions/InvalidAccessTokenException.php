<?php

namespace Waygou\Deployer\Exceptions;

use Exception;

class InvalidAccessTokenException extends Exception
{
    public function errorMessage()
    {
        return $this->getMessage();
    }
}
