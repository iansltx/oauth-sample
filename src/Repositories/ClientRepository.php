<?php

namespace App\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    private const CLIENTS = [
        'first-party' => [
            'name' => 'First Party App',
            'isConfidential' => false,
        ]
    ];

    /**
     * Get a client.
     *
     * @param string $clientIdentifier The client's identifier
     *
     * @return ClientEntityInterface|null
     */
    public function getClientEntity($clientIdentifier): ?ClientEntityInterface
    {
        if (!($row = static::CLIENTS[$clientIdentifier] ?? null)) {
            return null;
        }

        return new class($row['name'], $clientIdentifier, $row['redirects'] ?? [], $row['isConfidential'] ?? false)
            implements ClientEntityInterface
        {
            use ClientTrait, EntityTrait;

            public function __construct(string $name, string $id, array $redirectUris, bool $isConfidential = false)
            {
                $this->name = $name;
                $this->redirectUri = $redirectUris;
                $this->identifier = $id;
                $this->isConfidential = $isConfidential;
            }
        };
    }

    /**
     * Validate a client's secret.
     *
     * @param string $clientIdentifier The client's identifier
     * @param null|string $clientSecret The client's secret (if sent)
     * @param null|string $grantType The type of grant the client is using (if sent)
     *
     * @return bool
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        return $this->getClientEntity($clientIdentifier) !== null;
    }
}
