<?php
declare(strict_types=1);

namespace HomoChecker\Service;

use HomoChecker\Contracts\Service\ActivityPubService as ActivityPubServiceContract;

class ActivityPubService implements ActivityPubServiceContract
{
    public function __construct(
        protected string $id,
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
            'publicKey' => [
                'id' => $this->id . '#main-key',
                'owner' => $this->id,
                'publicKeyPem' => $this->publicKeyPem,
            ],
        ];
    }
}
