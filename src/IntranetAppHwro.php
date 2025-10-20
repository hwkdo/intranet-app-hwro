<?php

namespace Hwkdo\IntranetAppHwro;
use Hwkdo\IntranetAppBase\Interfaces\IntranetAppInterface;
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
}
