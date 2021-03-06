<?php

namespace Waygou\Deployer\Http\Controllers;

use Illuminate\Http\Request;
use Waygou\Deployer\Support\Remote;
use Illuminate\Support\Facades\Validator;
use Waygou\Deployer\Abstracts\RemoteBaseController;

final class PreScriptsController extends RemoteBaseController
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction' => 'required',
        ]);

        if ($validator->fails()) {
            return response_payload(false, ['message'=> $validator->errors()->first()], 201);
        }

        Remote::runPreScripts($request->input('transaction'));

        return response_payload(true);
    }
}
