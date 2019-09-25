<?php

declare(strict_types=1);

namespace App\Handler;

use ArrayObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReputationVIP\QueueClient\QueueClientInterface;
use Zend\Diactoros\Response\JsonResponse;
use TestDependency\TestDependencyInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class QueueCreateHandler implements RequestHandlerInterface
{
    /**
     * @var QueueClientInterface
     */
    private $queueClient;

    public function __construct(QueueClientInterface $queueClient)
    {
        $this->queueClient = $queueClient;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queueClient = $this->queueClient;
        $queueList = $this->queueClient->listQueues();
        foreach ($queueList as $queue) {
            $this->queueClient->deleteQueue($queue);
        }
//        $this->queueClient->createQueue('test');
//        $this->queueClient->addMessage('test', 'test');
//        $this->queueClient->addMessage('test', 'test2');
//        $this->queueClient->addMessage('test', 'test2333');
//        $messages = $this->queueClient->getMessages('test', 1);
//        var_dump($messages); die;


//        $queueClient->createQueue('testQueue');
//        $queueClient->createQueue('testRegexQueue');
//        $queueClient->createQueue('testQueueOne');
//        $queueClient->createQueue('testRegexQueueTwo');
//        $queueClient->createQueue('testQueueTwo');
//        print_r([['testQueue', 'testRegexQueue', 'testQueueOne', 'testRegexQueueTwo', 'testQueueTwo'], $queueClient->listQueues()]);
//        print_r([['testRegexQueue', 'testRegexQueueTwo'], $queueClient->listQueues('/.*Regex.*/')]);
//        die;


//        $queueClient->createQueue('testQueueOne');
//        $queueClient->createQueue('testQueueTwo');
//        $queueClient->addAlias('testQueueOne', 'queueAlias');
//        $queueClient->addAlias('testQueueTwo', 'queueAlias');
//        print_r([['queueAlias' => ['testQueueOne', 'testQueueTwo']], $queueClient->getAliases()]);
//        die;



        $queueClient->createQueue('testQueueOne');
        $queueClient->createQueue('testQueueTwo');
        $queueClient->addAlias('testQueueOne', 'queueAliasOne');
        $queueClient->addAlias('testQueueTwo', 'queueAliasOne');
        $queueClient->addAlias('testQueueTwo', 'queueAliasTwo');
        print_r([
            ['queueAliasOne' => ['testQueueOne', 'testQueueTwo'], 'queueAliasTwo' => ['testQueueTwo']],
            $queueClient->getAliases()
        ]);
        die;


        return new JsonResponse(['text' => print_r([], true)]);
    }
}
