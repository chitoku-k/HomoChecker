<?php
declare(strict_types=1);

namespace HomoChecker\Test\Http;

use HomoChecker\Http\RequestSigner;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface as Request;
use Slim\Psr7\Factory\RequestFactory;

class RequestSignerTest extends TestCase
{
    protected string $id;
    protected string $publicKeyPem;
    protected string $privateKeyPem;

    public function setUp(): void
    {
        parent::setUp();

        $this->id = 'https://example.com/actor';
        $this->publicKeyPem = <<<'EOF'
        -----BEGIN PUBLIC KEY-----
        MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsBn2IZ9I0tiwtlmQclZ6
        J2IEaV/h6lDidqMrVFwRbS/c2wKqtT+OnqmXSYl5Mvl/9wDxwFiHOe87FOdC0gHz
        Exjkq4EsWrsleMLAAagpDSxLyeFtdFJKLG08fT75hhZqyQIhTCk8cRc5lqpex6aP
        nfouBWaUvPh+VyJjNTUykoSHTR11/M7mM8lwu1d2OkOQWn7C3Wy9e85acxGYLTSO
        K4YSearvK97gNaOg6JU56H8QtBMzWDeuaTh11+v2s4uc1flADP5TzKNtwg51D/AK
        O2lj+Eq1ksYsoqi/uqcBcVHgV3ZYrGIyRWf31+zlpuVlrnbrgCvN6cicSxlU8PQq
        1QIDAQAB
        -----END PUBLIC KEY-----
        EOF;
        $this->privateKeyPem = <<<'EOF'
        -----BEGIN PRIVATE KEY-----
        MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCwGfYhn0jS2LC2
        WZByVnonYgRpX+HqUOJ2oytUXBFtL9zbAqq1P46eqZdJiXky+X/3APHAWIc57zsU
        50LSAfMTGOSrgSxauyV4wsABqCkNLEvJ4W10UkosbTx9PvmGFmrJAiFMKTxxFzmW
        ql7Hpo+d+i4FZpS8+H5XImM1NTKShIdNHXX8zuYzyXC7V3Y6Q5BafsLdbL17zlpz
        EZgtNI4rhhJ5qu8r3uA1o6DolTnofxC0EzNYN65pOHXX6/azi5zV+UAM/lPMo23C
        DnUP8Ao7aWP4SrWSxiyiqL+6pwFxUeBXdlisYjJFZ/fX7OWm5WWuduuAK83pyJxL
        GVTw9CrVAgMBAAECggEAA8X2omdLk+r9NFcMc4q7UNM2lXxutorXo2OhJsxXOj/z
        i0TOHBaZy3gGTBbUQD2c2pHMXEr5UMo5uZuv8JiGmRLoOW2J4gLPDXycyRxNjuDz
        WcbJBdxKhxOrH2LlTVR3Isn3JS7gAutUuk/5umzs+F1XNZnqV3c6m8raldYHOKDx
        vN+upzEp8eIIDRLSj8YjavHGdnWOjcm/0Wmmfgshs2Sveu0SoGz2c1wsy6aVV+hA
        xCTs+B88SuozYnUg2TxfVK8k1BHmTqbdSVfpbF38gvJVRd2JIZR9vrEZYoBcmrPR
        NuFlVlLvEHcz0KO7bESly4MV429pFWkjX2pr2t5cIQKBgQDUf/Jn+fMQWRJ0vmWu
        oFgZxpgQdohuMiYFmPiX0vQtKKL7r0d3Vl0ZKAXk3+5OjAwlZCMpgRiVFCOxzrgR
        iroWfmaR6QosbD7Q3WKbxmOMuPIoaS2/wrhhCPPOV3qXabLRSMjDeJeQwU5TLo5F
        D/vTFut4Wrc9hIdDwWrvNTq/tQKBgQDUJo6iHDt5aveIKRXCkf+p+Ohmbf+EI3SJ
        sMQcWK3mIk9DhpuEUnW49Pt0sZQY+/4fPfIdPzmjw72dliqtt+L33IXHq2jOFXsA
        e/4n5odcwLSJr4HGWNm745F4jBnvyI78Vc1PP67Zl72HVuoIXaf6M9isbnWEiGZB
        mnH1vvdyoQKBgGOVxotVxsQ9ifmuFMb+m+sQd8kXU56Y39q1sqKsGQRky+S5YvuZ
        PK4CZKi7DNpApZyMTjIwLs4GjyfP4dFOuyC5geYVWVAyNkn5xjGMirCzJ8EqcWcx
        oOjQojlsI6Z7wXJ08qkwhY8wGD3BTqks8W4eiqFvmfo5do6ZQTzzLCIVAoGBAJ6c
        rSsafITMqoCMZw5vZXxI8kgSmWTLtUd0d0rSKkHTCPvtWbxWgllkH9QhKB592IK3
        J5siOA/uOoflS8dRokm5//NGfjcF7E5yZZSjUDTShqgiJZ6Ls048V/iOlp2ljvGt
        nLBRZoKcZkEXhCX5D6uKs8ZHV2ldKUaHGAipXAvBAoGAbr916h6eTTc/77Tr0WKj
        bQF7AHHqc0X+eKpzuUeE+KApiXvcnXvO+smFayfvmh9kvsBQFcI1PNa6QF3wBvTP
        BkU4+kLDPZ7q6uJXVL/3ZUJRaTTQ5gYBEc6xEXj1yIOqo3D3TdmqcXiRwculK7iw
        h7oRzSyHfQtPrQ/J7HUzIuk=
        -----END PRIVATE KEY-----
        EOF;
    }

