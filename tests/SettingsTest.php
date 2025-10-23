<?php

declare(strict_types=1);

use Hwkdo\IntranetAppHwro\Data\AppSettings;
use Hwkdo\IntranetAppHwro\Data\UserSettings;
use Hwkdo\IntranetAppHwro\IntranetAppHwro;
use Hwkdo\IntranetAppHwro\Models\IntranetAppHwroSettings;

test('IntranetAppHwro returns correct user settings class', function () {
    expect(IntranetAppHwro::userSettingsClass())
        ->toBe(UserSettings::class);
});

test('IntranetAppHwro returns correct app settings class', function () {
    expect(IntranetAppHwro::appSettingsClass())
        ->toBe(AppSettings::class);
});

test('IntranetAppHwroSettings can retrieve current', function () {
    $settings = IntranetAppHwroSettings::current();

    expect($settings)->toBeInstanceOf(IntranetAppHwroSettings::class)
        ->and($settings->settings)->toBeInstanceOf(AppSettings::class);
})->skip('Database configuration issue');

test('IntranetAppHwroSettings has version field', function () {
    $settings = IntranetAppHwroSettings::current();

    expect($settings->version)->toBeInt()
        ->and($settings->settings->scheduleSearchBetriebsnr)->toBeBool()
        ->and($settings->settings->scheduleMakeBetriebsakte)->toBeBool();
})->skip('Database configuration issue');

test('AppSettings has correct default values', function () {
    $settings = new AppSettings();

    expect($settings->scheduleSearchBetriebsnr)->toBeTrue()
        ->and($settings->scheduleMakeBetriebsakte)->toBeTrue();
});

test('UserSettings has correct default values', function () {
    $settings = new UserSettings();

    expect($settings->defaultVorgaengeFilter)->toBe('alle');
});

