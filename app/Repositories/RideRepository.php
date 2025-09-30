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

    public function createRide(array $bodyParams): array
    {
        if (empty($bodyParams['user_id']) || empty($bodyParams['pickup']) || empty($bodyParams['dropoff'])) {
            throw new \InvalidArgumentException('user_id, pickup and dropoff are required');
        }

        // Extract pickup coordinates
        if (!isset($bodyParams['pickup']['lat'], $bodyParams['pickup']['lon'])) {
            throw new \InvalidArgumentException('Invalid pickup coordinates format');
        }
        $pickupLat = floatval($bodyParams['pickup']['lat']);
        $pickupLon = floatval($bodyParams['pickup']['lon']);

        // Extract dropoff coordinates
        if (!isset($bodyParams['dropoff']['lat'], $bodyParams['dropoff']['lon'])) {
            throw new \InvalidArgumentException('Invalid dropoff coordinates format');
        }
        $dropoffLat = floatval($bodyParams['dropoff']['lat']);
        $dropoffLon = floatval($bodyParams['dropoff']['lon']);

        $sql = "
        INSERT INTO rides (user_id, pickup, dropoff, status)
        VALUES (:user_id, ST_GeogFromText(:pickup), ST_GeogFromText(:dropoff), 'pending')
        RETURNING id, status
    ";

        $connection = $this->primaryDB->connection();
        $stmt = $connection->prepare($sql);

        $stmt->execute([
            'user_id' => $bodyParams['user_id'],
            'pickup'  => "SRID=4326;POINT($pickupLon $pickupLat)",
            'dropoff' => "SRID=4326;POINT($dropoffLon $dropoffLat)",
        ]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }


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

    public function getRideStatus(int $rideId): array
    {
        $sql = "
            SELECT id, user_id, driver_id, status, requested_at, updated_at
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

    public function getNearby(int $driver_id, int $radius_km): array
    {
        $connection = $this->primaryDB->connection();

        // Convert km to meters
        $radius_m = $radius_km * 1000;

        $sql = "
        SELECT 
            r.id AS ride_id,
            r.user_id,
            r.status,
            ST_Distance(dl.location, r.pickup) AS distance_meters,
            ST_AsText(r.pickup) AS pickup,
            ST_AsText(r.dropoff) AS dropoff
        FROM rides r
        JOIN driver_locations dl ON dl.driver_id = :driver_id
        WHERE r.status = 'pending'
          AND ST_DWithin(dl.location, r.pickup, :radius_m)
        ORDER BY distance_meters ASC
    ";

        $stmt = $connection->prepare($sql);
        $stmt->execute([
            'driver_id' => $driver_id,
            'radius_m' => $radius_m
        ]);

        $rides = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'driver_id' => $driver_id,
            'radius_km' => $radius_km,
            'rides' => $rides
        ];
    }

}
