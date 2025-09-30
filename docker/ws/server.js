// ./docker/ws/server.js
const WebSocket = require('ws');
const redis = require('redis');

const PORT = process.env.PORT || 6001;
const REDIS_HOST = process.env.REDIS_HOST || 'redis';
const REDIS_PORT = process.env.REDIS_PORT || 6379;

// WebSocket server
const wss = new WebSocket.Server({ port: PORT, host: '0.0.0.0' });

wss.on('connection', (ws) => {
    console.log('Client connected');

    ws.on('message', (msg) => {
        console.log('Received from client:', msg);
        wss.clients.forEach(client => {
            if (client.readyState === WebSocket.OPEN) {
                client.send(msg);
            }
        });
    });

    ws.on('close', () => {
        console.log('Client disconnected');
    });
});

// Redis subscriber
const subscriber = redis.createClient({
    host: REDIS_HOST,
    port: REDIS_PORT
});

subscriber.on('error', (err) => console.error('Redis error:', err));

subscriber.subscribe('driver_updates');
subscriber.on('message', (channel, message) => {
    console.log(`Received message on ${channel}: ${message}`);

    // Broadcast to all connected WS clients
    wss.clients.forEach(client => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(message);
        }
    });
});

console.log(`WebSocket server running on port ${PORT}`);
