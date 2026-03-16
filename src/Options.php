<?php

declare(strict_types=1);

namespace Crystalc0d3r\HttpTransport;

use Psr\Http\Message\StreamInterface;

class Options
{
    /**
     * Request options.
     *
     * @param float $timeout                        Maximum total time in seconds for the entire request
     * @param float $connectTimeout                 Maximum time in seconds to wait for connection
     * @param float $readTimeout                    Maximum time in seconds for read operations
     * @param string|null $proxy                    Proxy server (http://username:password@1.1.1.1:2345) or null
     * @param bool $verifySsl                       Verify SSL certificate
     * @param bool $compression                     Enable compression (gzip/deflate)
     * @param int $dnsCacheTimeout                  DNS cache timeout in seconds
     * @param bool $tcpNoDelay                      Enable TCP_NODELAY
     * @param bool $tcpKeepAlive                    Enable TCP keep-alive
     * @param int $tcpKeepIdle                      Idle time before keep-alive probes
     * @param int $tcpKeepInterval                  Interval between keep-alive probes
     * @param int $bufferSize                       Read/write buffer size
     * @param bool $followLocation                  Follow redirects
     * @param int $maxRedirects                     Maximum redirects
     * @param bool $preservePostOnRedirect          Preserve POST method on redirect
     * @param StreamInterface|null $responseStream  Stream for response body
     * @param array $attributes                     Additional transport-specific attributes
     */
    public function __construct(
        public float $timeout = 10,
        public float $connectTimeout = 10,
        public float $readTimeout = 10,
        public ?string $proxy = null,
        public bool $verifySsl = true,
        public bool $compression = true,
        public int $dnsCacheTimeout = 300,
        public bool $tcpNoDelay = true,
        public bool $tcpKeepAlive = true,
        public int $tcpKeepIdle = 30,
        public int $tcpKeepInterval = 5,
        public int $bufferSize = 65536,
        public bool $followLocation = true,
        public int $maxRedirects = 5,
        public bool $preservePostOnRedirect = false,

        public ?StreamInterface $responseStream = null,

        public array $attributes = []
    ) {}
}
