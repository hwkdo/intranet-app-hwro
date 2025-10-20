<?php

namespace Hwkdo\IntranetAppHwro\Models;

use Illuminate\Database\Eloquent\Model;

class Vorgang extends Model
{
    protected $table = 'intranet_app_hwro_vorgangs';
    protected $fillable = ['vorgangsnummer', 'betriebsnr'];   
}