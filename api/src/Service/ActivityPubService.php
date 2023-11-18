<?php
declare(strict_types=1);

namespace HomoChecker\Service;

use HomoChecker\Contracts\Service\ActivityPubService as ActivityPubServiceContract;

class ActivityPubService implements ActivityPubServiceContract
{
    public function __construct(
        protected string $id,
        protected string $preferredUsername,
        protected string $publicKeyPem,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function actor(): array
    {
        return [
            '@context' => [
                'https://www.w3.org/ns/activitystreams',
                'https://w3id.org/security/v1',
            ],
            'id' => $this->id,
            'type' => 'Application',
            'inbox' => $this->id . '/inbox',
            'outbox' => $this->id . '/outbox',
            'preferredUsername' => $this->preferredUsername,
            'publicKey' => [
                'id' => $this->id . '#main-key',
                'owner' => $this->id,
                'publicKeyPem' => $this->publicKeyPem,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function webFinger(string $resource): null|array
    {
        $domain = parse_url($this->id, \PHP_URL_HOST);
        $acct = "acct:{$this->preferredUsername}@{$domain}";

        if ($resource === $acct || $resource === $this->id) {
            return [
                'subject' => $acct,
                'links' => [
                    [
                        'rel' => 'self',
                        'type' => 'application/activity+json',
                        'href' => $this->id,
                    ],
                ],
            ];
        }

        return null;
    }
}
