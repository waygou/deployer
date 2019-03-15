<?php

namespace Waygou\Deployer\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UploadController extends Controller
{
    public function __invoke(Request $request)
    {
        if (! $request->has('codebase')) {
            return response_payload(false);
        }

        Remote::saveCodebase($request->input('codebase'));

        return response_payload(true);
    }
}
