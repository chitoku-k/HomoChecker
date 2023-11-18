<?php
declare(strict_types=1);

namespace HomoChecker\Test\Action;

use HomoChecker\Action\WebFingerAction;
use HomoChecker\Contracts\Service\ActivityPubService;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response as HttpResponse;
use Slim\Http\ServerRequest as HttpRequest;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;

class WebFingerActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstanceActor(): void
    {
        $request = (new RequestFactory())->createRequest('GET', '/.well-known/webfinger?resource=acct:example.com@example.com');

        /** @var ActivityPubService&MockInterface $activityPub */
        $activityPub = m::mock(ActivityPubService::class);
        $activityPub->shouldReceive('webFinger')
                    ->with('acct:example.com@example.com')
                    ->andReturn([
                        'subject' => 'acct:example.com@example.com',
                        'links' => [
                            [
                                'rel' => 'self',
                                'type' => 'application/activity+json',
                                'href' => 'https://example.com/actor',
                            ],
                        ],
                    ]);

        $action = new WebFingerAction($activityPub);
        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), []);

        $actual = $response->getHeaderLine('Content-Type');
        $this->assertMatchesRegularExpression('|^application/jrd\+json|', $actual);

        $actual = $response->getStatusCode();
        $this->assertEquals(200, $actual);

        $actual = (string) $response->getBody();
        $expected = json_encode([
            'subject' => 'acct:example.com@example.com',
            'links' => [
                [
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => 'https://example.com/actor',
                ],
            ],
        ]);
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testInstanceActorID(): void
    {
        $request = (new RequestFactory())->createRequest('GET', '/.well-known/webfinger?resource=https://example.com/actor');

        /** @var ActivityPubService&MockInterface $activityPub */
        $activityPub = m::mock(ActivityPubService::class);
        $activityPub->shouldReceive('webFinger')
                    ->with('https://example.com/actor')
                    ->andReturn([
                        'subject' => 'acct:example.com@example.com',
                        'links' => [
                            [
                                'rel' => 'self',
                                'type' => 'application/activity+json',
                                'href' => 'https://example.com/actor',
                            ],
                        ],
                    ]);

        $action = new WebFingerAction($activityPub);
        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), []);

        $actual = $response->getHeaderLine('Content-Type');
        $this->assertMatchesRegularExpression('|^application/jrd\+json|', $actual);

        $actual = $response->getStatusCode();
        $this->assertEquals(200, $actual);

        $actual = (string) $response->getBody();
        $expected = json_encode([
            'subject' => 'acct:example.com@example.com',
            'links' => [
                [
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => 'https://example.com/actor',
                ],
            ],
        ]);
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testInvalidActor(): void
    {
        $request = (new RequestFactory())->createRequest('GET', '/.well-known/webfinger');

        /** @var ActivityPubService&MockInterface $activityPub */
        $activityPub = m::mock(ActivityPubService::class);

        $action = new WebFingerAction($activityPub);
        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), []);

        $actual = $response->getStatusCode();
        $this->assertEquals(400, $actual);

        $actual = (string) $response->getBody();
        $this->assertEquals('', $actual);
    }

    public function testNonActor(): void
    {
        $request = (new RequestFactory())->createRequest('GET', '/.well-known/webfinger?resource=acct:non-actor@example.com');

        /** @var ActivityPubService&MockInterface $activityPub */
        $activityPub = m::mock(ActivityPubService::class);
        $activityPub->shouldReceive('webFinger')
                    ->with('acct:non-actor@example.com')
                    ->andReturn(null);

        $action = new WebFingerAction($activityPub);
        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), []);

        $actual = $response->getStatusCode();
        $this->assertEquals(404, $actual);

        $actual = (string) $response->getBody();
        $this->assertEquals('', $actual);
    }
}
