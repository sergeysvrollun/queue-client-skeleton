<?php


namespace QueueClintTest\Adapter;

use PHPUnit\Framework\TestCase;
use ReputationVIP\QueueClient\Adapter\DbAdapterFactory;

class DbAdapterFactoryTest extends TestCase
{
    public function testFactory()
    {
        $factory = new DbAdapterFactory();
        $this->assertInstanceOf(DbAdapterFactory::class, $factory);
    }

}