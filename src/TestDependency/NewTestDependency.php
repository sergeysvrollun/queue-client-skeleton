<?php


namespace TestDependency;


class NewTestDependency implements TestDependencyInterface
{

    public function __invoke()
    {
        return new self();
    }

    public function getText()
    {
        return 'Hello New World!';
    }
}