<?php

namespace App\Repositories;

use App\DB\Contracts\DBConnection;
use Exception;
use PDO;
use Redis;
use WebSocket\Client;

readonly class DriverRepository implements RepositoryInterface
{
    public function __construct(
        private DBConnection $primaryDB,
        private DBConnection $replicaDB,
    )
    {
    }

    public function storeDriverLocation(array $params): array
    {
        $pdo = $this->primaryDB->connection();
        $driverId = $params['driver_id'];
        $lat = $params['latitude'];
        $lon = $params['longitude'];

        $query = "SELECT id FROM driver_locations WHERE driver_id = :driver_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['driver_id' => $driverId]);
        $existing = $stmt->fetch();

        if ($existing) {
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

        // publish event here
        try {

            $redis = new Redis();
            $redis->connect('my_php_code-redis-1', 6379);
            $redis->publish('driver_updates', json_encode([
                'driver_id' => $driverId,
                'lat' => $lat,
                'lon' => $lon,
            ]));

            //
            $ws = new Client("ws://websocket:6001", []);

            $ws->send(json_encode([
                'event' => 'update',
                'channel' => 'driver_updates',
                'data' => [
                    'driver_id' => $driverId,
                    'lat' => $lat,
                    'lon' => $lon,
                ]
            ]));

            echo "Published driver location directly to WebSocket!\n";
            error_log("WebSocket publish OK", 3, ROOT_DIR . '/logs/app.log');

        } catch (Exception $e) {
            error_log("WebSocket publish failed: " . $e->getMessage(), 3, ROOT_DIR . '/logs/app.log');
            echo "WebSocket publish failed: " . $e->getMessage() . "\n";
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
            'lat' => $lat,
            'lon' => $lon,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}