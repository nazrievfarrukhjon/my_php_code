<?php

namespace App\Repositories;

use App\DB\Contracts\DBConnection;
use App\Validations\WhiteliststoreValidation;
use Exception;
use PDO;
use PDOException;

readonly class WhitelistRepository implements RepositoryInterface
{

    private PDO $connection;

    /**
     * @throws Exception
     */
    public function __construct(private DBConnection $db)
    {
        $this->connection = $this->db->connection();
    }

    public function all(): array
    {
        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = "SELECT * FROM whitelists";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return ["Connection failed: " . $e->getMessage()];
        }
    }

    public function store(array $params): void
    {
        try {
            (new WhitelistStoreValidation($params))->check();

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Construct the INSERT query
            $query = "INSERT INTO whitelists (first_name, second_name, third_name, fourth_name, type, birth_date) VALUES (?, ?, ?, ?, ?, ?)";

            // Prepare the statement
            $statement = $this->connection->prepare($query);

            // Execute the statement with parameters
            $statement->execute([$params['first_name'], $params['second_name'], $params['third_name'], $params['fourth_name'], $params['type'], $params['birth_date']]);

            // Return success message or any other appropriate response
            echo "Success: Record inserted successfully";
        } catch (PDOException|Exception $e) {
            echo json_encode(["Error: " . $e->getMessage()]);
        }
    }

    public function delete(int $id): array
    {
        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Construct the DELETE query
            $query = "DELETE FROM whitelists WHERE id = ?";

            // Prepare the statement
            $statement = $this->connection->prepare($query);

            // Execute the statement with parameter
            $statement->execute([$id]);

            // Return success message or any other appropriate response
            return ["Success: Record deleted successfully"];
        } catch (Exception $e) {
            return ["Error: " . $e->getMessage()];
        }
    }

    public function update(int $id, array $params): array
    {
        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Construct the UPDATE query
            $query = "UPDATE whitelists SET first_name = ?, second_name = ?, third_name = ?, fourth_name = ?, type = ?, birth_date = ? WHERE id = ?";

            // Prepare the statement
            $statement = $this->connection->prepare($query);

            // Execute the statement with parameters
            $statement->execute([$params['first_name'], $params['second_name'], $params['third_name'], $params['fourth_name'], $params['type'], $params['birth_date'], $id]);

            // Return success message or any other appropriate response
            return ["Success: Record updated successfully"];
        } catch (Exception $e) {
            return ["Error: " . $e->getMessage()];
        }
    }

}