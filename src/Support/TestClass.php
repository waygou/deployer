<?php

namespace Waygou\Deployer\Support;

class TestClass
{

    function __invoke()
    {
        info('invokable of test class was called!');
        return 'this is a return from the __invoke!';
    }

    function closeDown()
    {
        info('CloseDown method of test class was called!');
        return 'this is a return from the closeDown!';
    }
}
