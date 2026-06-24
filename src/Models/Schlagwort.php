<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppHwro\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schlagwort extends Model
{
    use HasFactory;

    protected $table = 'intranet_app_hwro_schlagworts';

    protected $fillable = ['schlagwort', 'filenames', 'belegtyp_hr'];

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

    public function resolveBelegtypHr(): string
    {
        if (filled($this->belegtyp_hr)) {
            return $this->belegtyp_hr;
        }

        return static::defaultBelegtypHr();
    }

    public static function resolveBelegtypHrForName(string $schlagwortName): string
    {
        $schlagwort = static::query()->where('schlagwort', $schlagwortName)->first();

        if ($schlagwort) {
            return $schlagwort->resolveBelegtypHr();
        }

        return static::defaultBelegtypHr();
    }

    public static function defaultBelegtypHr(): string
    {
        return IntranetAppHwroSettings::current()?->settings?->defaultBelegtypHr ?? 'Eintragungsverfahren';
    }
}

