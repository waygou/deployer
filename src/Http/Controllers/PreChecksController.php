<?php

namespace Waygou\Deployer\Http\Controllers;

use Waygou\Deployer\Remote;
use App\Http\Controllers\Controller;

class PreChecksController extends Controller
{
    public function __invoke()
    {
        Remote::preChecks();

        return response_payload(true);
    }
}
