<?php

namespace App\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\ScopeTrait;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;

class ScopeRepository implements ScopeRepositoryInterface
{
    protected const VALID_SCOPES = [
        'me.name' => 'Access to your first and last name',
        'me.hash' => 'Access to your hash value',
        'time' => 'Access the current time'
    ];

    /**
     * Return information about a scope.
     *
     * @param string $identifier The scope identifier
     *
     * @return ScopeEntityInterface|null
     */
    public function getScopeEntityByIdentifier($identifier): ?ScopeEntityInterface
    {
        return isset(self::VALID_SCOPES[$identifier]) ? new class ($identifier) implements ScopeEntityInterface
        {
            use ScopeTrait, EntityTrait;

            public function __construct(string $name)
            {
                $this->identifier = $name;
            }
        } : null;
    }

    public function listRequestedScopes(AuthorizationRequest $authRequest): array
    {
        return array_map(
            fn (ScopeEntityInterface $scope) => self::VALID_SCOPES[$scope->getIdentifier()],
            $authRequest->getScopes()
        );
    }

    /**
     * Given a client, grant type and optional user identifier validate the set of scopes requested are valid and optionally
     * append additional scopes or remove requested scopes.
     *
     * @param ScopeEntityInterface[] $scopes
     * @param string $grantType
     * @param ClientEntityInterface $clientEntity
     * @param null|string $userIdentifier
     *
     * @return ScopeEntityInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ): array {
        if (!$userIdentifier) {
            return [$this->getScopeEntityByIdentifier('time')];
        }

        return count($scopes) ? $scopes : [$this->getScopeEntityByIdentifier('me.name')];
    }
}
