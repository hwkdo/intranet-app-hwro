<?php

namespace Hwkdo\IntranetAppHwro\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Hwkdo\IntranetAppHwro\IntranetAppHwro
 */
class IntranetAppHwro extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Hwkdo\IntranetAppHwro\IntranetAppHwro::class;
    }
}
