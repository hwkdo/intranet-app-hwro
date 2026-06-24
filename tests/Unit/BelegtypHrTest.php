<?php

declare(strict_types=1);

use Hwkdo\IntranetAppHwro\Data\AppSettings;
use Hwkdo\IntranetAppHwro\Models\IntranetAppHwroSettings;
use Hwkdo\IntranetAppHwro\Models\Schlagwort;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

test('AppSettings has defaultBelegtypHr with Eintragungsverfahren as default', function () {
    $settings = new AppSettings();

    expect($settings->defaultBelegtypHr)->toBe('Eintragungsverfahren');
});

test('Schlagwort resolveBelegtypHr returns mapped value when set', function () {
    $schlagwort = new Schlagwort([
        'schlagwort' => 'Handelsregisterauszug',
        'belegtyp_hr' => 'Handelsregisterverfahren',
    ]);

    expect($schlagwort->resolveBelegtypHr())->toBe('Handelsregisterverfahren');
});

test('Schlagwort resolveBelegtypHr falls back to default when belegtyp_hr is null', function () {
    Schema::create('intranet_app_hwro_settings', function (Blueprint $table) {
        $table->id();
        $table->unsignedInteger('version')->default(1);
        $table->json('settings')->nullable();
        $table->timestamps();
    });

    IntranetAppHwroSettings::query()->create([
        'version' => 1,
        'settings' => new AppSettings(defaultBelegtypHr: 'Eintragungsverfahren'),
    ]);

    $schlagwort = new Schlagwort([
        'schlagwort' => 'Anhang',
        'belegtyp_hr' => null,
    ]);

    expect($schlagwort->resolveBelegtypHr())->toBe('Eintragungsverfahren');
});

test('Schlagwort resolveBelegtypHrForName uses schlagwort mapping from database', function () {
    Schema::create('intranet_app_hwro_schlagworts', function (Blueprint $table) {
        $table->id();
        $table->string('schlagwort');
        $table->json('filenames')->nullable();
        $table->string('belegtyp_hr')->nullable();
        $table->timestamps();
    });

    Schlagwort::query()->create([
        'schlagwort' => 'Handelsregisterauszug',
        'belegtyp_hr' => 'Handelsregisterverfahren',
    ]);

    expect(Schlagwort::resolveBelegtypHrForName('Handelsregisterauszug'))
        ->toBe('Handelsregisterverfahren');
});

test('Schlagwort resolveBelegtypHrForName falls back to default for unknown schlagwort', function () {
    Schema::create('intranet_app_hwro_schlagworts', function (Blueprint $table) {
        $table->id();
        $table->string('schlagwort');
        $table->json('filenames')->nullable();
        $table->string('belegtyp_hr')->nullable();
        $table->timestamps();
    });

    expect(Schlagwort::resolveBelegtypHrForName('Unbekannt'))
        ->toBe('Eintragungsverfahren');
});
