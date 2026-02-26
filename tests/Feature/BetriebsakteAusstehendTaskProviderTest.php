<?php

declare(strict_types=1);

use Hwkdo\IntranetAppBase\Interfaces\ProvidesTasksInterface;
use Hwkdo\IntranetAppBase\Interfaces\TaskProviderInterface;
use Hwkdo\IntranetAppHwro\IntranetAppHwro;
use Hwkdo\IntranetAppHwro\Models\Vorgang;
use Hwkdo\IntranetAppHwro\Tasks\BetriebsakteAusstehendTaskProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Schema;

function makeHwroUser(): Authenticatable
{
    return new class implements Authenticatable
    {
        public function getAuthIdentifierName(): string { return 'id'; }
        public function getAuthIdentifier(): mixed { return 1; }
        public function getAuthPasswordName(): string { return 'password'; }
        public function getAuthPassword(): string { return ''; }
        public function getRememberToken(): ?string { return null; }
        public function setRememberToken($value): void {}
        public function getRememberTokenName(): string { return 'remember_token'; }
    };
}

// --- Interface compliance ---

test('IntranetAppHwro implements ProvidesTasksInterface', function () {
    expect(is_a(IntranetAppHwro::class, ProvidesTasksInterface::class, true))->toBeTrue();
});

test('IntranetAppHwro::taskProviders returns BetriebsakteAusstehendTaskProvider', function () {
    expect(IntranetAppHwro::taskProviders())
        ->toContain(BetriebsakteAusstehendTaskProvider::class);
});

test('BetriebsakteAusstehendTaskProvider implements TaskProviderInterface', function () {
    expect(is_a(BetriebsakteAusstehendTaskProvider::class, TaskProviderInterface::class, true))->toBeTrue();
});

test('BetriebsakteAusstehendTaskProvider has correct label', function () {
    $provider = new BetriebsakteAusstehendTaskProvider;

    expect($provider->getLabel())->toBe('Betriebsakte ausstehend');
});

// --- DB tests ---

test('BetriebsakteAusstehendTaskProvider returns tasks for vorgänge without betriebsakte', function () {
    Schema::create('intranet_app_hwro_vorgangs', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->id();
        $table->bigInteger('vorgangsnummer');
        $table->bigInteger('betriebsnr')->nullable()->default(null);
        $table->timestamp('betriebsakte_created_at')->nullable();
        $table->timestamps();
    });

    Vorgang::factory()->withBetriebsnr()->create();
    Vorgang::factory()->withBetriebsnr()->create();
    Vorgang::factory()->withoutBetriebsnr()->create();

    $provider = new BetriebsakteAusstehendTaskProvider;
    $tasks = $provider->getTasksForUser(makeHwroUser());

    expect($tasks)->toHaveCount(2)
        ->and($tasks->first()->appIdentifier)->toBe('hwro')
        ->and($tasks->first()->badge)->toBe('Ausstehend')
        ->and($tasks->first()->priority)->toBe(5);
})->skip('Requires DB migration setup');

test('BetriebsakteAusstehendTaskProvider excludes vorgänge with betriebsakte_created_at set', function () {
    Schema::create('intranet_app_hwro_vorgangs', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->id();
        $table->bigInteger('vorgangsnummer');
        $table->bigInteger('betriebsnr')->nullable()->default(null);
        $table->timestamp('betriebsakte_created_at')->nullable();
        $table->timestamps();
    });

    Vorgang::factory()->withBetriebsnr()->create(['betriebsakte_created_at' => now()]);
    Vorgang::factory()->withBetriebsnr()->create();

    $provider = new BetriebsakteAusstehendTaskProvider;
    $tasks = $provider->getTasksForUser(makeHwroUser());

    expect($tasks)->toHaveCount(1);
})->skip('Requires DB migration setup');

test('BetriebsakteAusstehendTaskProvider task contains correct title and description', function () {
    Schema::create('intranet_app_hwro_vorgangs', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->id();
        $table->bigInteger('vorgangsnummer');
        $table->bigInteger('betriebsnr')->nullable()->default(null);
        $table->timestamp('betriebsakte_created_at')->nullable();
        $table->timestamps();
    });

    $vorgang = Vorgang::factory()->create([
        'betriebsnr' => 123456,
        'vorgangsnummer' => 7654321,
        'betriebsakte_created_at' => null,
    ]);

    $provider = new BetriebsakteAusstehendTaskProvider;
    $task = $provider->getTasksForUser(makeHwroUser())->first();

    expect($task->title)->toBe('Betriebsakte erstellen')
        ->and($task->description)->toContain('123456')
        ->and($task->description)->toContain('7654321')
        ->and($task->appName)->toBe(IntranetAppHwro::app_name())
        ->and($task->appIcon)->toBe(IntranetAppHwro::app_icon());
})->skip('Requires DB migration setup');
