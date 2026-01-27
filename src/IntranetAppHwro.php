<?php

namespace Hwkdo\IntranetAppHwro;

use Hwkdo\IntranetAppBase\Interfaces\IntranetAppInterface;
use Hwkdo\IntranetAppHwro\Data\AppSettings;
use Hwkdo\IntranetAppHwro\Data\UserSettings;
use Illuminate\Support\Collection;

class IntranetAppHwro implements IntranetAppInterface
{
    public static function app_name(): string
    {
        return 'Handwerksrolle Online (Hwro)';
    }

    public static function app_icon(): string
    {
        return 'magnifying-glass';
    }

    public static function identifier(): string
    {
        return 'hwro';
    }

    public static function roles_admin(): Collection
    {
        return collect(config('intranet-app-hwro.roles.admin'));
    }

    public static function roles_user(): Collection
    {
        return collect(config('intranet-app-hwro.roles.user'));
    }

    public static function userSettingsClass(): ?string
    {
        return UserSettings::class;
    }

    public static function appSettingsClass(): ?string
    {
        return AppSettings::class;
    }

    public static function mcpServers(): array
    {
        return [
            'hwro' => [
                'class' => \Hwkdo\IntranetAppHwro\Mcp\Servers\HwroServer::class,
                'middleware' => ['auth:api'],
            ],
        ];
    }
}
