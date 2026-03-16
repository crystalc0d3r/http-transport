<?php

declare(strict_types=1);

namespace Crystalc0d3r\HttpTransport\Contracts;

use Crystalc0d3r\HttpTransport\Options;
use Crystalc0d3r\HttpTransport\Result;
use Psr\Http\Message\RequestInterface;

interface Middleware
{
    public function handle(RequestInterface $request, Options $options, callable $next): Result;
}
