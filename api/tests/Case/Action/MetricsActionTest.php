<?php
declare(strict_types=1);

namespace HomoChecker\Test\Action;

use HomoChecker\Action\MetricsAction;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Prometheus\MetricFamilySamples;
use Prometheus\RegistryInterface;
use Prometheus\RendererInterface;
use Slim\Http\Response as HttpResponse;
use Slim\Http\ServerRequest as HttpRequest;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;

class MetricsActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testMetrics()
    {
        /** @var MetricFamilySamples[] $metrics */
        $metrics = [
            new MetricFamilySamples([
                'name' => 'homochecker_check_error_total',
                'type' => 'counter',
                'help' => 'The number of errors when checking.',
                'labelNames' => [
                    'status',
                    'code',
                    'screen_name',
                    'url',
                ],
                'samples' => [
                    [
                        'name' => 'homochecker_check_error_total',
                        'labelNames' => [],
                        'labelValues' => [
                            'status' => 'ERROR',
                            'code' => 0,
                            'screen_name' => 'baz',
                            'url' => 'https://baz.example.com',
                        ],
                        'value' => '1',
                    ],
                ],
            ]),
            new MetricFamilySamples([
                'name' => 'homochecker_check_total',
                'type' => 'counter',
                'help' => 'The number of checks.',
                'labelNames' => [
                    'status',
                    'code',
                    'screen_name',
                    'url',
                ],
                'samples' => [
                    [
                        'name' => 'homochecker_check_total',
                        'labelNames' => [],
                        'labelValues' => [
                            'status' => 'OK',
                            'code' => 301,
                            'screen_name' => 'foo',
                            'url' => 'https://foo.example.com/1',
                        ],
                        'value' => '2',
                    ],
                    [
                        'name' => 'homochecker_check_total',
                        'labelNames' => [],
                        'labelValues' => [
                            'status' => 'WRONG',
                            'code' => 200,
                            'screen_name' => 'foo',
                            'url' => 'https://foo.example.com/2',
                        ],
                        'value' => '2',
                    ],
                    [
                        'name' => 'homochecker_check_total',
                        'labelNames' => [],
                        'labelValues' => [
                            'status' => 'ERROR',
                            'code' => 0,
                            'screen_name' => 'baz',
                            'url' => 'https://baz.example.com',
                        ],
                        'value' => '2',
                    ],
                ],
            ]),
        ];

        $body = <<<'METRICS'
        # HELP homochecker_check_error_total The number of errors when checking.
        # TYPE homochecker_check_error_total counter
        homochecker_check_error_total{status="ERROR",code="0",screen_name="baz",url="https://baz.example.com"} 1
        # HELP homochecker_check_total The number of checks.
        # TYPE homochecker_check_total counter
        homochecker_check_total{status="OK",code="301",screen_name="foo",url="https://foo.example.com/1"} 2
        homochecker_check_total{status="WRONG",code="200",screen_name="foo",url="https://foo.example.com/2"} 2
        homochecker_check_total{status="ERROR",code="0",screen_name="baz",url="https://baz.example.com"} 2
        METRICS;

        /** @var MockInterface|RegistryInterface $registry */
        $registry = m::mock(RegistryInterface::class);
        $registry->shouldReceive('getMetricFamilySamples')
                 ->andReturn($metrics);

        /** @var MockInterface|RendererInterface $renderer */
        $renderer = m::mock(RendererInterface::class);
        $renderer->shouldReceive('render')
                 ->withArgs([$metrics])
                 ->andReturn($body);

        $action = new MetricsAction($registry, $renderer);
        $request = (new RequestFactory())->createRequest('GET', '/metrics');

        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), []);

        $actual = $response->getStatusCode();
        $this->assertEquals(200, $actual);

        $actual = $response->getHeaderLine('Content-Type');
        $this->assertMatchesRegularExpression('|^text/plain|', $actual);

        $actual = (string) $response->getBody();
        $this->assertEquals($body, $actual);
    }
}