    public function testSignWithDate(): void
    {
        $request = (new RequestFactory())->createRequest('GET', 'https://example.com/users/example.json');
        $request = $request->withHeader('Date', 'Sun, 31 Dec 2023 00:00:00 GMT');

        $pattern = '|keyId="https://example\.com/actor#main-key",headers="\(request-target\) date host",signature="(?<signature>.+)"|';

        $signer = new RequestSigner($this->id, $this->privateKeyPem);
        $middleware = $signer(function (Request $actual, array $options) use ($pattern) {
            $this->assertMatchesRegularExpression($pattern, $actual->getHeaderLine('Signature'));
            $this->assertNotFalse(preg_match($pattern, $actual->getHeaderLine('Signature'), $matches));

            $signature = base64_decode($matches['signature']);
            $this->assertNotFalse($signature);

            $data = <<<'EOF'
            (request-target): get /users/example.json
            date: Sun, 31 Dec 2023 00:00:00 GMT
            host: example.com
            EOF;
            $this->assertEquals(1, openssl_verify($data, $signature, $this->publicKeyPem, OPENSSL_ALGO_SHA256));
        });
        $middleware($request, []);
    }

    public function testSignWithoutDate(): void
    {
        $request = (new RequestFactory())->createRequest('GET', 'https://example.com/users/example.json');

        $pattern = '|keyId="https://example\.com/actor#main-key",headers="\(request-target\) date host",signature="(?<signature>.+)"|';

        $signer = new RequestSigner($this->id, $this->privateKeyPem);
        $middleware = $signer(function (Request $actual, array $options) use ($pattern) {
            $this->assertMatchesRegularExpression($pattern, $actual->getHeaderLine('Signature'));
            $this->assertNotFalse(preg_match($pattern, $actual->getHeaderLine('Signature'), $matches));

            $signature = base64_decode($matches['signature']);
            $this->assertNotFalse($signature);
        });
        $middleware($request, []);
    }

    public function testSignInvalid(): void
    {
        $request = (new RequestFactory())->createRequest('GET', 'https://example.com/users/example.json');

        $signer = new RequestSigner($this->id, 'invalid privateKeyPem');
        $middleware = $signer(function (Request $actual, array $options) {
            $this->fail('The next handler must not be called');
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Signing request failed');
        $middleware($request, []);
    }
}
