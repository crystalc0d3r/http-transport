<?php

declare(strict_types=1);

namespace Crystalc0d3r\HttpTransport;

use Crystalc0d3r\HttpTransport\Contracts\HttpTransport;
use Crystalc0d3r\HttpTransport\Contracts\Middleware;
use Psr\Http\Message\RequestInterface;

class Pipeline implements HttpTransport
{
    private ?\Closure $pipeline = null;

    public function __construct(
        private HttpTransport $httpTransport,
        private array $middlewares = []
    ) {}

    public function add(Middleware $middleware): self
    {
        $this->middlewares[] = $middleware;
        $this->pipeline = null;
        return $this;
    }

    public function send(RequestInterface $request, Options $options = new Options()): Result
    {
        if (!$this->pipeline) {
            $this->pipeline = $this->buildPipeline();
        }

        return ($this->pipeline)($request, $options);
    }

    private function buildPipeline(): \Closure
    {
        $chain = fn(RequestInterface $request, Options $options) => $this->httpTransport->send($request, $options);

        foreach (array_reverse($this->middlewares) as $middleware) {
            $chain = static fn(RequestInterface $request, Options $options) => $middleware->handle($request, $options, $chain);
        }

        return $chain;
    }
}
