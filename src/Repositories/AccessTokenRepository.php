<?php

namespace App\Repositories;

use App\Models\AccessToken;
use Aura\Sql\ExtendedPdoInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface as Token;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface as Scope;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    protected ExtendedPdoInterface $db;

    public function __construct(ExtendedPdoInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new access token
     *
     * @param ClientEntityInterface $clientEntity
     * @param Scope[] $scopes
     * @param mixed $userIdentifier
     *
     * @return Token
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): Token
    {
        return new class($clientEntity, $scopes, $userIdentifier) implements Token
        {
            use AccessTokenTrait, TokenEntityTrait, EntityTrait;

            public function __construct(ClientEntityInterface $client, array $scopes, ?int $userId)
            {
                $this->client = $client;
                $this->userIdentifier = $userId;
                $this->scopes = $scopes;
            }
        };
    }

    /**
     * Persists a new access token to permanent storage.
     *
     * @param Token $accessTokenEntity
     *
     * @throws UniqueTokenIdentifierConstraintViolationException
     */
    public function persistNewAccessToken(Token $accessTokenEntity)
    {
        try {
            $this->db->perform('INSERT INTO access_token (id, client_id, user_id, scopes, expires_at) VALUES
                (?, ?, ?, ?, ?)', [
                $accessTokenEntity->getIdentifier(),
                $accessTokenEntity->getClient()->getIdentifier(),
                $accessTokenEntity->getUserIdentifier(),
                implode(' ', array_map(fn (Scope $scope) => $scope->getIdentifier(), $accessTokenEntity->getScopes())),
                $accessTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s')
            ]);
        } catch (\PDOException $e) {
            throw $e->getCode() === 23000 ? UniqueTokenIdentifierConstraintViolationException::create() : $e;
        }
    }

    /**
     * Revoke an access token.
     *
     * @param string $tokenId
     */
    public function revokeAccessToken($tokenId)
    {
        $this->db->perform('DELETE FROM access_token WHERE id = ?', [$tokenId]);
    }

    /**
     * Check if the access token has been revoked.
     *
     * @param string $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isAccessTokenRevoked($tokenId)
    {
        return !$this->db->fetchValue('SELECT COUNT(*) FROM access_token WHERE id = ?', [$tokenId]);
    }
}
