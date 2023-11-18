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
                'publicKeyPem' => "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsBn2IZ9I0tiwtlmQclZ6\nJ2IEaV/h6lDidqMrVFwRbS/c2wKqtT+OnqmXSYl5Mvl/9wDxwFiHOe87FOdC0gHz\nExjkq4EsWrsleMLAAagpDSxLyeFtdFJKLG08fT75hhZqyQIhTCk8cRc5lqpex6aP\nnfouBWaUvPh+VyJjNTUykoSHTR11/M7mM8lwu1d2OkOQWn7C3Wy9e85acxGYLTSO\nK4YSearvK97gNaOg6JU56H8QtBMzWDeuaTh11+v2s4uc1flADP5TzKNtwg51D/AK\nO2lj+Eq1ksYsoqi/uqcBcVHgV3ZYrGIyRWf31+zlpuVlrnbrgCvN6cicSxlU8PQq\n1QIDAQAB\n-----END PUBLIC KEY-----",
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
