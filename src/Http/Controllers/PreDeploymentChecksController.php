<?php

namespace Waygou\Deployer\Http\Controllers;

use App\Http\Controllers\Controller;

class PreDeploymentChecksController extends Controller
{
    public function __invoke()
    {
        $result = Remote::preChecks();

        return response()->json([
            'payload' => ['result'=> true],
        ]);
    }
}
