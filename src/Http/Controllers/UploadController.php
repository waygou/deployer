<?php

namespace Waygou\Deployer\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UploadController extends Controller
{
    public function __invoke(Request $request)
    {
        //dd($request->file('zip'));
        return response_payload(true);
    }
}
