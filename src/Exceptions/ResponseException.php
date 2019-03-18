<?php

namespace Waygou\Deployer\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;
use Waygou\Deployer\Support\ResponsePayload;

class ResponseException extends Exception
{
    public $response;
    public $reason;
    public $status;
    public $message;

    public function __construct(ResponsePayload $response)
    {

        dd('I am here', $response);

        $this->response = $response;
        $this->message = 'Unknown ResponsePayload exception. Sorry about that.';

        if (isset($response->instance)) {
            $this->reason = $response->instance->getReasonPhrase();
            $this->status = $response->instance->getStatusCode();
        }

        if (isset($response->exception)) {
            $this->reason = $response->exception->message;
            $this->status = null;
            $this->message = $response->exception->message;
        }

        if (isset($response->payload)) {
            $this->message = data_get($response->payload->json, 'payload.message') ??
                             data_get($response->payload->json, 'exception');
        }

        parent::__construct($this->message);
    }

    public function report()
    {
        Log::error($this->message());
    }

    // Needs to compute the best readable message from the available error data.
    public function message()
    {
        /*
         * HTTP xxx
         * isOk = false
         * instance.status
         * instance.reasonPhrase
         * json.message
         * json.exception
         * json.file
         * json.line
         */

        return "Deployer Response Exception
                HTTP {$this->status} {$this->reason}
                {$this->message}";
    }
}
