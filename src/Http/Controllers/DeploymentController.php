<?php

namespace Waygou\Deployer\Http\Controllers;

use Waygou\Deployer\Abstracts\RemoteBaseController;

class DeploymentController extends RemoteBaseController
{
    public function __invoke()
    {
        return response_payload(true);
    }
}
