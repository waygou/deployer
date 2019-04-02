<?php

namespace Waygou\Deployer\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

final class CodebaseRepositoryException extends Exception
{
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
        parent::__construct($this->message);
    }

    public function report()
    {
        Log::error($this->message);
    }
}
