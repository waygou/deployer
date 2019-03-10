<?php

namespace Waygou\Deployer;

use Chumper\Zipper\Facades\Zipper;
use Illuminate\Support\Facades\File;
use Waygou\Deployer\Exceptions\LocalException;
use Waygou\Deployer\Exceptions\ResponseException;

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

    private function dua_get_files($path)
    {
        $dir_paths = [];
        foreach (glob($path.'/*', GLOB_ONLYDIR) as $filename) {
            $dir_paths[] = $filename;
            $a = glob("$filename/*", GLOB_ONLYDIR);
            if (is_array($a)) {
                $b = $this->dua_get_files("$filename/*");
                foreach ($b as $c) {
                    $dir_paths[] = $c;
                }
            }
        }

        return $dir_paths;
    }

    public function CreateCodebaseZip()
    {
        File::delete(base_path('backups/test.zip'));
        $files = glob(base_path('packages/waygou/deployer'));
        Zipper::make(base_path('backups/test.zip'))->folder('packages/waygou/deployer')->add($files)->close();

        dd('done.');

        // Check codebase content configuration.
        $folders = [];
        $files = collect(app('config')->get('deployer.codebase.folders'))->each(function ($item) use (&$folders) {
            $folders = array_merge($this->dua_get_files("{$item}"), $folders);
        });

        dd($folders);

        return $zipFilename;
    }

    /**
     * The pre-checks actions correspond to:
     * - Verify if the backup directory is writeable.
     * @return void
     */
    public function preChecks()
    {
        $backupPath = app('config')->get('deployer.codebase.backup_path');

        if ($backupPath) {
            @mkdir($backupPath, 0755, true);

            if (! is_writable($backupPath)) {
                throw new LocalException('Backup folder not writeable');
            }
        }
    }

    /**
     * Retrieves an access token from the remote server.
     * Populates private $accessToken with the access token credentials.
     * @return void
     */
    public function getAccessToken()
    {
        $response = RESTCaller::asPost()
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
        $response = RESTCaller::asPost()
                              ->withHeader('Authorization', 'Bearer '.$this->accessToken->token)
                              ->withHeader('Accept', 'application/json')
                              ->withPayload(['deployer-token' => app('config')->get('deployer.token')])
                              ->call(deployer_remote_url('prechecks'));

        $this->checkResponseAcknowledgement($response);
    }

    public function ping()
    {
        $response = RESTCaller::asPost()
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
