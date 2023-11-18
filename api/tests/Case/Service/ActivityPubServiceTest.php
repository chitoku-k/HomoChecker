<?php
declare(strict_types=1);

namespace HomoChecker\Test\Service;

use HomoChecker\Service\ActivityPubService;
use PHPUnit\Framework\TestCase;

class ActivityPubServiceTest extends TestCase
{
    protected string $id;
    protected string $preferredUsername;
    protected string $publicKeyPem;

    public function setUp(): void
    {
        parent::setUp();

        $this->id = 'https://example.com/actor';
        $this->preferredUsername = 'example.com';
        $this->publicKeyPem = "-----BEGIN PUBLIC KEY-----\nMCowBQYDK2VwAyEAUVd1lBkQ8I/3PJIRLgXbm2TDv16wQBXuN09wWo8lh74=\n-----END PUBLIC KEY-----\n";
    }

    public function testActor(): void
    {
        $activityPub = new ActivityPubService($this->id, $this->preferredUsername, $this->publicKeyPem);

        $expected = [
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
        ];

        $actual = $activityPub->actor();
        $this->assertEquals($expected, $actual);
    }

    public function testInstanceActorWebFinger(): void
    {
        $activityPub = new ActivityPubService($this->id, $this->preferredUsername, $this->publicKeyPem);

        $expected = [
            'subject' => 'acct:example.com@example.com',
            'links' => [
                [
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => 'https://example.com/actor',
                ],
            ],
        ];

        $actual = $activityPub->webFinger('acct:example.com@example.com');
        $this->assertEquals($expected, $actual);
    }

    public function testInstanceActorWebFingerByURL(): void
    {
        $activityPub = new ActivityPubService($this->id, $this->preferredUsername, $this->publicKeyPem);

        $expected = [
            'subject' => 'acct:example.com@example.com',
            'links' => [
                [
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => 'https://example.com/actor',
                ],
            ],
        ];

        $actual = $activityPub->webFinger('https://example.com/actor');
        $this->assertEquals($expected, $actual);
    }

    public function testNonActorWebFinger(): void
    {
        $activityPub = new ActivityPubService($this->id, $this->preferredUsername, $this->publicKeyPem);

        $actual = $activityPub->webFinger('acct:non-actor@example.com');
        $this->assertNull($actual);
    }
}
