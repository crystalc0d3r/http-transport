<?php

declare(strict_types=1);

namespace Crystalc0d3r\HttpTransport\Exceptions;

use Psr\Http\Message\RequestInterface;

class ConnectException extends HttpTransportException
{
    public function __construct(
        string $message,
        private RequestInterface $request,
        private array $context,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $request, null, $context, $previous);
    }
}
