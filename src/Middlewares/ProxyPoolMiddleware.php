<?php

declare(strict_types=1);

namespace Crystalc0d3r\HttpTransport\Middlewares;

use Crystalc0d3r\HttpTransport\Contracts\Middleware;
use Psr\Http\Message\RequestInterface;
use Crystalc0d3r\HttpTransport\Options;
use Crystalc0d3r\HttpTransport\Result;

class ProxyPoolMiddleware implements Middleware
{
    public function __construct(
        private array $proxyList,
        private ?\Closure $selector = null
    ) {
        if (!$proxyList) {
            throw new \InvalidArgumentException('Proxy list cannot be empty');
        }

        $this->proxyList = array_values($proxyList);

        $this->selector = $selector ?? static function (array $proxyList, RequestInterface $request, Options $options): string {
            return $proxyList[array_rand($proxyList)];
        };
    }

    private function nextProxy(RequestInterface $request, Options $options): string
    {
        return ($this->selector)($this->proxyList, $request, $options);
    }

    public function handle(RequestInterface $request, Options $options, callable $next): Result
    {
        if ($options->proxy === null) {
            $options->proxy = $this->nextProxy($request, $options);
        }

        return $next($request, $options);
    }
}
