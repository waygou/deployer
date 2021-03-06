<?php

namespace Waygou\Deployer\Support;

use Chumper\Zipper\Facades\Zipper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Waygou\Deployer\Exceptions\LocalException;
use Waygou\Deployer\Exceptions\ResponseException;

final class Local
{
    public static function __callStatic($method, $args)
    {
        return LocalOperation::new()->{$method}(...$args);
    }
}

final class LocalOperation
{
    private $accessToken;

    protected $zipFilename;

    public function createRepository(string $transaction) : void
    {
        // Create a new transaction folder inside the deployer storage.
        Storage::disk('deployer')->makeDirectory($transaction);

        // Create zip, and store it inside the transaction folder.
        $this->CreateCodebaseZip(deployer_storage_path("{$transaction}/codebase.zip"));

        // Store the runbook, and the zip codebase file.
        Storage::disk('deployer')->put(
            "{$transaction}/runbook.json",
            json_encode(app('config')->get('deployer.scripts'))
        );
    }

    public function runPostScripts(string $transaction) : void
    {
        $response = ReSTCaller::asPost()
                              ->withHeader('Authorization', 'Bearer '.$this->accessToken->token)
                              ->withHeader('Accept', 'application/json')
                              ->withPayload(['deployer-token' => app('config')->get('deployer.token')])
                              ->withPayload(['transaction' => $transaction])
                              ->call(deployer_remote_url('post-scripts'));

        $this->checkResponseAcknowledgement($response);
    }

    public function deploy(string $transaction) : void
    {
        $response = ReSTCaller::asPost()
                              ->withHeader('Authorization', 'Bearer '.$this->accessToken->token)
                              ->withHeader('Accept', 'application/json')
                              ->withPayload(['deployer-token' => app('config')->get('deployer.token')])
                              ->withPayload(['transaction' => $transaction])
                              ->call(deployer_remote_url('deploy'));

        $this->checkResponseAcknowledgement($response);
    }

    public function runPreScripts(string $transaction) : void
    {
        $response = ReSTCaller::asPost()
                              ->withHeader('Authorization', 'Bearer '.$this->accessToken->token)
                              ->withHeader('Accept', 'application/json')
                              ->withPayload(['deployer-token' => app('config')->get('deployer.token')])
                              ->withPayload(['transaction' => $transaction])
                              ->call(deployer_remote_url('pre-scripts'));

        $this->checkResponseAcknowledgement($response);
    }

    public function CreateCodebaseZip(string $fqfilename) : void
    {
        if (count(app('config')->get('deployer.codebase.folders')) == 0 && count(app('config')->get('deployer.codebase.files')) == 0) {
            throw new LocalException('No files or folders identified to upload. Please check your configuration file');
        }

        $zip = Zipper::make($fqfilename);

        /*
         * Add the codebase files and folders.
         * Collection iterator to add each resource to the Zipper object.
         * Calculation on the folder path for the files iterator using the
         * pathinfo function.
         */

        collect(app('config')->get('deployer.codebase.folders'))->each(function ($item) use (&$zip) {
            if (! blank($item)) {
                $zip->folder($item)->add(base_path($item));
            }
        });

        collect(app('config')->get('deployer.codebase.files'))->each(function ($item) use (&$zip) {
            if (! blank($item)) {
                $fileData = pathinfo($item);
                $zip->folder($fileData['dirname'])->add(base_path($item));
            }
        });

        $zip->close();
    }

    public function uploadCodebase(string $transaction) : void
    {
        $response = ReSTCaller::asPost()
                              ->withHeader('Authorization', 'Bearer '.$this->accessToken->token)
                              ->withHeader('Accept', 'application/json')
                              ->withPayload(['deployer-token' => app('config')->get('deployer.token')])
                              ->withPayload(['transaction' => $transaction])
                              ->withPayload(['runbook' => json_encode(app('config')->get('deployer.scripts'))])
                              ->withPayload(['codebase' => base64_encode(file_get_contents(deployer_storage_path("{$transaction}/codebase.zip")))])
                              ->call(deployer_remote_url('upload'));

        $this->checkResponseAcknowledgement($response);
    }

    public function preChecks() : void
    {
        $storagePath = app('config')->get('deployer.storage.path');
        if (! is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        if (! is_writable($storagePath)) {
            throw new LocalException('Local storage directory not writeable');
        }
    }

    public function getAccessToken()
    {
        $response = ReSTCaller::asPost()
                           ->withPayload(['grant_type'    => 'client_credentials',
                                          'client_id'     => app('config')->get('deployer.oauth.client'),
                                          'client_secret' => app('config')->get('deployer.oauth.secret'), ])
                           ->withHeader('Accept', 'application/json')
                           ->call(app('config')->get('deployer.remote.url').'/oauth/token');

        $this->checkAccessToken($response);

        $this->accessToken = new AccessToken(
            $response->payload->json['expires_in'],
            $response->payload->json['access_token']
        );

        return $this;
    }

    public function askRemoteForPreChecks() : void
    {
        $response = ReSTCaller::asPost()
                          ->withHeader('Authorization', 'Bearer '.$this->accessToken->token)
                          ->withHeader('Accept', 'application/json')
                          ->withPayload(['deployer-token' => app('config')->get('deployer.token')])
                          ->call(deployer_remote_url('prechecks'));

        $this->checkResponseAcknowledgement($response);
    }

    public function ping() : void
    {
        $response = ReSTCaller::asPost()
                          ->withHeader('Authorization', 'Bearer '.$this->accessToken->token)
                          ->withHeader('Accept', 'application/json')
                          ->withPayload(['deployer-token' => app('config')->get('deployer.token')])
                          ->call(deployer_remote_url('ping'));

        $this->checkResponseAcknowledgement($response);
    }

    /**
     * A response acknowledgement will always bring:
     * isOk = true
     * payload.result = true.
     * @param  ResponsePayload $response The response payload.
     * @return void
     */
    private function checkResponseAcknowledgement(ResponsePayload $response) : void
    {
        if (! $response->isOk || data_get($response->payload->json, 'payload.result') != true) {
            throw new ResponseException($response);
        }
    }

    private function checkAccessToken(?ResponsePayload $response) : void
    {
        if (! $response->isOk || data_get($response->payload->json, 'payload.access_token') != null) {
            throw new ResponseException($response);
        }
    }

    public static function new(...$args)
    {
        return new self(...$args);
    }
}

class AccessToken
{
    public $expiresIn = null;
    public $token = null;

    public function __construct(int $expiresIn, string $token)
    {
        list($this->expiresIn, $this->token) = [$expiresIn, $token];
    }
}
