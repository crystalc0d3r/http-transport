<?php

declare(strict_types=1);

namespace Crystalc0d3r\HttpTransport;

use Psr\Http\Message\StreamInterface;

readonly class Options
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
     * @param array|null $allowedProtocols          Allowed protocols ['http', 'https'] or null
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
        public ?array $allowedProtocols = null,

        public ?StreamInterface $responseStream = null,

        public array $attributes = []
    ) {}

    public function withTimeout(float $timeout): self
    {
        $new = clone $this;
        $new->timeout = $timeout;
        return $new;
    }

    public function withConnectTimeout(float $connectTimeout): self
    {
        $new = clone $this;
        $new->connectTimeout = $connectTimeout;
        return $new;
    }

    public function withReadTimeout(float $readTimeout): self
    {
        $new = clone $this;
        $new->readTimeout = $readTimeout;
        return $new;
    }

    public function withProxy(?string $proxy): self
    {
        $new = clone $this;
        $new->proxy = $proxy;
        return $new;
    }

    public function withVerifySsl(bool $verifySsl): self
    {
        $new = clone $this;
        $new->verifySsl = $verifySsl;
        return $new;
    }

    public function withCompression(bool $compression): self
    {
        $new = clone $this;
        $new->compression = $compression;
        return $new;
    }

    public function withDnsCacheTimeout(int $dnsCacheTimeout): self
    {
        $new = clone $this;
        $new->dnsCacheTimeout = $dnsCacheTimeout;
        return $new;
    }

    public function withTcpNoDelay(bool $tcpNoDelay): self
    {
        $new = clone $this;
        $new->tcpNoDelay = $tcpNoDelay;
        return $new;
    }

    public function withTcpKeepAlive(bool $tcpKeepAlive): self
    {
        $new = clone $this;
        $new->tcpKeepAlive = $tcpKeepAlive;
        return $new;
    }

    public function withTcpKeepIdle(int $tcpKeepIdle): self
    {
        $new = clone $this;
        $new->tcpKeepIdle = $tcpKeepIdle;
        return $new;
    }

    public function withTcpKeepInterval(int $tcpKeepInterval): self
    {
        $new = clone $this;
        $new->tcpKeepInterval = $tcpKeepInterval;
        return $new;
    }

    public function withBufferSize(int $bufferSize): self
    {
        $new = clone $this;
        $new->bufferSize = $bufferSize;
        return $new;
    }

    public function withFollowLocation(bool $followLocation): self
    {
        $new = clone $this;
        $new->followLocation = $followLocation;
        return $new;
    }

    public function withMaxRedirects(int $maxRedirects): self
    {
        $new = clone $this;
        $new->maxRedirects = $maxRedirects;
        return $new;
    }

    public function withPreservePostOnRedirect(bool $preservePostOnRedirect): self
    {
        $new = clone $this;
        $new->preservePostOnRedirect = $preservePostOnRedirect;
        return $new;
    }

    public function withAllowedProtocols(?array $allowedProtocols): self
    {
        $new = clone $this;
        $new->allowedProtocols = $allowedProtocols;
        return $new;
    }

    public function withResponseStream(?StreamInterface $responseStream): self
    {
        $new = clone $this;
        $new->responseStream = $responseStream;
        return $new;
    }

    public function withAttribute(string $key, mixed $value): self
    {
        $new = clone $this;
        $new->attributes[$key] = $value;
        return $new;
    }

    public function withoutAttribute(string $key): self
    {
        if (!array_key_exists($key, $this->attributes)) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$key]);
        return $new;
    }
}
