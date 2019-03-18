<?php

namespace Waygou\Deployer\Abstracts;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

abstract class RemoteBaseController extends Controller
{
    function __destruct()
    {
        // Disable any active token for the deployer client.
        DB::table('oauth_access_tokens')
          ->where('client_id', app('config')->get('deployer.oauth.client'))
          ->update(['revoked' => true]);
    }
}
