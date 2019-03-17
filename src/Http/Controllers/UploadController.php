<?php

namespace Waygou\Deployer\Http\Controllers;

use Waygou\Deployer\Remote;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UploadController extends Controller
{
    public function __invoke(Request $request)
    {
        if (! $request->has('codebase')) {
            return response_payload(false, ['message' => 'No codebase content']);
        }

        if (! $request->has('transaction')) {
            return response_payload(false, ['message' => 'No transaction code defined']);
        }

        Remote::saveCodebase($request->input('codebase'));

        return response_payload(true);
    }
}
