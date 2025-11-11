<?php

namespace Hwkdo\IntranetAppHwro\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schlagwort extends Model
{
    use HasFactory;

    protected $table = 'intranet_app_hwro_schlagworts';

    protected $fillable = ['schlagwort', 'filenames'];

    protected function casts(): array
    {
        return [
            'filenames' => 'array',
        ];
    }

    public function dokumente(): HasMany
    {
        return $this->hasMany(Dokument::class);
    }
}

