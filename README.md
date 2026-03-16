# http-transport

Minimal PSR-7 HTTP transport foundation: a small transport contract, configurable request options, and a lightweight middleware pipeline.

This package is implementation-agnostic (you can plug in any transport that implements the interface). A cURL-based transport is included as a reference implementation (`CurlTransport`).

Package: `crystalc0d3r/http-transport`

## Requirements

- PHP **8.5+**
- PSR-7 (`psr/http-message`)
- PSR-17 (`psr/http-factory`)

The examples use `nyholm/psr7`, but you can use any PSR-17 factory implementation.

## Installation

```bash
composer require crystalc0d3r/http-transport
```

## Quick start (cURL transport example)

`CurlTransport` needs a `ResponseFactoryInterface` and (unless you always pass `Options::$responseStream`) a `StreamFactoryInterface`.

```php
<?php

declare(strict_types=1);

use Crystalc0d3r\HttpTransport\Options;
use Crystalc0d3r\HttpTransport\Transports\CurlTransport;
use Nyholm\Psr7\Factory\Psr17Factory;

$psr17 = new Psr17Factory();

$transport = new CurlTransport(
    responseFactory: $psr17,
    streamFactory: $psr17
);

$request = $psr17
    ->createRequest('GET', 'https://httpbin.org/get')
    ->withHeader('Accept', 'application/json');

$result = $transport->send($request, new Options(
    timeout: 15,
    connectTimeout: 5,
    followLocation: true,
));

$response = $result->getResponse();

echo $response->getStatusCode() . PHP_EOL;
echo (string) $response->getBody() . PHP_EOL;
```

## Options

Most transport configuration is done through `Crystalc0d3r\HttpTransport\Options`:

- **timeouts**: `timeout`, `connectTimeout`, `readTimeout`
- **proxy**: `proxy` (example: `http://user:pass@1.2.3.4:8080`)
- **SSL**: `verifySsl`
- **redirects**: `followLocation`, `maxRedirects`, `preservePostOnRedirect`
- **connection tuning**: `dnsCacheTimeout`, `tcpNoDelay`, `tcpKeepAlive`, `tcpKeepIdle`, `tcpKeepInterval`, `bufferSize`
- **response streaming**: `responseStream`
- **extra transport attributes**: `attributes` (for cURL: `attributes['curl'] = [CURLOPT_* => ...]`)

### Example: custom cURL attributes

```php
use Crystalc0d3r\HttpTransport\Options;

$options = new Options(
    attributes: [
        'curl' => [
            CURLOPT_USERAGENT => 'http-transport/1.0',
            CURLOPT_HTTPHEADER => ['X-Debug: 1'],
        ],
    ]
);
```

## Middleware pipeline

Use `Crystalc0d3r\HttpTransport\Pipeline` to wrap a transport with middlewares.

### Example: rotate proxies with `ProxyPoolMiddleware`

```php
<?php

declare(strict_types=1);

use Crystalc0d3r\HttpTransport\Middlewares\ProxyPoolMiddleware;
use Crystalc0d3r\HttpTransport\Pipeline;
use Crystalc0d3r\HttpTransport\Transports\CurlTransport;
use Nyholm\Psr7\Factory\Psr17Factory;

$psr17 = new Psr17Factory();

$base = new CurlTransport($psr17, $psr17);

$pipeline = new Pipeline($base, [
    new ProxyPoolMiddleware([
        'http://1.2.3.4:8000',
        'http://5.6.7.8:8000',
    ]),
]);

$request = $psr17->createRequest('GET', 'https://httpbin.org/ip');
$result = $pipeline->send($request);

echo (string) $result->getResponse()->getBody() . PHP_EOL;
```

## Streaming response to a file (variant)

If you want to avoid buffering the whole body in memory, pass your own `responseStream`.

```php
<?php

declare(strict_types=1);

use Crystalc0d3r\HttpTransport\Options;
use Crystalc0d3r\HttpTransport\Transports\CurlTransport;
use Nyholm\Psr7\Factory\Psr17Factory;

$psr17 = new Psr17Factory();
$transport = new CurlTransport($psr17, $psr17);

$out = fopen(__DIR__ . '/response.bin', 'w+b');
$stream = $psr17->createStreamFromResource($out);

$request = $psr17->createRequest('GET', 'https://httpbin.org/bytes/1024');

$transport->send($request, new Options(responseStream: $stream));

fclose($out);
```

## Error handling

The cURL backend throws typed exceptions:

- `Crystalc0d3r\HttpTransport\Exceptions\TimeoutException`
- `Crystalc0d3r\HttpTransport\Exceptions\ConnectException`
- `Crystalc0d3r\HttpTransport\Exceptions\TransferException`
- `Crystalc0d3r\HttpTransport\Exceptions\HttpTransportException` (fallback)

Each transport exception exposes:

- `getRequest(): RequestInterface`
- `getResponse(): ?ResponseInterface`
- `hasResponse(): bool`
- `getContext(): array` (includes `options` and, for cURL, `curl` info like `errno`, `error`, `http_code`, etc.)

## Testing utilities (variant)

`DummyTransport` is useful for unit tests: it consumes a queue of `ResponseInterface` and/or `Throwable`.

```php
<?php

declare(strict_types=1);

use Crystalc0d3r\HttpTransport\Transports\DummyTransport;
use Nyholm\Psr7\Factory\Psr17Factory;

$psr17 = new Psr17Factory();

$transport = new DummyTransport([
    $psr17->createResponse(200)->withBody($psr17->createStream('ok')),
    new RuntimeException('boom'),
]);

$request = $psr17->createRequest('GET', 'https://example.test');

$first = $transport->send($request)->getResponse();
echo $first->getStatusCode() . ' ' . (string) $first->getBody() . PHP_EOL;

// next call throws RuntimeException('boom')
$transport->send($request);
```

## License

This project is licensed under the MIT License.  
See the `LICENSE` file for the full text (any redistribution must keep the copyright and license notice).

