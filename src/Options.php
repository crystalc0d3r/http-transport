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
        return clone($this, [
            'timeout' => $timeout
        ]);
    }

    public function withConnectTimeout(float $connectTimeout): self
    {
        return clone($this, [
            'connectTimeout' => $connectTimeout
        ]);
    }

    public function withReadTimeout(float $readTimeout): self
    {
        return clone($this, [
            'readTimeout' => $readTimeout
        ]);
    }

    public function withProxy(?string $proxy): self
    {
        return clone($this, [
            'proxy' => $proxy
        ]);
    }

    public function withVerifySsl(bool $verifySsl): self
    {
        return clone($this, [
            'verifySsl' => $verifySsl
        ]);
    }

    public function withCompression(bool $compression): self
    {
        return clone($this, [
            'compression' => $compression
        ]);
    }

    public function withDnsCacheTimeout(int $dnsCacheTimeout): self
    {
        return clone($this, [
            'dnsCacheTimeout' => $dnsCacheTimeout
        ]);
    }

    public function withTcpNoDelay(bool $tcpNoDelay): self
    {
        return clone($this, [
            'tcpNoDelay' => $tcpNoDelay
        ]);
    }

    public function withTcpKeepAlive(bool $tcpKeepAlive): self
    {
        return clone($this, [
            'tcpKeepAlive' => $tcpKeepAlive
        ]);
    }

    public function withTcpKeepIdle(int $tcpKeepIdle): self
    {
        return clone($this, [
            'tcpKeepIdle' => $tcpKeepIdle
        ]);
    }

    public function withTcpKeepInterval(int $tcpKeepInterval): self
    {
        return clone($this, [
            'tcpKeepInterval' => $tcpKeepInterval
        ]);
    }

    public function withBufferSize(int $bufferSize): self
    {
        return clone($this, [
            'bufferSize' => $bufferSize
        ]);
    }

    public function withFollowLocation(bool $followLocation): self
    {
        return clone($this, [
            'followLocation' => $followLocation
        ]);
    }

    public function withMaxRedirects(int $maxRedirects): self
    {
        return clone($this, [
            'maxRedirects' => $maxRedirects
        ]);
    }

    public function withPreservePostOnRedirect(bool $preservePostOnRedirect): self
    {
        return clone($this, [
            'preservePostOnRedirect' => $preservePostOnRedirect
        ]);
    }

    public function withAllowedProtocols(?array $allowedProtocols): self
    {
        return clone($this, [
            'allowedProtocols' => $allowedProtocols
        ]);
    }

    public function withResponseStream(?StreamInterface $responseStream): self
    {
        return clone($this, [
            'responseStream' => $responseStream
        ]);
    }

    public function withAttribute(string $key, mixed $value): self
    {
        return clone($this, [
            'attributes' => [...$this->attributes, $key => $value]
        ]);
    }

    public function withoutAttribute(string $key): self
    {
        if (!array_key_exists($key, $this->attributes)) {
            return $this;
        }

        $attributes = $this->attributes;
        unset($attributes[$key]);

        return clone($this, [
            'attributes' => $attributes,
        ]);
    }
}
