<?php

declare(strict_types=1);

namespace App\Handler;

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
        $test1[] = $this->queueClient->isEmpty('test');
        $this->queueClient->addMessage('test', 'test');
        $test1[] = $this->queueClient->isEmpty('test');
        return new JsonResponse(['text' => print_r($test1, true)]);
    }
}
