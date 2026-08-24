<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('registers betriebsbesuche routes', function () {
    expect(Route::has('apps.hwro.betriebsbesuche.index'))->toBeTrue();
    expect(Route::has('apps.hwro.betriebsbesuche.show'))->toBeTrue();
});
