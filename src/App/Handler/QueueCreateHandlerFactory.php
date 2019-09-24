<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TestDependency\TestDependencyInterface;

class QueueCreateHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $queueClient = $container->get('Application\QueueClient');
        return new QueueCreateHandler($queueClient);
    }
}
