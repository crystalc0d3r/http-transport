<?php

declare(strict_types=1);

namespace Crystalc0d3r\HttpTransport;

use Psr\Http\Message\ResponseInterface;

class Result
{
    public function __construct(
        private ResponseInterface $response,
        private array $context
    ) {}

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}