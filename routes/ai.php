<?php

use Hwkdo\IntranetAppHwro\Mcp\Servers\HwroServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/apps/hwro', HwroServer::class)
    ->middleware(['auth:api']);
