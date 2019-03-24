<?php

namespace Waygou\Deployer\Http\Controllers;

use Illuminate\Http\Request;
use Waygou\Deployer\Support\Remote;
use Illuminate\Support\Facades\Validator;
use Waygou\Deployer\Support\CodebaseRepository;
use Waygou\Deployer\Abstracts\RemoteBaseController;

class UploadController extends RemoteBaseController
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codebase'    => 'required',
            'transaction' => 'required',
            'runbook'     => 'required',
        ]);

        if ($validator->fails()) {
            return response_payload(false, ['message'=> $validator->errors()->first()], 201);
        }

        $repository = (new CodebaseRepository())
                          ->withCodebaseStream(base64_decode($request->input('codebase')))
                          ->withRunbook($request->input('runbook'))
                          ->withTransaction($request->input('transaction'));

        Remote::storeRepository($repository);

        return response_payload(true);
    }
}
