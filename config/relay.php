<?php

declare(strict_types=1);

use Prism\Relay\Enums\Transport;

return [
    /*
    |--------------------------------------------------------------------------
    | MCP Server Configurations for HWRO App
    |--------------------------------------------------------------------------
    |
    | Define your MCP server configurations here. Each server should have a
    | name as the key, and a configuration array with the appropriate settings.
    |
    */
    'servers' => [
        'hwro' => [
            'transport' => Transport::Http,
            'url' => env('RELAY_HWRO_SERVER_URL', 'http://localhost/mcp/apps/hwro'),
            'timeout' => env('RELAY_HWRO_SERVER_TIMEOUT', 30),
            'headers' => [
                // Bearer Token wird dynamisch zur Laufzeit hinzugefügt
            ],
        ],
    ],
];
