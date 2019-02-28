<?php

namespace Waygou\Deployer;

use Waygou\Deployer\Exceptions\RemotePreChecksException;
use Waygou\Deployer\Exceptions\InvalidAccessTokenException;
use Waygou\Deployer\Exceptions\RemoteServerConnectivityException;
use Waygou\Deployer\Exceptions\BackupDirectoryNotWriteableException;

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

    /**
     * The pre-checks actions correspond to:
     * - Verify if the backups directory is writeable.
     * @return void
     */
    public function preChecks()
    {
        $backupPath = app('config')->get('deployer.codebase.backup_path');
        if ($backupPath) {
            @mkdir($backupPath, 0755, true);

            if (is_writable($backupPath)) {
                return capsule(true);
            }

            throw new BackupDirectoryNotWriteableException(__('deployer::exceptions.backup_directory_not_writeable'));
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
                              ->withPayload(['deployer-token' => app('config')->get('deployer.token')])
                              ->call(deployer_remote_url('predeployment-check'));

        dd($response);

        if (! $response->isOk) {
            throw new RemotePreChecksException(__('deployer::exceptions.remote_prechecks_failed'));
        }

        return $response->isOk &&
               data_get($response->payload->json, 'payload.result') == true;
    }

    public function ping()
    {
        $response = RESTCaller::asPost()
                              ->withHeader('Authorization', 'Bearer '.$this->accessToken->token)
                              ->withPayload(['deployer-token' => app('config')->get('deployer.token')])
                              ->call(deployer_remote_url('ping'));

        if (! $response->isOk) {
            throw new RemoteServerConnectivityException(__('deployer::exceptions.cannot_get_access_token'));
        }
    }

    private function checkAccessToken(?ResponsePayload $response)
    {
        if (! $response->isOk) {
            throw new InvalidAccessTokenException(__('deployer::exceptions.cannot_get_access_token'));
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
