<?php

namespace App\OAuth;

use App\Auth\JWT;
use App\Repositories\AccessTokenRepository;
use App\Repositories\ClientRepository;
use App\Repositories\RefreshTokenRepository;
use Exception;

class OAuthServer
{
    private ClientRepository $clientRepo;
    private AccessTokenRepository $accessTokenRepo;
    private RefreshTokenRepository $refreshTokenRepo;
    private JWT $jwt;

    public function __construct(
        ClientRepository $clientRepo,
        AccessTokenRepository $accessTokenRepo,
        RefreshTokenRepository $refreshTokenRepo,
        JWT $jwt
    ) {
        $this->clientRepo = $clientRepo;
        $this->accessTokenRepo = $accessTokenRepo;
        $this->refreshTokenRepo = $refreshTokenRepo;
        $this->jwt = $jwt;
    }

    public function issueAccessToken(string $grantType, array $params): array
    {
        switch ($grantType) {
            case 'password':
                if (empty($params['user_id']) || empty($params['client_id'])) {
                    throw new Exception('Missing user_id or client_id for password grant');
                }
                $accessToken = $this->jwt->encode([
                    'sub' => $params['user_id'],
                    'client_id' => $params['client_id'],
                    'scope' => $params['scope'] ?? ''
                ]);
                $refreshToken = bin2hex(random_bytes(32));

                $this->accessTokenRepo->store($accessToken, $params['user_id'], $params['client_id'], $params['scope'] ?? '');
                $this->refreshTokenRepo->store($refreshToken, $accessToken);

                return [
                    'access_token' => $accessToken,
                    'token_type' => 'Bearer',
                    'expires_in' => 3600,
                    'refresh_token' => $refreshToken
                ];

            case 'client_credentials':
                if (empty($params['client_id'])) {
                    throw new Exception('Missing client_id for client credentials grant');
                }
                $accessToken = $this->jwt->encode([
                    'sub' => $params['client_id'],
                    'scope' => $params['scope'] ?? ''
                ]);
                $this->accessTokenRepo->store($accessToken, null, $params['client_id'], $params['scope'] ?? '');
                return [
                    'access_token' => $accessToken,
                    'token_type' => 'Bearer',
                    'expires_in' => 3600
                ];

            case 'refresh_token':
                if (empty($params['refresh_token'])) {
                    throw new Exception('Missing refresh_token');
                }
                $oldToken = $this->refreshTokenRepo->find($params['refresh_token']);
                if (!$oldToken) {
                    throw new Exception('Invalid refresh token');
                }
                $accessToken = $this->jwt->encode([
                    'sub' => $oldToken['user_id'] ?? $oldToken['client_id'],
                    'client_id' => $oldToken['client_id'] ?? null,
                    'scope' => $oldToken['scope'] ?? ''
                ]);
                $newRefreshToken = bin2hex(random_bytes(32));
                $this->accessTokenRepo->store($accessToken, $oldToken['user_id'] ?? null, $oldToken['client_id'] ?? null, $oldToken['scope'] ?? '');
                $this->refreshTokenRepo->store($newRefreshToken, $accessToken);
                $this->refreshTokenRepo->revoke($params['refresh_token']);

                return [
                    'access_token' => $accessToken,
                    'token_type' => 'Bearer',
                    'expires_in' => 3600,
                    'refresh_token' => $newRefreshToken
                ];

            default:
                throw new Exception('Unsupported grant type: ' . $grantType);
        }
    }
}
