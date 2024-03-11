<?php

/**
 * @author      Ian Simpson <ian@iansimpson.nz>
 * @copyright   Copyright (c) Ian Simpson
 */

namespace IanSimpson\OAuth2\Repositories;

use IanSimpson\OAuth2\Entities\AccessTokenEntity;
use IanSimpson\OAuth2\Entities\ClientEntity;
use IanSimpson\OAuth2\Entities\ScopeEntity;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use SilverStripe\ORM\DataObject;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function getAccessToken($tokenId): null|AccessTokenEntity|DataObject
    {
        $clients = AccessTokenEntity::get()->filter([
            'Code' => $tokenId,
        ]);

        return $clients->first();
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessToken): void
    {
        /** @var AccessTokenEntity $accessTokenEntity */
        $accessTokenEntity       = $accessToken;
        $accessTokenEntity->Code = $accessTokenEntity->getIdentifier();
        $accessTokenEntity->write();
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken($tokenId): void
    {
        // Some logic here to revoke the access token
        $token          = $this->getAccessToken($tokenId);
        $token->Revoked = true;
        $token->write();
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked($tokenId): bool
    {
        $token = $this->getAccessToken($tokenId);

        return (bool) $token->Revoked;
    }

    /**
     * @param ClientEntity $clientEntity
     * @param ScopeEntity[] $scopes
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): AccessTokenEntity
    {
        $accessToken = AccessTokenEntity::create();
        $accessToken->setClient($clientEntity);
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }
        $accessToken->setUserIdentifier($userIdentifier);

        return $accessToken;
    }
}