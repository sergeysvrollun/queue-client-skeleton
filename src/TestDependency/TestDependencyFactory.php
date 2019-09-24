<?php

namespace TestDependency;

class TestDependencyFactory
{

    /**
     * @param            $container
     * @param            $requestedName
     * @param array|null $options
     *
     * @return TestDependencyInterface
     */
    public function __invoke($container, $requestedName, array $options = null)
    {
        return new NewTestDependency();
    }
}