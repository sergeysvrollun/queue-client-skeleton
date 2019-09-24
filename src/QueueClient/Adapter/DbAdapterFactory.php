<?php


namespace ReputationVIP\QueueClient\Adapter;

use Psr\Container\ContainerInterface;

class DbAdapterFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return DbAdapter
     */
    public function __invoke(ContainerInterface $container)
    {
        $db = $container->get('db');
        if ($container->has('QueueClient\PriorityHandler')) {
            $priorityHandler = $container->get('QueueClient\PriorityHandler');
        } else {
            $priorityHandler = null;
        }
        return new DbAdapter($db, $priorityHandler);
    }

}