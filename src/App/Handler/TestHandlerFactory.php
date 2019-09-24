<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TestDependency\TestDependencyInterface;

class TestHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $dependency = $container->get(TestDependencyInterface::class);
        //$db = $container->get('db');
        return new TestHandler($dependency);
    }
}
