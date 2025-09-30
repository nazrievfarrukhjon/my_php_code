<?php

namespace App\Repositories;

use App\DB\Contracts\DBConnection;

readonly class DriverLocationRepository implements RepositoryInterface
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


}