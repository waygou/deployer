<?php

namespace Waygou\Deployer\Support;

use Zttp\ZttpResponse;

class ResponsePayload
{
    public $isOk = false;
    public $instance = null;
    public $exception = null;

    public function __construct(ZttpResponse $response = null, ?\Exception $exception)
    {
        if (isset($exception)) {
            $this->exception = new \StdClass;
            $this->exception->instance = $exception;
            $this->exception->message = $exception->getMessage();
            $this->exception->code = $exception->getCode();
        }

        if (is_null($response)) {
            return $this;
        }

        $this->payload = new \StdClass;
        $this->payload->raw = $response->body();
        $this->payload->json = $response->json();

        $this->isOk = $response->isOk() && $response->status() == 200;
        $this->instance = $response;
    }
}
