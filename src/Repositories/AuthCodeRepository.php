<?php

namespace App\Repositories;

use Aura\Sql\ExtendedPdoInterface;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface as Scope;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    protected ExtendedPdoInterface $db;

    public function __construct(ExtendedPdoInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Creates a new AuthCode
     *
     * @return AuthCodeEntityInterface
     */
    public function getNewAuthCode(): AuthCodeEntityInterface
    {
        return new class implements AuthCodeEntityInterface {
            use AuthCodeTrait, EntityTrait, TokenEntityTrait;
        };
    }

    /**
     * Persists a new auth code to permanent storage.
     *
     * @param AuthCodeEntityInterface $authCodeEntity
     *
     * @throws UniqueTokenIdentifierConstraintViolationException
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        try {
            $this->db->perform('INSERT INTO auth_code (id, redirect_uri, client_id, expires_at, scopes, user_id)
                    VALUES (?, ?, ?, ?, ?, ?)', [
                $authCodeEntity->getIdentifier(),
                $authCodeEntity->getRedirectUri(),
                $authCodeEntity->getClient()->getIdentifier(),
                $authCodeEntity->getExpiryDateTime()->format('Y-m-d H:i:s'),
                implode(' ', array_map(fn (Scope $scope) => $scope->getIdentifier(), $authCodeEntity->getScopes())),
                $authCodeEntity->getUserIdentifier()
            ]);
        } catch (\PDOException $e) {
            throw $e->getCode() === 23000 ? UniqueTokenIdentifierConstraintViolationException::create() : $e;
        }
    }

    /**
     * Revoke an auth code.
     *
     * @param string $codeId
     */
    public function revokeAuthCode($codeId)
    {
        $this->db->perform('DELETE FROM auth_code WHERE id = ?', [$codeId]);
    }

    /**
     * Check if the auth code has been revoked.
     *
     * @param string $codeId
     *
     * @return bool Return true if this code has been revoked
     */
    public function isAuthCodeRevoked($codeId): bool
    {
        return !$this->db->fetchValue('SELECT COUNT(*) FROM auth_code WHERE id = ?', [$codeId]);
    }
}
