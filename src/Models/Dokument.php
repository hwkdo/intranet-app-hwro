<?php

namespace Hwkdo\IntranetAppHwro\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Dokument extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $table = 'intranet_app_hwro_dokuments';

    protected $fillable = ['vorgang_id', 'schlagwort_id'];

    protected function casts(): array
    {
        return [
            'vorgang_id' => 'integer',
            'schlagwort_id' => 'integer',
        ];
    }

    public function vorgang(): BelongsTo
    {
        return $this->belongsTo(Vorgang::class);
    }

    public function schlagwort(): BelongsTo
    {
        return $this->belongsTo(Schlagwort::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->singleFile()
            ->useDisk('intranet-app-hwro');
    }
}

