<?php

namespace App\Repositories;

use App\DB\Contracts\DBConnection;
use PDO;

readonly class DriverRepository implements RepositoryInterface
{
    public function __construct(
        private DBConnection $primaryDB,
        private DBConnection $replicaDB,
    )
    {
    }

    public function storeDriverLocation(array $coordinates): array
    {
        $pdo = $this->primaryDB->connection();
        $driverId = $coordinates['driver_id'];
        $lat = $coordinates['latitude'];
        $lon = $coordinates['longitude'];

        $query = "SELECT id FROM driver_locations WHERE driver_id = :driver_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['driver_id' => $driverId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // обновляем
            $update = "
            UPDATE driver_locations
            SET location = ST_SetSRID(ST_MakePoint(:lon, :lat), 4326),
                updated_at = NOW()
            WHERE driver_id = :driver_id
        ";
            $stmt = $pdo->prepare($update);
            $stmt->execute([
                'lon' => $lon,
                'lat' => $lat,
                'driver_id' => $driverId
            ]);
        } else {
            // вставляем
            $insert = "
            INSERT INTO driver_locations(driver_id, location, updated_at)
            VALUES(:driver_id, ST_SetSRID(ST_MakePoint(:lon, :lat), 4326), NOW())
        ";
            $stmt = $pdo->prepare($insert);
            $stmt->execute([
                'driver_id' => $driverId,
                'lon' => $lon,
                'lat' => $lat
            ]);
        }

        return [
            'driver_id' => $driverId,
            'latitude' => $lat,
            'longitude' => $lon,
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    public function storeDriver(array $bodyParams): array
    {
        $connection = $this->primaryDB->connection();

        $lat = $bodyParams['latitude'] ?? null;
        $lon = $bodyParams['longitude'] ?? null;

        $sql = "
        INSERT INTO drivers (name, location)
        VALUES (:name, ST_SetSRID(ST_MakePoint(:lon, :lat), 4326))
        RETURNING id, name, location
    ";

        $stmt = $connection->prepare($sql);
        $stmt->execute([
            'name' => $bodyParams['name'] ?? null,
            'lat'  => $lat,
            'lon'  => $lon,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}