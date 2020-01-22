<?php

namespace App\Repositories;

use Aura\Sql\ExtendedPdoInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;

class ClientRepository implements ClientRepositoryInterface
{
    private const CLIENTS = [
        'first-party' => [
            'name' => 'First Party App',
            'isConfidential' => false,
        ],
        'machine-to-machine' => [
            'name' => 'Machine to Machine',
            'redirects' => [],
            'isConfidential' => true,
            'secret' => 'super-secret-client-secret-string'
        ],
        'single-page-app' => [
            'name' => 'Single Page App',
            'redirects' => ['http://localhost/spa-implicit.php'],
            'isConfidential' => false
        ],
        'third-party-service' => [
            'name' => 'Third Party Service',
            'redirects' => ['http://localhost/server-side-auth-code.php'],
            'isConfidential' => true,
            'secret' => 'another-super-secret-string'
        ]
    ];

    protected ExtendedPdoInterface $db;

    public function __construct(ExtendedPdoInterface $db)
    {
        $this->db = $db;
    }

    public function wasApproved(AuthorizationRequest $authRequest): bool
    {
        return $this->db->fetchValue('SELECT COUNT(*) FROM user_client_consent
                WHERE user_id = ? && client_id = ? && scopes LIKE ?', [
            $authRequest->getUser()->getIdentifier(),
            $authRequest->getClient()->getIdentifier(),
            '%' . $this->getScopeList($authRequest) . '%'
        ]) > 0;
    }

    public function recordApproval(AuthorizationRequest $authRequest): void
    {
        $this->db->perform('INSERT IGNORE INTO user_client_consent (user_id, client_id, scopes) VALUES (?, ?, ?)', [
                $authRequest->getUser()->getIdentifier(),
                $authRequest->getClient()->getIdentifier(),
                $this->getScopeList($authRequest)
            ]);
    }

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
        return ($client = $this->getClientEntity($clientIdentifier)) !== null &&
            (!$client->isConfidential() || hash_equals(self::CLIENTS[$clientIdentifier]['secret'], $clientSecret));
    }

    protected function getScopeList(AuthorizationRequest $authRequest)
    {
        $scopeList = array_map(fn (ScopeEntityInterface $scope) => $scope->getIdentifier(), $authRequest->getScopes());
        sort($scopeList);

        return implode(' ', $scopeList);
    }
}
