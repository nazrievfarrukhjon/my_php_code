<?php

namespace App\Controllers;

use App\DB\Contracts\DBConnection;
use App\Http\RequestDTO;
use Exception;

class WelcomeController implements ControllerInterface
{
    private DBConnection $db;

    /**
     * @throws Exception
     */
    public function __construct(
        DBConnection $db
    ) {
        $this->db = $db;
    }

    /**
     * @throws Exception
     */
    public function index(RequestDTO $requestDTO): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Taxi Tracker</title>
</head>
<body>
    <h1>Welcome to Taxi Tracker</h1>
    <div id="map"></div>

    <script>
        const ws = new WebSocket('ws://localhost:8080');

        ws.onmessage = function(event) {
            const data = JSON.parse(event.data);
            console.log('Driver location update:', data);
            // TODO: Update your map marker here
        };

        ws.onopen = function() {
            console.log('Connected to WebSocket server!');
        };

        ws.onclose = function() {
            console.log('WebSocket connection closed');
        };
    </script>
</body>
</html>
HTML;
    }


    public function favicon(RequestDTO $requestDTO): void
    {
        header('Content-Type: image/x-icon');
        readfile(ROOT_DIR . '/public/favicon.png');
        exit;
    }

}