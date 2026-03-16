<?php

declare(strict_types=1);

namespace Crystalc0d3r\HttpTransport\Exceptions;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpTransportException extends \RuntimeException
{
    public function __construct(
        string $message,
        private RequestInterface $request,
        private ?ResponseInterface $response = null,
        private array $context,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function hasResponse(): bool
    {
        return $this->response !== null;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
