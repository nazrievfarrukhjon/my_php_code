<?php

namespace App\Console\Commands;

use WebSocket\Client;

readonly class ListenToChannelCommand implements Command
{
    public function execute(): void
    {
        $wsUrl = "ws://websocket:6001";
        $channel = "driver_updates";

        $client = new Client($wsUrl, ['timeout' => 600]);

        echo "Connected!\n";

        while (true) {
            $message = $client->receive();
            echo "[" . date('Y-m-d H:i:s') . "] Message received: $message\n";
        }
    }
}
