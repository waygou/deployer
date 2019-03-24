<?php

namespace Waygou\Deployer\Http\Controllers;

use Waygou\Deployer\Abstracts\RemoteBaseController;

class PostScriptsController extends RemoteBaseController
{
    public function __invoke()
    {
        return response_payload(true);
    }
}
