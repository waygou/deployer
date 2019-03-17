<?php

namespace Waygou\Deployer\Support;

use Chumper\Zipper\Facades\Zipper;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Waygou\Deployer\Exceptions\LocalException;
use Waygou\Deployer\Exceptions\ResponseException;
use Waygou\Deployer\Support\ReSTCaller;

class Local
{
    public static function __callStatic($method, $args)
    {
        return LocalOperation::new()->{$method}(...$args);
    }
}

class LocalOperation
{
    private $accessToken;
    protected $zipFilename;

    /**
     * Creates a zip file with the respective codebase configuration resources.
     * @return string The zip filename.
     */
    public function CreateCodebaseZip(string $transaction)
    {
        // **************** Testing purposes *************
        $file = new Filesystem;
        $file->cleanDirectory(deployer_storage_path());
        // ***********************************************

        $zip = Zipper::make(deployer_storage_path("{$transaction}.zip"));

        /*
         * Add the codebase files and folders.
         * Collection iterator to add each resource to the Zipper object.
         * Calculation on the folder path for the files iterator using the
         * pathinfo function.
         */
        collect(app('config')->get('deployer.codebase.folders'))->each(function ($item) use (&$zip) {
            $zip->folder($item)->add(base_path($item));
        });

        collect(app('config')->get('deployer.codebase.files'))->each(function ($item) use (&$zip) {
            $fileData = pathinfo($item);
            $zip->folder($fileData['dirname'])->add(base_path($item));
        });

        $zip->close();
    }

    public function uploadCodebase(string $transaction)
    {
        $response = ReSTCaller::asPost()
                              ->withHeader('Authorization', 'Bearer '.$this->accessToken->token)
                              ->withHeader('Accept', 'application/json')
                              ->withPayload(['deployer-token' => app('config')->get('deployer.token')])
                              ->withPayload(['transaction' => $transaction])
                              ->withPayload(['runbook' => json_encode(app('config')->get('deployer.scripts'))])
                              ->withPayload(['codebase' => base64_encode(file_get_contents(deployer_storage_path("{$transaction}.zip")))])
                              ->call(deployer_remote_url('upload'));

        $this->checkResponseAcknowledgement($response);
    }

    /**
     * The local environment pre-checklist actions correspond to:
     * - Attempt to create the deployer storage folder in case it doesn't exist.
     * - Verify if the storage directory is writeable.
     *
     * @return void
     */
    public function preChecks()
    {
        $storagePath = app('config')->get('deployer.storage.path');
        if (! is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        if (!is_writable($storagePath)) {
            throw new LocalException('Local storage directory not writeable');
        }
    }

    /**
     * Retrieves an access token from the remote server.
     * Populates private $accessToken with the access token credentials.
     * @return void
     */
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

    public function askRemoteForPreChecks()
    {
        $response = ReSTCaller::asPost()
                          ->withHeader('Authorization', 'Bearer '.$this->accessToken->token)
                          ->withHeader('Accept', 'application/json')
                          ->withPayload(['deployer-token' => app('config')->get('deployer.token')])
                          ->call(deployer_remote_url('prechecks'));

        $this->checkResponseAcknowledgement($response);
    }

    public function ping()
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
    private function checkResponseAcknowledgement(ResponsePayload $response)
    {
        if (! $response->isOk || data_get($response->payload->json, 'payload.result') != true) {
            throw new ResponseException($response);
        }
    }

    private function checkAccessToken(?ResponsePayload $response)
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
