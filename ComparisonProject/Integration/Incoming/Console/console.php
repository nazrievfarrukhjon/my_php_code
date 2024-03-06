<?php

namespace console;

// todo hold one process to handle queue
// todo create queue

use Comparison\Integration\Incoming\Console\ConsoleCommandsHandler;

require __DIR__ . '/../../../../vendor/autoload.php';

if ($argc < 2) {
    echo "please use signature: php my_php_file_script.php <message>\n";
    exit(1);
}

$args = $argv[1];
$secondArg = $argv[2];
$thirdArg = $argv[3];

$childProcesses = [];

for ($i = 0; $i < 1; $i++) {
    $pid = pcntl_fork();

    if ($pid == -1) {
        die('Could not fork');
    } elseif ($pid) {
        // This is the parent process
        $childProcesses[] = $pid;
    } else {
        ConsoleCommandsHandler::handle($args, $secondArg, $thirdArg);

        echo "Child PID: " . getmypid() . "\n";
        exit(0);
    }
}

foreach ($childProcesses as $pid) {
    pcntl_waitpid($pid, $status);
}
