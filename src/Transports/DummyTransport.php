<?php

declare(strict_types=1);

namespace Crystalc0d3r\HttpTransport\Transports;

use Crystalc0d3r\HttpTransport\Contracts\HttpTransport;
use Crystalc0d3r\HttpTransport\Options;
use Crystalc0d3r\HttpTransport\Result;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DummyTransport implements HttpTransport
{
    public function __construct(
        private array $queue
    ) {}

    public function send(RequestInterface $request, Options $options = new Options()): Result
    {
        if (!$this->queue) {
            throw new \OutOfBoundsException('Dummy queue is empty. No more responses or exceptions left');
        }

        $item = array_shift($this->queue);

        if ($item instanceof \Throwable) {
            throw $item;
        }

        if ($item instanceof ResponseInterface) {
            return new Result(
                $item,
                ['options' => $options]
            );
        }

        throw new \InvalidArgumentException(sprintf(
            'Queue item must be ResponseInterface or Throwable, %s given',
            get_debug_type($item)
        ));
    }
}
