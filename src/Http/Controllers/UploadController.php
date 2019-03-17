<?php

namespace Waygou\Deployer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Waygou\Deployer\Support\Remote;

class UploadController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codebase'    => 'required',
            'transaction' => 'required',
            'runbook'     => 'required'
        ]);

        if ($validator->fails()) {
            return response_payload(false, ['message'=> $validator->errors()->first()], 201);
        }

        $codebaseRepo =

        Remote::storeCodebaseRepository();

        return response_payload(true);
    }
}
