<?php

declare(strict_types=1);

namespace Crystalc0d3r\HttpTransport\Transports;

use Crystalc0d3r\HttpTransport\Contracts\HttpTransport;
use Crystalc0d3r\HttpTransport\Exceptions\ConnectException;
use Crystalc0d3r\HttpTransport\Exceptions\HttpTransportException;
use Crystalc0d3r\HttpTransport\Exceptions\TimeoutException;
use Crystalc0d3r\HttpTransport\Exceptions\TransferException;
use Crystalc0d3r\HttpTransport\Options;
use Crystalc0d3r\HttpTransport\Result;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class CurlTransport implements HttpTransport
{
    private \CurlHandle $curlHandle;

    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private ?StreamFactoryInterface $streamFactory = null
    ) {
        $this->curlHandle = curl_init();
    }

    public function send(RequestInterface $request, Options $options = new Options()): Result
    {
        $this->resetOptions();
        $this->applyOptions($request, $options);

        $responseStream = $this->prepareResponseStream($options);

        $headers = [];
        $statusCode = 0;
        $reasonPhrase = '';

        $this->setupHeaderCallback($headers, $statusCode, $reasonPhrase);
        $this->setupBodyCallback($responseStream);

        curl_exec($this->curlHandle);

        $errno      = curl_errno($this->curlHandle);
        $curlError  = curl_error($this->curlHandle);
        $curlInfo   = curl_getinfo($this->curlHandle);

        if ($statusCode === 0) {
            $statusCode = $curlInfo['http_code'] ?? 0;
        }

        if ($responseStream->isSeekable()) {
            $responseStream->rewind();
        }

        if ($errno !== 0) {
            $context = $this->buildResultContext(
                $options,
                $curlInfo + ['errno' => $errno, 'error' => $curlError]
            );

            $partialResponse = null;

            if ($statusCode !== 0 || $headers) {
                $partialResponse = $this->buildResponse($responseStream, $statusCode, $reasonPhrase, $headers);
            }

            $this->throwCurlException($errno, $curlError, $request, $context, $partialResponse);
        }

        return new Result(
            $this->buildResponse($responseStream, $statusCode, $reasonPhrase, $headers),
            $this->buildResultContext($options, $curlInfo)
        );
    }

    private function throwCurlException(int $errno, string $errorMessage, RequestInterface $request, array $context, ?ResponseInterface $response = null): void
    {
        $message = sprintf('cURL error (%d): %s', $errno, $errorMessage);

        match (true) {
            in_array($errno, [
                CURLE_OPERATION_TIMEDOUT
            ], true) =>
            throw new TimeoutException($message, $request, $response, $context),

            in_array($errno, [
                CURLE_COULDNT_RESOLVE_PROXY,
                CURLE_COULDNT_RESOLVE_HOST,
                CURLE_COULDNT_CONNECT,
                CURLE_SSL_CONNECT_ERROR,
                CURLE_SSL_CERTPROBLEM,
                CURLE_SSL_CACERT,
                CURLE_SSL_CIPHER,
                CURLE_PROXY,
            ], true) =>
            throw new ConnectException($message, $request, $context),

            in_array($errno, [
                CURLE_RECV_ERROR,
                CURLE_SEND_ERROR,
                CURLE_PARTIAL_FILE,
                CURLE_READ_ERROR,
                CURLE_WRITE_ERROR,
                CURLE_GOT_NOTHING,
                CURLE_FILESIZE_EXCEEDED,
                CURLE_ABORTED_BY_CALLBACK,
            ], true) =>
            throw new TransferException($message, $request, $response, $context),

            default =>
            throw new HttpTransportException($message, $request, $response, $context),
        };
    }

    private function buildResultContext(Options $options, ?array $curlInfo = null): array
    {
        $context = [
            'options' => $options
        ];

        if ($curlInfo) {
            $context['curl'] = $curlInfo;
        }

        return $context;
    }

    private function prepareResponseStream(Options $options): StreamInterface
    {
        if ($options->responseStream !== null) {
            return $options->responseStream;
        }

        if ($this->streamFactory === null) {
            throw new \InvalidArgumentException('Response stream must be provided via Options or StreamFactory must be set');
        }

        return $this->streamFactory->createStreamFromResource(fopen('php://temp', 'w+'));
    }

    private function setupHeaderCallback(array &$headers, int &$statusCode, string &$reasonPhrase): void
    {
        curl_setopt($this->curlHandle, CURLOPT_HEADERFUNCTION, static function ($ch, $header) use (&$headers, &$statusCode, &$reasonPhrase): int {
            $trimmed = rtrim($header, "\r\n");

            if ($trimmed === '') {
                return strlen($header);
            }

            if (str_starts_with(strtoupper($trimmed), 'HTTP/')) {
                if (preg_match('#^HTTP/[\d.]+\s+(\d+)\s*(.*)$#i', $trimmed, $matches)) {
                    $statusCode   = (int) $matches[1];
                    $reasonPhrase = trim($matches[2] ?? '');
                }

                $headers = [];
                return strlen($header);
            }

            if (strpos($trimmed, ':') !== false) {
                [$name, $value] = explode(':', $trimmed, 2);
                $headers[trim($name)][] = trim($value);
            }

            return strlen($header);
        });
    }

    private function setupBodyCallback($responseStream): void
    {
        curl_setopt($this->curlHandle, CURLOPT_WRITEFUNCTION, static function ($ch, $data) use ($responseStream): int {
            return $responseStream->write($data);
        });
    }

    private function buildResponse(StreamInterface $responseStream, int $statusCode, string $reasonPhrase, array $headers): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($statusCode, $reasonPhrase);

        foreach ($headers as $name => $values) {
            $response = $response->withHeader($name, $values);
        }

        return $response->withBody($responseStream);
    }

    private function resetOptions(): void
    {
        curl_setopt($this->curlHandle, CURLOPT_HEADERFUNCTION, null);
        curl_setopt($this->curlHandle, CURLOPT_READFUNCTION, null);
        curl_setopt($this->curlHandle, CURLOPT_WRITEFUNCTION, null);
        curl_setopt($this->curlHandle, CURLOPT_PROGRESSFUNCTION, null);
        curl_reset($this->curlHandle);
    }

    private function applyOptions(RequestInterface $request, Options $options): void
    {
        $curlOptions = [];

        foreach (
            [
                $this->applyBaseOptions($request, $options),
                $this->applyProxyOptions($options),
                $this->applyRedirectOptions($options),
                $this->applyProtocolVersion($request),
                $this->applyConnectionOptions($options),
                $this->applyHeaders($request),
                $this->applyBody($request),
                $this->applyCustomCurlAttributes($options),
            ] as $opts
        ) {
            foreach ($opts as $k => $v) {
                $curlOptions[$k] = $v;
            }
        }

        curl_setopt_array($this->curlHandle, $curlOptions);
    }

    private function applyBaseOptions(RequestInterface $request, Options $options): array
    {
        return [
            CURLOPT_URL            => (string) $request->getUri(),
            CURLOPT_CUSTOMREQUEST  => $request->getMethod(),
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER         => false,
            CURLOPT_CONNECTTIMEOUT => $options->connectTimeout,
            CURLOPT_TIMEOUT        => $options->timeout,
            CURLOPT_SSL_VERIFYPEER => $options->verifySsl,
            CURLOPT_SSL_VERIFYHOST => $options->verifySsl ? 2 : 0,
        ];
    }

    private function applyProxyOptions(Options $options): array
    {
        return $options->proxy !== null
            ? [CURLOPT_PROXY => $options->proxy]
            : [];
    }

    private function applyRedirectOptions(Options $options): array
    {
        $opts = [];
        if ($options->followLocation) {
            $opts[CURLOPT_FOLLOWLOCATION] = true;
            $opts[CURLOPT_MAXREDIRS]      = $options->maxRedirects;
        }
        if ($options->preservePostOnRedirect) {
            $opts[CURLOPT_POSTREDIR] = 3;
        }
        return $opts;
    }

    private function applyProtocolVersion(RequestInterface $request): array
    {
        return match ($request->getProtocolVersion()) {
            '3', '3.0' => [CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_3],
            '2', '2.0' => [CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0],
            '1.0'      => [CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0],
            default    => [CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1],
        };
    }

    private function applyConnectionOptions(Options $options): array
    {
        $opts = [
            CURLOPT_ENCODING          => $options->compression ? '' : 'identity',
            CURLOPT_DNS_CACHE_TIMEOUT => $options->dnsCacheTimeout,
            CURLOPT_TCP_NODELAY       => $options->tcpNoDelay,
            CURLOPT_TCP_KEEPALIVE     => $options->tcpKeepAlive ? 1 : 0,
            CURLOPT_BUFFERSIZE        => min($options->bufferSize, 1024 * 1024),
        ];

        if ($options->tcpKeepAlive) {
            $opts[CURLOPT_TCP_KEEPIDLE]  = $options->tcpKeepIdle;
            $opts[CURLOPT_TCP_KEEPINTVL] = $options->tcpKeepInterval;
        }

        return $opts;
    }

    private function applyHeaders(RequestInterface $request): array
    {
        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            if (!$values) {
                $headers[] = $name . ';';
            } else {
                $headers[] = $name . ': ' . implode(', ', $values);
            }
        }

        return $headers ? [CURLOPT_HTTPHEADER => $headers] : [];
    }

    private function applyBody(RequestInterface $request): array
    {
        $bodyStream = $request->getBody();
        $bodySize   = $bodyStream->getSize();

        if ($bodySize === 0) {
            return [];
        }

        if (!$bodyStream->isSeekable() || $bodySize <= 1024 * 1024) {
            return [CURLOPT_POSTFIELDS => (string) $bodyStream];
        }

        if ($bodyStream->isSeekable()) {
            $bodyStream->rewind();
        }

        return [
            CURLOPT_UPLOAD     => true,
            CURLOPT_INFILESIZE => $bodySize,
            CURLOPT_READFUNCTION => static function ($ch, $fd, $length) use ($bodyStream) {
                $data = $bodyStream->read($length);
                return $data === '' ? '' : $data;
            },
        ];
    }

    private function applyCustomCurlAttributes(Options $options): array
    {
        if (empty($options->attributes['curl']) || !is_array($options->attributes['curl'])) {
            return [];
        }

        return $options->attributes['curl'];
    }
}
