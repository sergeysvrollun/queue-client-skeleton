<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use TestDependency\TestDependencyInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class TestHandler implements RequestHandlerInterface
{
    /**
     * @var TestDependencyInterface
     */
    private $dependency;

    public function __construct(TestDependencyInterface $dependency)
    {
        $this->dependency = $dependency;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $dependency = $this->getDependency();
        return new JsonResponse(['text' => $dependency->getText()]);
    }

    /**
     * @return TestDependencyInterface
     */
    public function getDependency()
    {
        return $this->dependency;
    }
}
