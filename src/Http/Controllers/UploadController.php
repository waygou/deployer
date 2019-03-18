<?php

namespace Waygou\Deployer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Waygou\Deployer\Abstracts\RemoteBaseController;
use Waygou\Deployer\Support\CodebaseRepository;
use Waygou\Deployer\Support\Remote;

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

        $repository = new CodebaseRepository(
            $request->input('transaction'),
            $request->input('runbook'),
            base64_decode($request->input('codebase'))
        );

        Remote::storeRepository($repository);

        return response_payload(true);
    }
}
