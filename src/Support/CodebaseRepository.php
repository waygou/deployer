<?php

namespace Waygou\Deployer\Support;

class CodebaseRepository
{
    private $transaction;
    private $codebase;
    private $runbook;

    public function __construct(string $transaction, string $runbook, string $codebase)
    {
        list($this->transaction, $this->runbook, $this->codebase) = [$transaction, $runbook, $codebase];
    }

    public function transaction()
    {
        return $this->transaction;
    }
}
