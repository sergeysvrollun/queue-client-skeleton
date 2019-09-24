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
        return new JsonResponse(['text' => "test"]);
    }
}
