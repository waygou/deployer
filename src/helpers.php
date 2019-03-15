<?php

use sixlive\DotenvEditor\DotenvEditor;

function ascii_title()
{
    /*
     * Credits to patorjk.com/software/taag/
     */
    return "
          _____             _
         |  __ \           | |
         | |  | | ___ _ __ | | ___  _   _  ___ _ __
         | |  | |/ _ \ '_ \| |/ _ \| | | |/ _ \ '__|
         | |__| |  __/ |_) | | (_) | |_| |  __/ |
         |_____/ \___| .__/|_|\___/ \__, |\___|_|
                     | |             __/ |
                     |_|            |___/
        ";
}

function capsule(bool $result, $message = null, $payload = null)
{
    $capsule = new stdClass();
    $capsule->ok = $result;
    $capsule->payload = $payload;
    $capsule->message = $message;

    return $capsule;
}

function deployer_remote_url($path)
{
    return app('config')->get('deployer.remote.url').
           deployer_url($path);
}

function deployer_url($url)
{
    return config('deployer.remote.prefix')."/{$url}";
}

function append_line_to_env(string $key, $value)
{
    return file_put_contents(base_path('.env'), PHP_EOL."{$key}={$value}", FILE_APPEND);
}

function response_payload($result, $payload = [])
{
    return response()->json([
        'payload' => array_merge(['result'=> $result], $payload),
    ]);
}

function deployer_storage_path($path)
{
    return app('config')->get('deployer.storage.path')."/{$path}";
}

function set_env(string $key, string $value)
{
    $env = new DotenvEditor;
    $env->load(base_path('.env'));
    //$env->unset($key);
    $env->set($key, $value);
    $env->save();
    unset($env);
}
