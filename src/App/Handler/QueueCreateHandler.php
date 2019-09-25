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
        $queueList = $this->queueClient->listQueues();
        foreach ($queueList as $queue) {
            $this->queueClient->deleteQueue($queue);
        }
        $this->queueClient->createQueue('test');
        $this->queueClient->addMessage('test', 'test');
        $this->queueClient->addMessage('test', 'test2');
        $this->queueClient->addMessage('test', 'test2333');
        $messages = $this->queueClient->getMessages('test', 1);
        var_dump($messages); die;
//        $message = array_pop($messages);
//        $this->queueClient->deleteMessage('test', $message);
//        /**
//         * @var ArrayObject[]
//         */
//        foreach ($messages as $message) {
//            print_r($message->id); die;
//        }
        return new JsonResponse(['text' => print_r($messages, true)]);
    }
}
