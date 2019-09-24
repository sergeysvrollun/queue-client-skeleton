<?php


namespace TestDependency;


class TestDependency implements TestDependencyInterface
{

    public function __invoke()
    {
        return new self();
    }

    public function getText()
    {
        return 'Hello World!';
    }
}