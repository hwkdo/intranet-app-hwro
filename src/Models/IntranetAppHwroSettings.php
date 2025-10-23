<?php

namespace Hwkdo\IntranetAppHwro\Models;

use Hwkdo\IntranetAppHwro\Data\AppSettings;
use Illuminate\Database\Eloquent\Model;

class IntranetAppHwroSettings extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'settings' => AppSettings::class.':default',
        ];
    }

    public static function current(): IntranetAppHwroSettings
    {
        return self::orderBy('version', 'desc')->first();
    }
}
