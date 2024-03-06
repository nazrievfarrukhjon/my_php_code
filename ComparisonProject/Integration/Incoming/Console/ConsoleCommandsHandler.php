<?php

namespace Comparison\Integration\Incoming\Console;
use Comparison\Integration\Incoming\Console\migrations\CreateMigrationsTable;
use Comparison\Integration\Incoming\Console\migrations\CreateUsersTable;
use Comparison\Integration\Incoming\Console\migrations\CreateWhitelistedsTable;
use Console\BaseRoutes;
use Console\Exception;
use console\migrations\CreateSuspiciousTable;
use Console\Router;
use Console\SuspiciousRoutes;
use Console\TerroristsRoutes;

class ConsoleCommandsHandler
{
    private array $commands = [
        'migrate all' => 'migrates all',
        'rollback all' => 'roll back all migrations',
        'clear route cache' => 'clears routes caches file',
        'refresh routes' => 'refreshes routes'
    ];

    private array $migrationClasses = [
        CreateMigrationsTable::class => CreateMigrationsTable::class,
        CreateUsersTable::class => CreateUsersTable::class,
        CreateSuspiciousTable::class => CreateSuspiciousTable::class,
        CreateWhitelistedsTable::class => CreateWhitelistedsTable::class,
    ];

    public static function handle($args, $secondArg, $thirdArg): void
    {
        (new self())->handleCommand($args, $secondArg, $thirdArg);
    }

    private function refreshRoutes(): void
    {
        $routesCacheFile = __DIR__ . '/../conf/routes_cache.php';
        if (!file_exists($routesCacheFile)) {
            file_put_contents($routesCacheFile, '');
        }

        $routes = [
            new TerroristsRoutes(),
            new BaseRoutes(),
            new SuspiciousRoutes(),
        ];
        foreach ($routes as $route) {
            $route();
        }
        $routes = Router::$routes;

        $routeContent = "<?php \n return " . var_export($routes, true) . ";";
        file_put_contents($routesCacheFile, $routeContent);
        echo("routes cached successfully.\n");
    }

    public function echoMessage(string $args): void
    {
        echo "Unknown command: $args";
    }

    private function migrateAll(): void
    {
        foreach ($this->migrationClasses as $migration) {
            (new $migration())->up();
        }
    }

    public function migrateByClassName($className): void
    {
        (new $this->migrationClasses['console\\migrations\\' . $className]())->up();
    }

    private function rollbackAll(): void
    {
        try {
            (new CreateUsersTable())->down();
        } catch (Exception $e) {
            echo $e;
        }
    }

    private function clearRouteCache(): void
    {
        $routesCacheFile = __DIR__ . '/../conf/routes_cache.php';
        file_put_contents($routesCacheFile, '');
    }

    private function showCommands(): void
    {
        echo implode(PHP_EOL, array_keys($this->commands));
    }

    private function handleCommand(string $args, $secondArg, $thirdArg): void
    {
        if ('refresh' === $args) {
            $this->refresh($secondArg);
        } elseif ('migrate' === $args) {
            $this->migrate($secondArg, $thirdArg);
        } elseif ('list' === $args) {
            $this->showCommands();
        } else {
            $this->{$this->commands[$args]}();
        }
        echo "\n";
    }

    private function refresh($secondArg): void
    {
        if ('env' === $secondArg) {
            $envCacheFile = __DIR__ . '/../conf/cache.php';
            if (!file_exists($envCacheFile)) {
                file_put_contents($envCacheFile, '');
            }

            $ENV = [];

            $envContents = file_get_contents(__DIR__ . '/../.env');

            $lines = explode("\n", $envContents);

            foreach ($lines as $line) {
                if (empty($line) || str_starts_with($line, '#')) {
                    continue;
                }

                list($key, $value) = explode('=', $line, 2);
                $ENV[trim($key)] = trim($value);
            }

            $cacheContent = "<?php \n return " . var_export($ENV, true) . ";";

            file_put_contents($envCacheFile, $cacheContent);


            echo("env cached successfully.\n");

        } elseif ('routes' === $secondArg) {
            $this->refreshRoutes();
        }
    }

    private function migrate($secondArg, $thirdArg): void
    {
        if ('all' === $secondArg) {
            $this->migrateAll();
        } elseif('class' == $secondArg) {
            $this->migrateByClassName($thirdArg);
        }
    }

}
