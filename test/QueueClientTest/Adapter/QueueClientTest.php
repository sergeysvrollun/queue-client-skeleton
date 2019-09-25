<?php


namespace QueueClientTest\Adapter;


use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use ReputationVIP\QueueClient\Adapter\Exception\QueueAccessException;
use ReputationVIP\QueueClient\Exception\QueueAliasException;
use ReputationVIP\QueueClient\QueueClient;

class QueueClientTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    public $container;
    public $queueClient;

    public function getQueueClient(): QueueClient
    {
        return $this->container->get('Application\QueueClient');
    }

//    /**
//     * @param array $a
//     * @param array $b
//     *
//     * @return bool
//     */
//    public function isSimilarArrays(array $a, array $b): bool
//    {
//        if (count(array_diff_assoc($a, $b))) {
//            return false;
//        }
//        foreach ($a as $k => $v) {
//            if ($v !== $b[$k]) {
//                return false;
//            }
//        }
//        return true;
//    }

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
        $queueClient = $this->getQueueClient();
        $queueList = $queueClient->listQueues();
        foreach ($queueList as $queue) {
            $queueClient->deleteQueue($queue);
        }
    }

//    public function testCreateDeleteQueue()
//    {
//        $queueClient = $this->getQueueClient();
//        $this->assertCount(0, $queueClient->listQueues());
//        $queueClient->createQueue('testQueue');
//        $this->assertCount(1, $queueClient->listQueues());
//        $queueClient->createQueue('testQueue2');
//        $this->assertCount(2, $queueClient->listQueues());
//        $queueClient->deleteQueue('testQueue');
//        $this->assertCount(1, $queueClient->listQueues());
//        $queueClient->deleteQueue('testQueue2');
//        $this->assertCount(0, $queueClient->listQueues());
//    }

//    public function testAddRemoveMessage()
//    {
//        $queueClient = $this->getQueueClient();
//        $queueClient->createQueue('testQueue');
//        $this->assertTrue($queueClient->isEmpty('testQueue'));
//        $queueClient->addMessage('testQueue', 'testMessage');
//        $this->assertFalse($queueClient->isEmpty('testQueue'));
//        $queueClient->deleteMessage('testQueue', 'testMessage');
//        $this->assertTrue($queueClient->isEmpty('testQueue'));
//    }

    public function testQueueClientCreateQueueWithSpace()
    {
        $queueClient = $this->getQueueClient();
        $this->expectException(InvalidArgumentException::class);
        $queueClient->createQueue('test Queue One');
    }


