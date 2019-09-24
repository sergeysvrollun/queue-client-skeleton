<?php


namespace ReputationVIP\QueueClient;

use Psr\Container\ContainerInterface;

class QueueClientFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return QueueClientInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        $adapter = $container->get('Application\QueueAdapter');
        return new QueueClient($adapter);
    }

}