<?php


namespace QueueClientTest\Adapter;


use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use ReputationVIP\QueueClient\Adapter\DbAdapter;
use ReputationVIP\QueueClient\QueueClient;
use ReputationVIP\QueueClient\QueueClientFactory;

class QueueClientTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    public $container;
    public $queueClient;

    public function getQueueClient(): QueueClient
    {
        return $this->container->get('Application\QueueClient');
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        /** @var ContainerInterface $container */
        global $container;
        $this->container = $container;
        $queueClient = $this->getQueueClient();
        $queueList = $queueClient->listQueues();
        foreach ($queueList as $queue) {
            $queueClient->deleteQueue($queue);
        }
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
//        $queueClient = $this->getQueueClient();
//        $queueList = $queueClient->listQueues();
//        foreach ($queueList as $queue) {
//            $queueClient->deleteQueue($queue);
//        }
    }

    public function testCreateDeleteQueue()
    {
        $queueClient = $this->getQueueClient();
        $this->assertCount(0, $queueClient->listQueues());
        $queueClient->createQueue('testQueue');
        $this->assertCount(1, $queueClient->listQueues());
        $queueClient->createQueue('testQueue2');
        $this->assertCount(2, $queueClient->listQueues());
        $queueClient->deleteQueue('testQueue');
        $this->assertCount(1, $queueClient->listQueues());
        $queueClient->deleteQueue('testQueue2');
        $this->assertCount(0, $queueClient->listQueues());
   }

    public function testAddRemoveMessage()
    {
        $queueClient = $this->getQueueClient();
        $queueClient->createQueue('testQueue');
        $this->assertTrue($queueClient->isEmpty('testQueue'));
        $queueClient->addMessage('testQueue', 'testMessage');
        $this->assertFalse($queueClient->isEmpty('testQueue'));
//        $queueClient->deleteMessage('testQueue', 'testMessage');
//        $this->assertTrue($queueClient->isEmpty('testQueue'));
    }

}