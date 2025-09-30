<?php

namespace App\Controllers;

use App\Http\RequestDTO;
use App\Repositories\UserRepository;
use Exception;

class UserController implements ControllerInterface
{
    public function __construct(
        private readonly UserRepository $repository
    ) {}

    public function getProfile(RequestDTO $requestDTO): array
    {
        try {
            $userId = $requestDTO->bodyParams['user_id'];

            $user = $this->repository->getUserById($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'User not found'
                ];
            }

            return [
                'success' => true,
                'data' => $user
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function updateProfile(RequestDTO $requestDTO): array
    {
        try {
            $userId = $requestDTO->bodyParams['user_id'];
            $data = $requestDTO->bodyParams['data'] ?? [];

            $updatedUser = $this->repository->updateUser($userId, $data);

            return [
                'success' => true,
                'data' => $updatedUser
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function deleteProfile(RequestDTO $requestDTO): array
    {
        try {
            $userId = $requestDTO->bodyParams['user_id'];

            $this->repository->deleteUser($userId);

            return [
                'success' => true,
                'message' => 'User deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

}