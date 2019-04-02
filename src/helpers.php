<?php


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

function response_payload($result, $payload = [], $statusCode = 200)
{
    $data = ['payload' => array_merge(['result'=> $result], $payload)];

    return response(json_encode($data), $statusCode);
}

function deployer_storage_path($path = null)
{
    return app('config')->get('deployer.storage.path')."/{$path}";
}

function generate_transaction_code()
{
    return date('Ymd-His').'-'.strtoupper(str_random(5));
}

function deployer_rescue(callable $callback, $rescue = null)
{
    try {
        return $callback();
    } catch (Throwable $e) {
        report($e);
        return $rescue($e);
    }
}
