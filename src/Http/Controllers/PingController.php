<?php

namespace Waygou\Deployer\Http\Controllers;

use App\Http\Controllers\Controller;

class PingController extends Controller
{
    public function __invoke()
    {
        $result = Remote::pingCheck();

        return response()->json([
            'payload' => ['result'=> true],
        ]);
    }
}
