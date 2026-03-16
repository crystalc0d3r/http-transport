<?php

declare(strict_types=1);

namespace Crystalc0d3r\HttpTransport\Contracts;

use Crystalc0d3r\HttpTransport\Options;
use Crystalc0d3r\HttpTransport\Result;
use Psr\Http\Message\RequestInterface;

interface HttpTransport
{
    public function send(RequestInterface $request, Options $options = new Options()): Result;
}
