<?php

namespace Waygou\Deployer\Support;

class TestClass
{
    public function __invoke()
    {
        info('invokable of test class was called!');

        return 'this is a return from the __invoke!';
    }

    public function closeDown()
    {
        info('CloseDown method of test class was called!');

        return 'this is a return from the closeDown!';
    }
}
