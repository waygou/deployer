<?php

namespace Waygou\Deployer\Contracts;

interface Localable
{
    public function checkRemote(): bool;
}