//    public function testQueueClientAddMessageWithAlias(MemoryAdapter $adapter)
//    {
//        $this
//            ->given($this->newTestedInstance($adapter))
//            ->when(
//                $this->testedInstance->createQueue('testQueueOne'),
//                $this->testedInstance->createQueue('testQueueTwo'),
//                $this->testedInstance->addAlias('testQueueOne', 'queueAlias')
//            )
//            ->then
//            ->object($this->testedInstance->addMessage('queueAlias', 'testMessage'))->isTestedInstance()
//            ->when($this->testedInstance->addAlias('testQueueTwo', 'queueAlias'))
//            ->then
//            ->object($this->testedInstance->addMessage('queueAlias', 'testMessage'))->isTestedInstance()
//        ;
//    }


    public function testQueueClientAddMessageWithAlias()
    {
        $queueClient = $this->getQueueClient();
        $queueClient->createQueue('testQueueOne');
        $queueClient->createQueue('testQueueTwo');
        $queueClient->addAlias('testQueueOne', 'queueAlias');
        //$queueClient->addMessage('queueAlias', 'testMessage'); //isTestedInstance()
        $this->assertSame($queueClient, $queueClient->addMessage('queueAlias', 'testMessage'));
        $queueClient->addAlias('testQueueTwo', 'queueAlias');
        $this->assertSame($queueClient, $queueClient->addMessage('queueAlias', 'testMessage'));
        //$queueClient->addMessage('queueAlias', 'testMessage'); //isTestedInstance()
    }

    public function testQueueClientAddMessage()
    {
        $queueClient = $this->getQueueClient();

        $queueClient->createQueue('testQueue');
        $queueClient->addMessage('testQueue', 'testMessage');
        $this->assertSame($queueClient, $queueClient->addMessage('testQueue', 'testMessage')); //isTestedInstance()
    }

    public function testQueueClientAddMessages()
    {
        $queueClient = $this->getQueueClient();
        $queueClient->createQueue('testQueue');
        $this->assertSame($queueClient, $queueClient->addMessages('testQueue', ['testMessageOne', 'testMessageTwo', 'testMessageThree'])); //>isTestedInstance();
    }

    public function testQueueClientGetMessagesWithAlias()
    {
        $queueClient = $this->getQueueClient();
        $queueClient->createQueue('testQueueOne');
        $queueClient->createQueue('testQueueTwo');
        $queueClient->addAlias('testQueueOne', 'queueAlias');
        $queueClient->addAlias('testQueueTwo', 'queueAlias');

        $this->expectException(QueueAliasException::class);
        $queueClient->getMessages('queueAlias');
    }

    public function testQueueClientGetMessages()
    {
        $queueClient = $this->getQueueClient();
        $queueClient->createQueue('testQueue');
        $this->assertIsArray($queueClient->getMessages('testQueue'));
    }

    public function testQueueClientDeleteMessageWithAlias()
    {
        $queueClient = $this->getQueueClient();

        $queueClient->createQueue('testQueueOne');
        $queueClient->createQueue('testQueueTwo');
        $queueClient->addAlias('testQueueOne', 'queueAlias');
        $queueClient->addAlias('testQueueTwo', 'queueAlias');

        $this->expectException(QueueAliasException::class);
        $queueClient->deleteMessage('queueAlias', ['testMessage']); // exception
    }

    public function testQueueClientDeleteMessage()
    {
        $queueClient = $this->getQueueClient();

        $queueClient->createQueue('testQueue');
        $this->assertSame($queueClient, $queueClient->deleteMessage('testQueue', 'testMessage'));// isTestedInstance();
    }

    public function testQueueClientDeleteMessages()
    {
        $queueClient = $this->getQueueClient();

        $this->assertSame($queueClient, $queueClient->deleteMessages('testQueue', ['testMessageOne', 'testMessageTwo', 'testMessageThree'])); //isTestedInstance();
    }

    public function testQueueClientIsEmptyWithAlias()
    {
        $queueClient = $this->getQueueClient();

        $queueClient->createQueue('testQueueOne');
        $queueClient->createQueue('testQueueTwo');
        $queueClient->addAlias('testQueueOne', 'queueAlias');
        $queueClient->addAlias('testQueueTwo', 'queueAlias');

        $this->expectException(QueueAliasException::class);
        $queueClient->isEmpty('queueAlias');
    }

    public function testQueueClientIsEmpty()
    {
        $queueClient = $this->getQueueClient();
        $queueClient->createQueue('testQueue');
        $this->assertTrue($queueClient->isEmpty('testQueue'));
    }

    public function testQueueClientGetNumberMessageWithAlias()
    {
        $queueClient = $this->getQueueClient();
        $queueClient->createQueue('testQueueOne');
        $queueClient->createQueue('testQueueTwo');
        $queueClient->addAlias('testQueueOne', 'queueAlias');
        $queueClient->addAlias('testQueueTwo', 'queueAlias');

        $this->expectException(QueueAliasException::class);
        $queueClient->getNumberMessages('queueAlias');
    }

    public function testQueueClientNumberMessage()
    {
        $queueClient = $this->getQueueClient();
        $queueClient->createQueue('testQueue');
        $this->assertSame(0, $queueClient->getNumberMessages('testQueue'));
    }

    public function testQueueClientDeleteQueueWithAlias()
    {
        $queueClient = $this->getQueueClient();
        $queueClient->createQueue('testQueue');
        $queueClient->addAlias('testQueue', 'queueAliasOne');
        $queueClient->addAlias('testQueue', 'queueAliasTwo');
        $this->assertSame($queueClient, $queueClient->deleteQueue('testQueue')); // isTestedInstance();
        $this->assertEmpty($queueClient->getAliases());
    }

    public function testQueueClientDeleteQueue()
    {
        $queueClient = $this->getQueueClient();

        $this->assertSame($queueClient, $queueClient->deleteQueue('testQueue')); //isTestedInstance();
    }

    public function testQueueClientCreateQueue()
    {
        $queueClient = $this->getQueueClient();
        $this->assertSame($queueClient, $queueClient->createQueue('testQueue')); //isTestedInstance();
    }

    public function testQueueClientRenameQueueWithAlias()
    {
        $queueClient = $this->getQueueClient();
        $queueClient->createQueue('testQueue');
        $queueClient->addAlias('testQueue', 'queueAliasOne');
        $queueClient->addAlias('testQueue', 'queueAliasTwo');
        $this->assertSame($queueClient, $queueClient->renameQueue('testQueue', 'testRenameQueue')); //isTestedInstance();
        $alases = $queueClient->getAliases();
        $this->assertIsArray($alases);
        $this->assertSame(['queueAliasOne' => ['testRenameQueue'], 'queueAliasTwo' => ['testRenameQueue']], $alases);
    }

    public function testQueueClientRenameQueue()
    {
        $queueClient = $this->getQueueClient();

        $this->assertSame($queueClient, $queueClient->renameQueue('testQueue', 'testRenameQueue'));// isTestedInstance();
    }

    public function testQueueClientPurgeQueueWithAlias()
    {
        $queueClient = $this->getQueueClient();
        $queueClient->createQueue('testQueueOne');
        $queueClient->createQueue('testQueueTwo');
        $queueClient->addAlias('testQueueOne', 'queueAlias');
        $queueClient->addAlias('testQueueTwo', 'queueAlias');

        $this->expectException(QueueAliasException::class);
        $queueClient->purgeQueue('queueAlias');
    }

    public function testQueueClientPurgeQueue()
    {
        $queueClient = $this->getQueueClient();

        $queueClient->createQueue('testQueue');
        $this->assertSame($queueClient, $queueClient->purgeQueue('testQueue')); // isTestedInstance();
    }

    public function testQueueClientListQueue()
    {
        $queueClient = $this->getQueueClient();

        $queueClient->createQueue('testQueue');
        $queueClient->createQueue('testRegexQueue');
        $queueClient->createQueue('testQueueOne');
        $queueClient->createQueue('testRegexQueueTwo');
        $queueClient->createQueue('testQueueTwo');
        $this->assertEquals(['testQueue', 'testRegexQueue', 'testQueueOne', 'testRegexQueueTwo', 'testQueueTwo'], $queueClient->listQueues());
        $this->assertEquals(['testRegexQueue', 'testRegexQueueTwo'], $queueClient->listQueues('/.*Regex.*/'));
    }

    public function testQueueClientAddAliasWithEmptyAlias()
    {
        $queueClient = $this->getQueueClient();

        $queueClient->createQueue('testQueue');

        $this->expectException(QueueAliasException::class);
        $queueClient->addAlias('testQueue', '');
    }

    public function testQueueClientAddAliasWithEmptyQueueName()
    {
        $queueClient = $this->getQueueClient();

        $queueClient->createQueue('testQueue');

        $this->expectException(InvalidArgumentException::class);
        $queueClient->addAlias('', 'queueAlias');
    }

    public function testQueueClientAddAliasOnUndefinedQueue()
    {
        $queueClient = $this->getQueueClient();
        $this->expectException(QueueAccessException::class);
        $queueClient->addAlias('testQueue', 'queueAlias');
    }

    public function testQueueClientAddAlias()
    {
        $queueClient = $this->getQueueClient();

        $queueClient->createQueue('testQueueOne');
        $queueClient->createQueue('testQueueTwo');
        $this->assertSame($queueClient, $queueClient->addAlias('testQueueOne', 'queueAlias')); //->isTestedInstance();
        $this->assertSame($queueClient, $queueClient->addAlias('testQueueTwo', 'queueAlias')); //->isTestedInstance();
        $this->assertEquals(['queueAlias' => ['testQueueOne', 'testQueueTwo']], $queueClient->getAliases());
        //$this->assertArraySubset(['queueAlias' => ['testQueueOne', 'testQueueTwo']], $queueClient->getAliases());
    }

    public function testQueueClientRemoveAliasWithUndefinedAlias()
    {
        $queueClient = $this->getQueueClient();

        $this->expectException(QueueAliasException::class);
        $queueClient->RemoveAlias('queueAlias');
    }

    public function testQueueClientRemoveAlias()
    {
        $queueClient = $this->getQueueClient();

        $queueClient->createQueue('testQueueOne');
        $queueClient->createQueue('testQueueTwo');
        $queueClient->addAlias('testQueueOne', 'queueAliasOne');
        $queueClient->addAlias('testQueueTwo', 'queueAliasTwo');
        $this->assertSame($queueClient, $queueClient->removeAlias('queueAliasOne')); //->isTestedInstance();
        $this->assertIsArray($queueClient->getAliases());
        $this->assertEquals(['queueAliasTwo' => ['testQueueTwo']], $queueClient->getAliases());
        //$this->assertArraySubset(['queueAliasTwo' => ['testQueueTwo']], $queueClient->getAliases());

    }

    public function testQueueClientGetAliases()
    {
        $queueClient = $this->getQueueClient();

        $queueClient->createQueue('testQueueOne');
        $queueClient->createQueue('testQueueTwo');
        $queueClient->addAlias('testQueueOne', 'queueAliasOne');
        $queueClient->addAlias('testQueueTwo', 'queueAliasOne');
        $queueClient->addAlias('testQueueTwo', 'queueAliasTwo');
        $this->assertIsArray($queueClient->getAliases());
        $this->assertEquals(['queueAliasOne' => ['testQueueOne', 'testQueueTwo'], 'queueAliasTwo' => ['testQueueTwo']], $queueClient->getAliases());
        //$this->assertArraySubset(['queueAliasOne' => ['testQueueOne', 'testQueueTwo'], 'queueAliasTwo' => ['testQueueTwo']], $queueClient->getAliases());
    }

    public function testQueueClientGetPriorityHandler()
    {
        $queueClient = $this->getQueueClient();
        $this->assertInstanceOf('ReputationVIP\QueueClient\PriorityHandler\PriorityHandlerInterface', $queueClient->getPriorityHandler());
    }

}