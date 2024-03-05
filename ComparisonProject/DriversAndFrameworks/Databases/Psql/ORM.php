<?php

namespace Comparison\DriversAndFrameworks\DataBASES\Psql;

use Comparison\MembersOfComparison\Blacklisted\Entities\Blacklisted;
use DateTime;
use Exception;
use InvalidArgumentException;
use PDO;

class ORM
{
    protected ?\PDO $db;
    protected ?string $table = null;
    protected array|null $fields = null;
    private array $rows;
    private string $sqlQuery;

    private array $values = [];
    private \PDOStatement|false $stmt;
    private ?string $joinQuery = null;

    private string $selectingItems = '*';

    private ?string $orderBy = null;

    private ?string $groupBy = null;

    public function __construct()
    {
        $this->db = DB::getPGConnection();
    }

    public function __set($name, $value)
    {
        $this->fields[$name] = $value;
    }

    public function __get($name)
    {
        return $this->fields[$name] ?? null;
    }

    public function getById(int $id)
    {

    }

    public function getAll()
    {

    }

    public function save($params)
    {
        try {
            $columns = [];
            $placeholders = [];
            $values = [];

            foreach ($params as $key => $value) {
                $columns[] = $key;
                $placeholders[] = ":$key";
                $value = $this->formatValue($key, $value);
                $values[":$key"] = $value;
            }

            $columns = implode(', ', $columns);
            $placeholders = implode(', ', $placeholders);

            $sqlQuery = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";

            $stmt = $this->db->prepare($sqlQuery);

            if ($stmt->execute($values)) {
                return $this->db->lastInsertId(); // Return the last inserted ID
            } else {
                throw new Exception('Failed to execute the SQL query.');
            }

        } catch
        (Exception $e) {
            dd($e);
            return null;
        }
    }


    public static function update(array $params)
    {
        return (new static())->updateById($params);
    }

    public function updateById($params)
    {
        try {
            if (!isset($params['id'])) {
                return null;
            }

            $id = $params['id'];
            $bl = Blacklisted::find($id);

            if (!$bl) {
                return null;
            }

            $updates = [];

            foreach ($params as $key => $value) {
                if ($key === 'id' || !array_key_exists($key, $bl->fields)) {
                    continue;
                }

                $updates[$key] = $value;
            }

            if (empty($updates)) {
                return $bl;
            }

            $setStatements = [];
            foreach ($updates as $key => $value) {
                $setStatements[] = "{$key} = :{$key}";
            }
            $setClause = implode(', ', $setStatements);

            $sqlQuery = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
            $stmt = $this->db->prepare($sqlQuery);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            foreach ($updates as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }

            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return null;
            }

            return Blacklisted::find($id);
        } catch (\Error $e) {
            dd($e);
        }
    }

    public static function find(int $id): ?static
    {
        return (new static())->findById($id);
    }

    private function findById(int $id): ?static
    {
        $sqlQuery = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sqlQuery);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $obj = new static();

        foreach ($row as $key => $value) {
            $obj->$key = $value;
        }

        return $obj;
    }

    public static function create(array $params): false|int
    {
        return (new static())->save($params);
    }

    public static function delete(int $id): bool
    {
        return (new static())->deleteById($id);
    }

    public function deleteById(int $id): bool
    {
        $sqlQuery = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sqlQuery);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function toSql(): string
    {
        $this->sqlQuery = "SELECT {$this->selectingItems} FROM " . $this->sqlQuery;

        if ($this->joinQuery) {
            $this->sqlQuery .= ' ' . $this->joinQuery;
            $this->joinQuery = null;
        }

        if ($this->groupBy) {
            $this->sqlQuery .= ' ' . $this->groupBy;
            $this->groupBy = null;
        }

        if ($this->orderBy) {
            $this->sqlQuery .= ' ' . $this->orderBy;
            $this->orderBy = null;
        }

        return $this->sqlQuery;
    }

    public static function query(): ?static
    {
        $obj = (new static());
        //todo
        $obj->sqlQuery = "{$obj->table}";

        return $obj;
    }

    public function where(string $column, string $condition, string $value): ?static
    {
        //todo
        $validConditions = ['=', '!=', '<', '>', '<=', '>=', 'like'];
        if (!in_array($condition, $validConditions)) {
            throw new InvalidArgumentException('Invalid condition');
        }

        $placeholder = ':value' . count($this->values);
        $this->values[$placeholder] = $value;

        if ($this->joinQuery) {
            $this->sqlQuery = $this->sqlQuery . ' ' . $this->joinQuery;
            $this->joinQuery = null;
        }

        if (str_contains($this->sqlQuery, 'WHERE')) {
            $this->sqlQuery .= " AND {$column} {$condition} {$placeholder}";
        } else {
            $this->sqlQuery .= " WHERE {$column} {$condition} {$placeholder}";
        }

        return $this;
    }

    public function get(): ?array
    {
        $this->sqlQuery = "SELECT {$this->selectingItems} FROM " . $this->sqlQuery;

        if ($this->joinQuery) {
            $this->sqlQuery .= ' ' . $this->joinQuery;
            $this->joinQuery = null;
        }

        if ($this->groupBy) {
            $this->sqlQuery .= ' ' . $this->groupBy;
            $this->groupBy = null;
        }

        if ($this->orderBy) {
            $this->sqlQuery .= ' ' . $this->orderBy;
            $this->orderBy = null;
        }

        $stmt = $this->db->prepare($this->sqlQuery);

        foreach ($this->values as $placeholder => &$value) {
            $stmt->bindParam($placeholder, $value, PDO::PARAM_STR);
        }

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($rows === false) {
            return null;
        }

        $this->rows = $rows;

        return $this->rows;
    }


    public function first()
    {
        $this->sqlQuery = "SELECT {$this->selectingItems} FROM " . $this->sqlQuery;

        if ($this->joinQuery) {
            $this->sqlQuery .= ' ' . $this->joinQuery;
            $this->joinQuery = null;
        }

        $this->sqlQuery .= " LIMIT 1";

        $stmt = $this->db->prepare($this->sqlQuery);

        foreach ($this->values as $placeholder => &$value) {
            $stmt->bindParam($placeholder, $value, PDO::PARAM_STR);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $row;
    }


    public function formatValue(string $column, string $value): string
    {
        if ($column == 'created_at' || $column == 'updated_at') {
            $value = $value . ' ' . date('H:i:s');
            $timestamp = DateTime::createFromFormat('Y-m-d H:i:s', $value);

            if ($timestamp === false) {
                echo "Error parsing the timestamp: $value";
            } else {
                $value = $timestamp->format('Y-m-d H:i:s');
            }
        }
        return $value;
    }

    public function join(string $table, string $columnOne, string $condition, string $columnTwo): static
    {
        $this->joinQuery = "join {$table} on {$columnOne} {$condition} $columnTwo";
        return $this;
    }

    public function orderBy(string|int $column = 'id', string $direction = 'asc'): static
    {
        $this->orderBy  = " ORDER BY {$column} " . ' ' . $direction;

        return $this;
    }

    public function select(string $selectingItems): static
    {
        $this->selectingItems = $selectingItems;
        return $this;
    }

    public function groupBy(array $columns): static
    {
        $this->groupBy  = " GROUP BY ". implode(',', $columns);

        return $this;
    }

}
