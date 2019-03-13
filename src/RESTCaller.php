<?php

namespace Waygou\Deployer;

use Zttp\Zttp;
use Zttp\ZttpResponse;
use Zttp\ConnectionException;
use GuzzleHttp\Exception\RequestException;

class RESTCaller
{
    public static function __callStatic($method, $args)
    {
        return RequestPayload::new()->{$method}(...$args);
    }
}

class RequestPayload
{
    private const HTTP_VERB_GET = 'get';
    private const HTTP_VERB_POST = 'post';

    private $payload = [];
    private $headers = [];
    private $verb = self::HTTP_VERB_GET;
    private $accessToken = null;
    private $multiPart = false;

    public function __construct()
    {
    }

    public function withHeader(string $key, string $value)
    {
        $this->headers = array_merge($this->headers, [$key => $value]);

        return $this;
    }

    public function withPayload(array $payload)
    {
        $this->payload = array_merge($this->payload, $payload);

        return $this;
    }

    public function asPost()
    {
        $this->verb = self::HTTP_VERB_POST;

        return $this;
    }

    public function asMultiPart()
    {
        $this->multiPart = true;

        return $this;
    }

    public function asGet()
    {
        $this->verb = self::HTTP_VERB_GET;

        return $this;
    }

    public function call($url)
    {
        try {
            $response = Zttp::withHeaders($this->headers);

            if ($this->multiPart) {
                $response->asMultipart();
            }

            if ($url == 'http://localhost:8010/upload') {
                info($url, $this->payload);
            }

            $response = $response->{$this->verb}($url, $this->payload);
        } catch (ConnectionException | RequestException $e) {
            $exception = $e;
        }

        return new ResponsePayload($response ?? null, $exception ?? null);
    }

    public static function new(...$args)
    {
        return new self(...$args);
    }
}

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

        $this->isOk = $response->isOk();
        $this->instance = $response;
    }
}
