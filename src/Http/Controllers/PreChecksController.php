<?php

namespace Waygou\Deployer\Http\Controllers;

use Waygou\Deployer\Support\Remote;
use Waygou\Deployer\Abstracts\RemoteBaseController;

final class PreChecksController extends RemoteBaseController
{
    public function __invoke()
    {
        Remote::preChecks();

        return response_payload(true);
    }
}
