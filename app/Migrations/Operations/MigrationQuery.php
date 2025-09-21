<?php
namespace App\Migrations\Operations;

use App\DB\Contracts\DBConnection;
use Exception;

readonly class MigrationQuery
{
    public function __construct(
        private DBConnection $db
    ) {}

    /**
     * Run all migration files
     * @param string $method "migrate" or "rollback"
     * @throws Exception
     */
    public function run(string $method): void
    {
        $directory = ROUTE_ . '/../../Migrations';
        $fileNames = scandir($directory);

        // filter only files like "123_Blacklists.php"
        $fileNames = array_filter($fileNames, fn($fileName) =>
            preg_match('/^\d+_/', $fileName) === 1
        );

        foreach ($fileNames as $file) {
            require_once $directory . '/' . $file;

            $className = basename($file, '.php');
            $exploded = explode('_', $className);

            // full namespaced class
            $className = 'App\\Migrations\\' . $exploded[1];

            if (!class_exists($className)) {
                throw new Exception("Migration class $className not found in $file");
            }

            $migration = new $className($this->db);
            if (!method_exists($migration, $method)) {
                throw new Exception("Migration class $className missing method $method");
            }

            $migration->$method();
        }
    }
}
