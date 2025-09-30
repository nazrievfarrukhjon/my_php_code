<?php

namespace App\Repositories;

use App\DB\Contracts\DBConnection;
use Exception;
use PDO;

readonly class RideRepository implements RepositoryInterface
{
    public function __construct(
        private DBConnection $primaryDB,
        private DBConnection $replicaDB,
    ) {}

    /**
     * Create a new ride request
     */
    public function createRide(array $bodyParams): array
    {
        $sql = "
            INSERT INTO rides (client_id, pickup, dropoff, status)
            VALUES (:client_id, ST_GeogFromText(:pickup), ST_GeogFromText(:dropoff), 'pending')
            RETURNING id, status
        ";

        $connection = $this->primaryDB->connection();
        $stmt = $connection->prepare($sql);

        $stmt->execute([
            'client_id' => $bodyParams['client_id'] ?? null, // can be NULL for street hail
            'pickup'    => "SRID=4326;POINT({$bodyParams['pickup']['lon']} {$bodyParams['pickup']['lat']})",
            'dropoff'   => "SRID=4326;POINT({$bodyParams['dropoff']['lon']} {$bodyParams['dropoff']['lat']})",
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Driver accepts ride
     */
    public function acceptRide(int $rideId, int $driverId): array
    {
        $sql = "
            UPDATE rides
            SET driver_id = :driver_id,
                status = 'accepted',
                updated_at = NOW()
            WHERE id = :ride_id AND status = 'pending'
            RETURNING id, driver_id, status
        ";

        $connection = $this->primaryDB->connection();
        $stmt = $connection->prepare($sql);
        $stmt->execute([
            'driver_id' => $driverId,
            'ride_id'   => $rideId,
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new Exception("Ride not found or already accepted");
        }

        return $result;
    }

    /**
     * Mark ride as started (driver picked up client)
     */
    public function startRide(int $rideId): array
    {
        $sql = "
            UPDATE rides
            SET status = 'in_progress',
                updated_at = NOW()
            WHERE id = :ride_id AND status = 'accepted'
            RETURNING id, driver_id, status
        ";

        $connection = $this->primaryDB->connection();
        $stmt = $connection->prepare($sql);
        $stmt->execute(['ride_id' => $rideId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new Exception("Ride cannot be started (must be accepted first)");
        }

        return $result;
    }

    /**
     * Mark ride as completed
     */
    public function completeRide(int $rideId): array
    {
        $sql = "
            UPDATE rides
            SET status = 'completed',
                updated_at = NOW()
            WHERE id = :ride_id AND status = 'in_progress'
            RETURNING id, driver_id, status
        ";

        $connection = $this->primaryDB->connection();
        $stmt = $connection->prepare($sql);
        $stmt->execute(['ride_id' => $rideId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new Exception("Ride cannot be completed (must be in progress)");
        }

        return $result;
    }

    /**
     * Get ride status
     */
    public function getRideStatus(int $rideId): array
    {
        $sql = "
            SELECT id, client_id, driver_id, status, requested_at, updated_at
            FROM rides
            WHERE id = :ride_id
        ";

        $connection = $this->replicaDB->connection();
        $stmt = $connection->prepare($sql);
        $stmt->execute(['ride_id' => $rideId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new Exception("Ride not found");
        }

        return $result;
    }
}
