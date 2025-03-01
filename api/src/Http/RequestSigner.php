<?php
declare(strict_types=1);

namespace HomoChecker\Http;

use Psr\Http\Message\RequestInterface as Request;

final class RequestSigner
{
    public function __construct(
        protected string $id,
        protected string $privateKeyPem,
    ) {}

    /**
     * Sign the given HTTP request.
     * @return Request The signed request.
     */
    private function sign(Request $request): Request
    {
        $requestTarget = strtolower($request->getMethod()) . ' ' . $request->getRequestTarget();
        if (!$date = $request->getHeaderLine('Date')) {
            $date = (new \DateTimeImmutable())->format(\DateTimeImmutable::RFC7231);
            $request = $request->withHeader('Date', $date);
        }
        if (!$host = $request->getHeaderLine('Host')) {
            $host = $request->getUri()->getHost();
            $request = $request->withHeader('Host', $host);
        }

        $data = implode("\n", [
            "(request-target): {$requestTarget}",
            "date: {$date}",
            "host: {$host}",
        ]);

        set_error_handler(fn ($severity, $message, $filename, $line) => throw new \ErrorException($message, 0, $severity, $filename, $line));

        try {
            openssl_sign($data, $signature, $this->privateKeyPem, OPENSSL_ALGO_SHA256);
            return $request->withHeader('Signature', implode(',', [
                "keyId=\"{$this->id}#main-key\"",
                'headers="(request-target) date host"',
                'signature="' . base64_encode($signature) . '"',
            ]));
        } catch (\Throwable $e) {
            throw new \RuntimeException('Signing request failed', 0, $e);
        } finally {
            restore_error_handler();
        }
    }

    public function __invoke(callable $handler): callable
    {
        return fn (Request $request, array $options) => $handler($this->sign($request), $options);
    }
}
