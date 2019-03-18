<?php

namespace Waygou\Deployer\Http\Controllers;

use Illuminate\Http\Request;
use Waygou\Deployer\Support\Remote;
use Illuminate\Support\Facades\Validator;
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

        /*
         * The codebase repository is a folder that is created with the transaction name.
         *
         */

        Remote::storeCodebaseRepository();

        return response_payload(true);
    }
}
