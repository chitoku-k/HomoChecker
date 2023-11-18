<?php
declare(strict_types=1);

namespace HomoChecker\Test\Action;

use HomoChecker\Action\ActivityPubActorAction;
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

class ActivityPubActorActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testActor(): void
    {
        $request = (new RequestFactory())->createRequest('GET', '/actor');

        /** @var ActivityPubService&MockInterface $activityPub */
        $activityPub = m::mock(ActivityPubService::class);
        $activityPub->shouldReceive('actor')
                    ->andReturn([
                        '@context' => [
                            'https://www.w3.org/ns/activitystreams',
                            'https://w3id.org/security/v1',
                        ],
                        'id' => 'https://example.com/actor',
                        'type' => 'Application',
                        'inbox' => 'https://example.com/actor/inbox',
                        'outbox' => 'https://example.com/actor/outbox',
                        'preferredUsername' => 'example.com',
                        'publicKey' => [
                            'id' => 'https://example.com/actor#main-key',
                            'owner' => 'https://example.com/actor',
                            'publicKeyPem' => "-----BEGIN PUBLIC KEY-----\nMCowBQYDK2VwAyEAUVd1lBkQ8I/3PJIRLgXbm2TDv16wQBXuN09wWo8lh74=\n-----END PUBLIC KEY-----\n",
                        ],
                    ]);

        $action = new ActivityPubActorAction($activityPub);
        $response = $action(new HttpRequest($request), new HttpResponse(new Response(), new StreamFactory()), []);

        $actual = $response->getHeaderLine('Content-Type');
        $this->assertMatchesRegularExpression('|^application/activity\+json|', $actual);

        $actual = (string) $response->getBody();
        $expected = json_encode([
            '@context' => [
                'https://www.w3.org/ns/activitystreams',
                'https://w3id.org/security/v1',
            ],
            'id' => 'https://example.com/actor',
            'type' => 'Application',
            'inbox' => 'https://example.com/actor/inbox',
            'outbox' => 'https://example.com/actor/outbox',
            'preferredUsername' => 'example.com',
            'publicKey' => [
                'id' => 'https://example.com/actor#main-key',
                'owner' => 'https://example.com/actor',
                'publicKeyPem' => "-----BEGIN PUBLIC KEY-----\nMCowBQYDK2VwAyEAUVd1lBkQ8I/3PJIRLgXbm2TDv16wQBXuN09wWo8lh74=\n-----END PUBLIC KEY-----\n",
            ],
        ]);
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }
}
