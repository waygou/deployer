<?php

namespace Waygou\Deployer\Http\Controllers;

use Waygou\Deployer\Abstracts\RemoteBaseController;
use Waygou\Deployer\Support\Remote;

class PreChecksController extends RemoteBaseController
{
    public function __invoke()
    {
        Remote::preChecks();

        return response_payload(true);
    }
}
