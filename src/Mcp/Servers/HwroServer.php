<?php

namespace Hwkdo\IntranetAppHwro\Mcp\Servers;

use Hwkdo\IntranetAppHwro\Mcp\Tools\DokumenteAnzeigenTool;
use Hwkdo\IntranetAppHwro\Mcp\Tools\VorgaengeAnzeigenTool;
use Laravel\Mcp\Server;

class HwroServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'HWRO Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.0.0';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = 'This server provides tools to query and display Vorgänge (cases) and Dokumente (documents) from the HWRO (Handwerksrolle Online) system with optional filtering capabilities.';

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        VorgaengeAnzeigenTool::class,
        DokumenteAnzeigenTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        //
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        //
    ];
}
