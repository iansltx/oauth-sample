<?php

namespace App\Repositories;

use Aura\Sql\ExtendedPdoInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    protected ExtendedPdoInterface $db;

    public function __construct(ExtendedPdoInterface $db)
    {
        $this->db = $db;
    }

    public function getNewRefreshToken(): ?RefreshTokenEntityInterface
    {
        return new class implements RefreshTokenEntityInterface
        {
            use EntityTrait, RefreshTokenTrait;
        };
    }

    /**
     * Create a new refresh token_name.
     *
     * @param RefreshTokenEntityInterface $refreshTokenEntity
     *
     * @throws UniqueTokenIdentifierConstraintViolationException
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        try {
            $this->db->perform('INSERT INTO refresh_token (id, access_token_id, expires_at) VALUES (?, ?, ?)', [
                $refreshTokenEntity->getIdentifier(),
                $refreshTokenEntity->getAccessToken()->getIdentifier(),
                $refreshTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s')
            ]);
        } catch (\PDOException $e) {
            throw $e->getCode() === 23000 ? UniqueTokenIdentifierConstraintViolationException::create() : $e;
        }
    }

    /**
     * Revoke the refresh token.
     *
     * @param string $tokenId
     */
    public function revokeRefreshToken($tokenId)
    {
        $this->db->perform('DELETE FROM refresh_token WHERE id = ?', [$tokenId]);
    }

    /**
     * Check if the refresh token has been revoked.
     *
     * @param string $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        return !$this->db->fetchValue('SELECT COUNT(*) FROM refresh_token WHERE id = ?', [$tokenId]);
    }
}
